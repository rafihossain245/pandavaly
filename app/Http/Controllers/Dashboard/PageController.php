<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Pages grouped by the footer column they belong to, so the admin sees the
     * footer's actual shape rather than a flat list.
     */
    public function index(Request $request)
    {
        $query = Page::with('category')->ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (int) $request->is_active);
        }

        $pages = $query->get();
        $categories = PageCategory::ordered()->get();

        // Keyed by category id, with unfiled pages under '' so they are never
        // invisible just because they are in no footer column.
        $grouped = $pages->groupBy(fn ($page) => $page->category_id ?? '');

        $stats = [
            'pages' => Page::count(),
            'live' => Page::active()->count(),
            'columns' => PageCategory::active()->count(),
            'empty' => Page::whereNull('link_url')
                ->where(fn ($q) => $q->whereNull('content')->orWhere('content', ''))
                ->count(),
        ];

        return view('pages.index', compact('pages', 'categories', 'grouped', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        try {
            $page = DB::transaction(function () use ($request, $validated) {
                return Page::create([
                    'category_id' => ($validated['category_id'] ?? null) ?: null,
                    'title' => $validated['title'],
                    'slug' => Page::uniqueSlug(($validated['slug'] ?? null) ?: $validated['title']),
                    'content' => $validated['content'] ?? null,
                    'link_url' => ($validated['link_url'] ?? null) ?: null,
                    'position' => $this->nextPosition(($validated['category_id'] ?? null) ?: null),
                    'is_active' => $request->boolean('is_active'),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Page created successfully.',
                'data' => $page,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $role, string $id)
    {
        $page = Page::findOrFail($id);
        $validated = $request->validate($this->rules($page->id), $this->messages());

        try {
            // Moving a page to another column puts it at that column's end; staying
            // put keeps its order. Cast first — the form sends "1" as a string, and
            // a strict compare against the int column would always look like a move.
            $categoryId = filled($validated['category_id'] ?? null)
                ? (int) $validated['category_id']
                : null;

            $position = $categoryId === $page->category_id
                ? $page->position
                : $this->nextPosition($categoryId);

            $page->update([
                'category_id' => $categoryId,
                'title' => $validated['title'],
                'slug' => Page::uniqueSlug(($validated['slug'] ?? null) ?: $validated['title'], $page->id),
                'content' => $validated['content'] ?? null,
                'link_url' => ($validated['link_url'] ?? null) ?: null,
                'position' => $position,
                'is_active' => $request->boolean('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully.',
                'data' => $page->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, string $role, string $id)
    {
        try {
            Page::findOrFail($request->input('item_id', $id))->delete();

            return response()->json(['success' => true, 'message' => 'Page deleted successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Persist drag-and-drop ordering of links within a footer column. */
    public function reorder(Request $request, string $role)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:pages,id',
        ]);

        foreach ($request->ids as $position => $id) {
            Page::where('id', $id)->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/i',
                Rule::unique('pages', 'slug')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'category_id' => 'nullable|exists:page_categories,id',
            'content' => 'nullable|string',
            'link_url' => 'nullable|string|max:255',
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Give the page a title — this is the text shown in the footer.',
            'slug.regex' => 'The URL slug may only contain letters, numbers and dashes.',
            'slug.unique' => 'Another page already uses that URL slug.',
            'category_id.exists' => 'That footer column no longer exists.',
        ];
    }

    private function nextPosition(?int $categoryId): int
    {
        return (int) Page::where('category_id', $categoryId)->max('position') + 1;
    }
}
