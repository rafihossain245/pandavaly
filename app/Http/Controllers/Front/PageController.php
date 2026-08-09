<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Render a storefront content page at /page/{slug}.
     */
    public function show(string $slug)
    {
        $page = Page::active()->where('slug', $slug)->firstOrFail();

        // A link-only entry has no body of its own — send visitors where it points
        // rather than showing them an empty page.
        if ($page->isLinkOnly()) {
            return redirect($page->link_url);
        }

        return view('frontEnd.page', compact('page'));
    }
}
