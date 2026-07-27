<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Models\Buyer;
use App\Models\BuyerDocument;
use App\Models\Invoice;
use App\Models\MediaFile;
use App\Models\SalesOrder;
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

    public function index()
    {
        $buyer = $this->buyer();
        $stats = [
            'orders' => $buyer->orders()->count(),
            'pending' => $buyer->orders()->whereIn('status', ['pending', 'confirmed', 'packed'])->count(),
            'invoices' => $buyer->invoices()->count(),
            'outstanding' => $buyer->invoices()->whereIn('status', ['unpaid', 'partial'])->sum('balance'),
        ];
        $recentOrders = $buyer->orders()->latest()->limit(5)->get();

        return view('frontEnd.buyer.dashboard', compact('buyer', 'stats', 'recentOrders'));
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

        return view('frontEnd.buyer.profile', ['buyer' => $buyer, 'tradeLicenseDocument' => $tradeLicenseDocument]);
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
