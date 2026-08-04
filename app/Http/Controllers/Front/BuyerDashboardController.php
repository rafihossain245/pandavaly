<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Models\Buyer;
use App\Models\BuyerDocument;
use App\Models\Coupon;
use App\Models\District;
use App\Models\Invoice;
use App\Models\MediaFile;
use App\Models\ProductReview;
use App\Models\SalesOrder;
use App\Models\Thana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class BuyerDashboardController extends Controller
{
    private function buyer(): Buyer
    {
        return Auth::guard('buyer')->user();
    }

    /** Statuses that still count as "running" for the buyer. */
    private const RUNNING_STATUSES = [
        'pending', 'approved', 'payment_requested', 'payment_verified',
        'processing', 'confirmed', 'packed', 'shipped',
    ];

    public function index()
    {
        $buyer = $this->buyer();

        $stats = [
            'orders'      => $buyer->orders()->count(),
            'running'     => $buyer->orders()->whereIn('status', self::RUNNING_STATUSES)->count(),
            'cart_items'  => (int) (session('cart')['count'] ?? 0),
            'wishlist'    => $buyer->wishlists()->count(),
            'spent'       => (float) $buyer->orders()->whereNot('status', 'cancelled')->sum('total'),
            'tickets'     => 0, // support tickets are not built yet
            'invoices'    => $buyer->invoices()->count(),
            'outstanding' => (float) $buyer->invoices()->whereIn('status', ['unpaid', 'partial'])->sum('balance'),
        ];

        $recentOrders = $buyer->orders()->withCount('items')->latest()->limit(5)->get();

        $wishlistProducts = $buyer->wishlists()
            ->with('product.product_prices')
            ->latest()
            ->limit(4)
            ->get()
            ->pluck('product')
            ->filter();

        return view('frontEnd.buyer.dashboard', compact('buyer', 'stats', 'recentOrders', 'wishlistProducts'));
    }

    /**
     * Coupons the buyer can use right now — the same validity rules the
     * checkout box enforces, so nothing unusable is advertised here.
     */
    public function coupons()
    {
        $coupons = Coupon::active()
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->orderByDesc('id')
            ->get();

        return view('frontEnd.buyer.coupons', compact('coupons'));
    }

    public function address()
    {
        return view('frontEnd.buyer.address', [
            'buyer'            => $this->buyer(),
            'districts'        => District::active()->get(['id', 'name', 'delivery_charge']),
            'thanasByDistrict' => Thana::orderBy('name')->get(['id', 'district_id', 'name'])->groupBy('district_id'),
        ]);
    }

    public function updateAddress(Request $request)
    {
        $buyer = $this->buyer();

        $validated = $request->validate([
            'address'     => ['required', 'string', 'max:2000'],
            'district_id' => ['required', 'exists:districts,id'],
            'thana_id'    => [
                'nullable',
                Rule::exists('thanas', 'id')->where(
                    fn ($query) => $query->where('district_id', $request->input('district_id'))
                ),
            ],
            'city'        => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
        ], [
            'thana_id.exists' => 'The selected thana does not belong to the chosen district.',
        ]);

        $buyer->update($validated);

        return back()->with('success', 'Your default delivery address has been saved.');
    }

    public function payments()
    {
        $buyer = $this->buyer();

        $orders = $buyer->orders()
            ->with('invoice')
            ->latest()
            ->paginate(10);

        $totals = [
            'paid' => (float) $buyer->orders()->sum('advance_paid'),
            'due'  => (float) $buyer->invoices()->whereIn('status', ['unpaid', 'partial'])->sum('balance'),
        ];

        return view('frontEnd.buyer.payments', compact('orders', 'totals'));
    }

    public function reviews()
    {
        $reviews = ProductReview::with('product')
            ->where('buyer_id', $this->buyer()->id)
            ->latest()
            ->paginate(10);

        return view('frontEnd.buyer.reviews', compact('reviews'));
    }

    public function editPassword()
    {
        return view('frontEnd.buyer.password', ['buyer' => $this->buyer()]);
    }

    public function updatePassword(Request $request)
    {
        $buyer = $this->buyer();

        // An account created by guest checkout has a random password nobody
        // knows, so the current-password check only applies once one is set.
        $hasUsablePassword = ! empty($buyer->password) && ! $buyer->must_set_password;

        $request->validate([
            'current_password' => $hasUsablePassword ? ['required', 'current_password:buyer'] : ['nullable'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $buyer->update([
            'password'          => $request->password,
            'must_set_password' => false,
        ]);

        return back()->with('success', 'Your password has been updated.');
    }

    public function orders()
    {
        $orders = $this->buyer()->orders()->withCount('items')->latest()->paginate(10);

        return view('frontEnd.buyer.orders.index', compact('orders'));
    }

    public function order(SalesOrder $order)
    {
        abort_unless($order->buyer_id === $this->buyer()->id, 404);
        $order->load(['items.productSku.product', 'invoice']);

        return view('frontEnd.buyer.orders.show', compact('order'));
    }

    /**
     * The condensed 4-stage tracker the buyer sees, mapped from the internal
     * workflow (the public /track-order page uses the 6-step version).
     */
    public function trackOrder(SalesOrder $order)
    {
        abort_unless($order->buyer_id === $this->buyer()->id, 404);

        $stage = match ($order->status) {
            'pending', 'approved', 'payment_requested', 'payment_verified' => 1,
            'processing', 'confirmed', 'packed' => 2,
            'shipped' => 3,
            'delivered', 'completed' => 4,
            default => 1, // cancelled orders show as placed and nothing further
        };

        return view('frontEnd.buyer.orders.tracking', [
            'order'      => $order->loadCount('items'),
            'stage'      => $stage,
            'cancelled'  => $order->status === 'cancelled',
            'stages'     => ['Order Placed', 'Processing', 'In Transit', 'Delivered'],
            'estimated'  => $order->created_at?->copy()->addDays(3),
        ]);
    }

    public function reorder(SalesOrder $order)
    {
        abort_unless($order->buyer_id === $this->buyer()->id, 404);
        $order->load('items.productSku.product');

        $cart = app(CartController::class);
        $errors = [];
        $added = 0;
        $bumped = false;

        foreach ($order->items as $item) {
            $sku = $item->productSku;
            if (!$sku || !$sku->product) {
                continue;
            }

            $qty = $item->qty;
            $moq = max(1, (int) ($sku->product->moq ?? 1));
            if ($qty < $moq) {
                $qty = $moq;
                $bumped = true;
            }

            $result = $cart->addToCart($sku->product, $sku, $qty);

            if (isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                $added++;
            }
        }

        if ($added === 0) {
            return back()->with('error', $errors[0] ?? 'Unable to add items from this order to your cart.');
        }

        if ($errors) {
            return redirect()->route('cart.index')->with('warning', 'Some items could not be added: ' . implode(' ', $errors));
        }

        $message = 'Items from this order have been added to your cart.';
        if ($bumped) {
            $message .= ' Quantities for some items were increased to meet the minimum order quantity.';
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function uploadSlip(Request $request, SalesOrder $order)
    {
        abort_unless($order->buyer_id === $this->buyer()->id, 404);
        abort_unless(
            $order->payment_method === 'bank_transfer'
                && in_array($order->status, ['approved', 'payment_requested'])
                && $order->payment_status !== 'pending_verification'
                && $order->payment_status !== 'verified',
            422
        );

        $request->validate([
            'transaction_id'  => 'required|string|max:255',
            'bank_name'       => 'required|string|max:255',
            'payment_slip'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $slip        = $request->file('payment_slip');
        $image_name  = uniqid();
        $ext         = strtolower($slip->getClientOriginalExtension());
        $filename    = $image_name . '.' . $ext;
        $upload_path = 'images/payment-slips/';
        $image_url   = $upload_path . $filename;
        $success     = $slip->move(public_path($upload_path), $filename);

        if (!$success) {
            return back()->with('error', 'Failed to upload payment slip. Please try again.');
        }

        $order->update([
            'payment_slip_path'        => $image_url,
            'payment_transaction_id'   => $request->transaction_id,
            'payment_bank_name'        => $request->bank_name,
            'payment_slip_uploaded_at' => now(),
            'payment_status'           => 'pending_verification',
        ]);

        return back()->with('success', 'Payment slip uploaded successfully. We will verify it shortly.');
    }

    public function profile()
    {
        $buyer = $this->buyer();
        $tradeLicenseDocument = $buyer->documents()->where('type', 'TradeLicense')->latest()->with('media')->first();
        $districts = \App\Models\District::active()->get();
        $thanasByDistrict = \App\Models\Thana::orderBy('name')->get()->groupBy('district_id');

        return view('frontEnd.buyer.profile', [
            'buyer' => $buyer,
            'tradeLicenseDocument' => $tradeLicenseDocument,
            'districts' => $districts,
            'thanasByDistrict' => $thanasByDistrict,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $buyer = $this->buyer();
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('buyers')->ignore($buyer->id)],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'thana_id' => ['nullable', 'exists:thanas,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'tin' => ['nullable', 'string', 'max:50'],
            'trade_license_no' => ['nullable', 'string', 'max:100'],
            'trade_license_expiry' => ['nullable', 'date'],
            'trade_license_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'current_password' => ['nullable', 'required_with:password', 'current_password:buyer'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $profile = collect($validated)->except([
            'current_password', 'password', 'trade_license_document',
        ])->all();
        if (! empty($validated['password'])) {
            $profile['password'] = $validated['password'];
        }

        $buyer->update($profile);
        $buyer->primaryContact()->updateOrCreate(
            ['is_primary' => true],
            [
                'name' => $buyer->business_name,
                'email' => $buyer->email,
                'phone' => $buyer->phone,
                'designation' => 'Primary Contact',
            ]
        );

        if ($request->hasFile('trade_license_document')) {
            $file = $request->file('trade_license_document');
            $filename = uniqid() . '.' . strtolower($file->getClientOriginalExtension());
            $uploadPath = 'images/buyer-documents/';
            $file->move(public_path($uploadPath), $filename);

            $media = MediaFile::create([
                'mediable_type' => Buyer::class,
                'mediable_id' => $buyer->id,
                'disk' => 'public',
                'path' => $uploadPath . $filename,
                'mime' => $file->getClientMimeType(),
                'title' => 'Trade License Document',
                'uploaded_by' => null,
            ]);

            $existingDocument = $buyer->documents()->where('type', 'TradeLicense')->latest()->first();
            $documentData = [
                'number' => $validated['trade_license_no'] ?? null,
                'expiry_date' => $validated['trade_license_expiry'] ?? null,
                'media_id' => $media->id,
            ];

            if ($existingDocument) {
                $existingDocument->update($documentData);
            } else {
                BuyerDocument::create($documentData + ['buyer_id' => $buyer->id, 'type' => 'TradeLicense']);
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function invoices()
    {
        $invoices = $this->buyer()->invoices()->with('salesOrder')->latest()->paginate(10);

        return view('frontEnd.buyer.invoices.index', compact('invoices'));
    }

    public function invoice(Invoice $invoice)
    {
        abort_unless($invoice->buyer_id === $this->buyer()->id, 404);
        $invoice->load(['items.productSku.product', 'salesOrder', 'buyer']);

        return view('frontEnd.buyer.invoices.show', compact('invoice'));
    }
}
