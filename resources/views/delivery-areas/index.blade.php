@extends('layout.app')
@section('meta-information')
    <title>Delivery Areas</title>
@endsection
@section('css')
<style>
    .states-table .states-table-container { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); overflow: hidden; }
    .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white; }
    .states-table .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
    .states-table .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: .85rem .75rem; font-weight: 600; color: #495057; }
    .states-table .table tbody td { padding: .6rem .75rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
    .states-table .table tbody tr:hover { background-color: #f8f9fa; }
    .da-charge { width: 120px; text-align: right; }
    .da-off td { background: #fafafa; color: #9ca3af; }
    .da-bar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #e9ecef; background: #fff; }
    .da-note { font-size: .8rem; color: #6b7280; }
    .da-err { color: #b91c1c; font-size: .8rem; margin-top: 4px; }
    .da-sticky { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e9ecef; padding: 14px 18px; display: flex; justify-content: flex-end; gap: 10px; }
</style>
@endsection
@section('main-content')
    @php
        // Same as every other dashboard screen: the {role} route segment comes
        // from the signed-in user's role, not from the layout (sections render
        // before it does, so the sidebar's $roleSlug is not in scope here).
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());
    @endphp
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header px-6 py-4 flex justify-between items-center">
                <h2 class="text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-truck-fast mr-2"></i>Delivery Areas
                </h2>
                <button type="button" class="btn btn-light" onclick="document.getElementById('daAdd').classList.toggle('hidden')">
                    <i class="fas fa-plus mr-1"></i> Add Area
                </button>
            </div>

            {{-- Add an area. Hidden until asked for: the 64 districts are already
                 seeded, so this is for a shop that splits one (Dhaka City vs
                 Dhaka District, say) rather than everyday use. --}}
            <div id="daAdd" class="{{ $errors->hasAny(['name', 'name_bn']) ? '' : 'hidden' }}" style="padding: 18px; border-bottom: 1px solid #e9ecef; background: #f9fafb;">
                <form method="POST" action="{{ route('role.delivery-areas.store', ['role' => $roleSlug]) }}"
                      class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1" for="name">Area name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="form-control" placeholder="e.g. Dhaka City" style="min-width: 220px;">
                        @error('name')<p class="da-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1" for="name_bn">Bangla name</label>
                        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}"
                               class="form-control" placeholder="ঢাকা সিটি" style="min-width: 180px;">
                        @error('name_bn')<p class="da-err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1" for="delivery_charge">Charge (৳)</label>
                        <input type="number" name="delivery_charge" id="delivery_charge" step="1" min="0"
                               value="{{ old('delivery_charge', $defaultCharge) }}" required class="form-control da-charge">
                        @error('delivery_charge')<p class="da-err">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700" style="padding-bottom: 8px;">
                        <input type="checkbox" name="is_active" value="1" checked> Available
                    </label>
                    <button type="submit" class="btn btn-primary" style="margin-bottom: 2px;">Add</button>
                </form>
            </div>

            <div class="da-bar">
                <form method="GET" action="{{ route('role.delivery-areas.index', ['role' => $roleSlug]) }}" class="flex gap-2">
                    <input type="search" name="q" value="{{ $search }}" class="form-control"
                           placeholder="Search area…" style="min-width: 240px;">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    @if($search !== '')
                        <a href="{{ route('role.delivery-areas.index', ['role' => $roleSlug]) }}" class="btn btn-link">Clear</a>
                    @endif
                </form>
                <p class="da-note mb-0">
                    <i class="fas fa-circle-info mr-1"></i>
                    Shoppers pick one of these at checkout and its charge is added to the order.
                    An area that is switched off disappears from the picker; orders already placed keep theirs.
                    An order with no area falls back to ৳{{ number_format($defaultCharge) }}.
                </p>
            </div>

            @if($errors->has('charges') || $errors->has('charges.*'))
                <p class="da-err" style="padding: 12px 18px 0;">{{ $errors->first('charges') ?: $errors->first('charges.*') }}</p>
            @endif

            <form method="POST" action="{{ route('role.delivery-areas.update', ['role' => $roleSlug]) }}">
                @csrf
                @method('PUT')
                <div class="table-responsive overflow-x-auto" style="padding: 0 15px;">
                    <table class="table table-hover min-w-full">
                        <thead>
                            <tr>
                                <th style="width: 60px;">SL</th>
                                <th>Area</th>
                                <th style="width: 160px;">Delivery charge</th>
                                <th style="width: 130px;">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($districts as $i => $district)
                                <tr class="{{ $district->is_active ? '' : 'da-off' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $district->name }}</strong>
                                        @if(filled($district->name_bn))
                                            <span class="text-gray-500">— {{ $district->name_bn }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="1" min="0" required
                                                   name="charges[{{ $district->id }}]"
                                                   value="{{ old('charges.' . $district->id, (int) $district->delivery_charge) }}"
                                                   class="form-control da-charge">
                                        </div>
                                    </td>
                                    <td>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="active[{{ $district->id }}]" value="1"
                                                   {{ $district->is_active ? 'checked' : '' }}>
                                            <span>Serving</span>
                                        </label>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500" style="padding: 28px;">
                                        No area matches “{{ $search }}”.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($districts->isNotEmpty())
                    <div class="da-sticky">
                        <span class="da-note" style="margin-right:auto;">
                            {{ $districts->count() }} {{ Str::plural('area', $districts->count()) }} shown{{ $search !== '' ? ' (filtered — only these are saved)' : '' }}.
                        </span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save changes
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
