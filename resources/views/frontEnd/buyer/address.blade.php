@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Address</h3>
        </div>
        <div class="ba-panel-body">
            <p style="font-size:13px; color:#6b7280; margin-bottom:18px">
                This is your default delivery address. Checkout is pre-filled with it, and the
                delivery cost follows the district you choose here.
            </p>

            <form action="{{ route('buyer.address.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="ba-label" for="address">Street address <span style="color:#dc2626">*</span></label>
                        <input type="text" id="address" name="address" class="ba-input"
                               value="{{ old('address', $buyer->address) }}"
                               placeholder="ex: House no. / building / street / area" required>
                        @error('address')<div class="ba-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 ba-field">
                        <label class="ba-label" for="district_id">District <span style="color:#dc2626">*</span></label>
                        <select id="district_id" name="district_id" class="ba-input ba-select" data-placeholder="Select District" required>
                            <option value=""></option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" data-charge="{{ $district->delivery_charge }}"
                                    {{ (string) old('district_id', $buyer->district_id) === (string) $district->id ? 'selected' : '' }}>
                                    {{ $district->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('district_id')<div class="ba-err">{{ $message }}</div>@enderror
                        <div class="ba-help" id="charge-note"></div>
                    </div>

                    <div class="col-md-6 ba-field">
                        <label class="ba-label" for="thana_id">Thana / Upazila</label>
                        <select id="thana_id" name="thana_id" class="ba-input ba-select"
                                data-placeholder="Select Thana (Optional)"
                                data-selected="{{ old('thana_id', $buyer->thana_id) }}">
                            <option value=""></option>
                        </select>
                        @error('thana_id')<div class="ba-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="ba-label" for="city">City / Area</label>
                        <input type="text" id="city" name="city" class="ba-input" value="{{ old('city', $buyer->city) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="ba-label" for="postal_code">Postal code</label>
                        <input type="text" id="postal_code" name="postal_code" class="ba-input" value="{{ old('postal_code', $buyer->postal_code) }}">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="ba-btn-sm ba-btn-primary" style="padding:9px 22px">Save address</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection

@section('scripts')
<script>
// Runs after the theme's script.js ready handler, which calls $('.select2').select2()
// — initialising earlier lets it re-init on top of these and render them blank.
jQuery(function ($) {
    var thanasByDistrict = @json($thanasByDistrict);
    var $district = $('#district_id');
    var $thana    = $('#thana_id');
    var preselect = String($thana.data('selected') || '');

    $('.ba-select').each(function () {
        $(this).select2({ width: '100%', placeholder: $(this).data('placeholder') || 'Select' });
    });

    function repopulateThanas(keepSelected) {
        var thanas = thanasByDistrict[$district.val()] || [];
        $thana.empty().append('<option value=""></option>');

        thanas.forEach(function (thana) {
            var $option = $('<option>').val(thana.id).text(thana.name);
            if (keepSelected && String(thana.id) === preselect) {
                $option.prop('selected', true);
            }
            $thana.append($option);
        });

        $thana.trigger('change.select2');
    }

    function showCharge() {
        var selected = $district.find('option:selected');
        var charge   = parseFloat(selected.data('charge'));

        $('#charge-note').text(
            selected.val() && !isNaN(charge)
                ? 'Delivery to ' + $.trim(selected.text()) + ': ' + charge.toFixed(2) + ' BDT'
                : ''
        );
    }

    $district.on('change', function () { repopulateThanas(false); showCharge(); });
    repopulateThanas(true);
    showCharge();
});
</script>
@endsection
