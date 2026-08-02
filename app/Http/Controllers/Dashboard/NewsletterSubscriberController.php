<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index()
    {
        $datas = NewsletterSubscriber::orderBy('created_at', 'desc')->paginate(20);

        return view('newsletter-subscribers.index', compact('datas'));
    }

    public function destroy(Request $request, $role, string $id)
    {
        try {
            $data = NewsletterSubscriber::find($request->item_id);
            if (! $data) {
                return response()->json(['success' => false, 'message' => 'Subscriber not found!']);
            }
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Subscriber removed.']);
    }
}
