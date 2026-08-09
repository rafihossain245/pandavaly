{{-- One footer link. Shared by the column groups and the unfiled group. --}}
@foreach ($rows as $row)
    <li class="cms-row" data-id="{{ $row->id }}">
        <span class="cms-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>

        <div class="cms-row-main">
            <div class="cms-row-title">{{ $row->title }}</div>
            <div class="cms-row-url">{{ $row->isLinkOnly() ? $row->link_url : '/page/' . $row->slug }}</div>
        </div>

        <div class="cms-badges">
            @if ($row->is_active)
                <span class="cms-badge cms-b-live">Visible</span>
            @else
                <span class="cms-badge cms-b-hidden">Hidden</span>
            @endif

            @if ($row->isLinkOnly())
                <span class="cms-badge cms-b-link">Link only</span>
            @elseif (! $row->hasContent())
                <span class="cms-badge cms-b-empty">No content yet</span>
            @endif
        </div>

        <div class="cms-row-actions">
            <a href="{{ $row->url() }}" target="_blank" class="cms-btn-icon" title="View on site">
                <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
            <button class="cms-btn-icon edit-item-btn" title="Edit"
                    data-item_id="{{ $row->id }}"
                    data-title="{{ $row->title }}"
                    data-slug="{{ $row->slug }}"
                    data-link_url="{{ $row->link_url }}"
                    data-category_id="{{ $row->category_id }}"
                    data-is_active="{{ $row->is_active ? 1 : 0 }}"
                    data-content="{{ $row->content }}">
                <i class="fas fa-pen"></i>
            </button>
            {{-- Data attributes rather than an inline onclick: a title containing a
                 quote would otherwise break out of the attribute. --}}
            <button class="cms-btn-icon danger delete-item-btn" title="Delete"
                    data-item_id="{{ $row->id }}"
                    data-title="{{ $row->title }}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </li>
@endforeach
