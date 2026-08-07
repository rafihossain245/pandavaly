<script id="variants-js-script">
/**
 * Product options & variants builder.
 *
 * Options (Size, Colour…) are picked from the global attribute library; the
 * variant grid below is the cartesian product of the selected values and is
 * rebuilt on every change. Edits already made to a row survive the rebuild
 * because rows are keyed by their sorted attribute-value ids.
 */
(function () {
    var MAX_OPTIONS = 3;
    var MANY_VARIANTS = 100;

    function readJson(id) {
        var el = document.getElementById(id);
        if (!el) return [];
        try { return JSON.parse(el.textContent || '[]') || []; } catch (e) { return []; }
    }

    function initVariantBuilder($section) {
        if (!$section || !$section.length || $section.data('pv-init')) return;
        $section.data('pv-init', true);

        var attributes = readJson('pv-attributes-data');
        var existing = readJson('pv-existing-data');

        var $hasVariants = $section.find('#pv-has-variants');
        var $body = $section.find('#pv-body');
        var $options = $section.find('#pv-options');
        var $addOption = $section.find('#pv-add-option');
        var $optionLimit = $section.find('#pv-option-limit');
        var $panel = $section.find('#pv-variants-panel');
        var $tbody = $section.find('#pv-tbody');
        var $countBadge = $section.find('#pv-count-badge');
        var $tooMany = $section.find('#pv-too-many');
        var $fileInputs = $section.find('#pv-file-inputs');
        var $json = $section.find('#variants_json');
        var $form = $section.closest('form');

        if (!attributes.length) {
            // No attributes configured — nothing to build, but still make sure a
            // submit does not carry a stale payload.
            $form.on('submit.pvariants', function () { $json.val(''); });
            return;
        }

        // Row state keyed by "3-7" (sorted attribute-value ids), so re-generating
        // the grid never loses what the admin already typed.
        var rowState = {};
        // Rows the admin explicitly removed — kept out until the options change.
        var removedKeys = {};
        var valueLookup = {};

        attributes.forEach(function (attribute) {
            attribute.values.forEach(function (value) {
                valueLookup[String(value.id)] = { value: value, attribute: attribute };
            });
        });

        function keyFor(ids) {
            return ids.map(String).sort(function (a, b) { return a - b; }).join('-');
        }

        function money(value) {
            return (value === null || value === undefined || value === '') ? '' : value;
        }

        /* ---------------------------------------------------------------- options */

        function usedAttributeIds($except) {
            var used = [];
            $options.find('.pv-option').each(function () {
                if ($except && this === $except[0]) return;
                var id = $(this).find('.pv-attr-select').val();
                if (id) used.push(String(id));
            });
            return used;
        }

        function optionMarkup(index) {
            return '' +
                '<div class="pv-option">' +
                    '<div class="pv-option-head">' +
                        '<label class="pv-option-label">Option ' + index + '</label>' +
                        '<select class="pv-attr-select"></select>' +
                        '<button type="button" class="pv-remove-option"><i class="fas fa-xmark"></i> Remove</button>' +
                    '</div>' +
                    '<div class="pv-values"></div>' +
                '</div>';
        }

        function refreshAttributeSelects() {
            $options.find('.pv-option').each(function (i) {
                var $option = $(this);
                // Scoped to the header label — the value chips are <label>s too.
                $option.find('.pv-option-label').text('Option ' + (i + 1));

                var $select = $option.find('.pv-attr-select');
                var current = $select.val();
                var taken = usedAttributeIds($option);

                $select.empty().append('<option value="">Choose an attribute…</option>');
                attributes.forEach(function (attribute) {
                    if (taken.indexOf(String(attribute.id)) !== -1) return;
                    $select.append(
                        $('<option></option>').val(attribute.id).text(attribute.name)
                    );
                });
                if (current) $select.val(current);
            });

            var count = $options.find('.pv-option').length;
            $addOption.toggle(count < MAX_OPTIONS && count < attributes.length);
            $optionLimit.toggle(count >= MAX_OPTIONS);
        }

        function renderValues($option) {
            var attributeId = $option.find('.pv-attr-select').val();
            var $values = $option.find('.pv-values').empty();

            if (!attributeId) {
                $values.append('<span class="pv-values-empty">Pick an attribute to see its values.</span>');
                return;
            }

            var attribute = attributes.filter(function (a) { return String(a.id) === String(attributeId); })[0];
            if (!attribute || !attribute.values.length) {
                $values.append('<span class="pv-values-empty">This attribute has no values yet.</span>');
                return;
            }

            attribute.values.forEach(function (value) {
                var $chip = $('<label class="pv-chip"></label>');
                $chip.append($('<input type="checkbox" class="pv-value-cb">').val(value.id).attr('data-label', value.value));
                if (attribute.display_type === 'swatch') {
                    $chip.append($('<span class="pv-dot"></span>').css('background', value.color || '#d1d5db'));
                }
                $chip.append($('<span></span>').text(value.value));
                $values.append($chip);
            });

            $values.append('<button type="button" class="pv-select-all">Select all</button>');
        }

        function addOption(attributeId, selectedValueIds) {
            if ($options.find('.pv-option').length >= MAX_OPTIONS) return;

            var $option = $(optionMarkup($options.find('.pv-option').length + 1));
            $options.append($option);
            refreshAttributeSelects();

            if (attributeId) {
                $option.find('.pv-attr-select').val(String(attributeId));
                renderValues($option);
                (selectedValueIds || []).forEach(function (id) {
                    $option.find('.pv-value-cb[value="' + id + '"]').prop('checked', true).closest('.pv-chip').addClass('is-on');
                });
            } else {
                renderValues($option);
            }

            return $option;
        }

        function selectedGroups() {
            var groups = [];
            $options.find('.pv-option').each(function () {
                var $option = $(this);
                var attributeId = $option.find('.pv-attr-select').val();
                if (!attributeId) return;

                var values = [];
                $option.find('.pv-value-cb:checked').each(function () {
                    values.push({ id: String($(this).val()), label: $(this).data('label') });
                });
                if (values.length) groups.push({ attributeId: attributeId, values: values });
            });
            return groups;
        }

        function cartesian(groups) {
            return groups.reduce(function (acc, group) {
                var out = [];
                acc.forEach(function (combo) {
                    group.values.forEach(function (value) { out.push(combo.concat([value])); });
                });
                return out;
            }, [[]]);
        }

        /* ---------------------------------------------------------------- grid */

        function captureRows() {
            $tbody.find('tr').each(function () {
                var $row = $(this);
                var key = $row.attr('data-key');
                if (!key) return;
                var state = rowState[key] || {};
                rowState[key] = $.extend(state, {
                    id: $row.attr('data-sku-id') || state.id || null,
                    sku: $row.find('.pv-f-sku').val(),
                    price: $row.find('.pv-f-price').val(),
                    compare_at_price: $row.find('.pv-f-compare').val(),
                    cost: $row.find('.pv-f-cost').val(),
                    stock_qty: $row.find('.pv-f-stock').val(),
                    weight: $row.find('.pv-f-weight').val(),
                    is_active: $row.find('.pv-f-active').is(':checked') ? 1 : 0
                });
            });
        }

        function basePrice() {
            var $price = $form.find('[name="selling_price"]');
            return $price.length ? $price.val() : '';
        }

        function buildRow(combo) {
            var ids = combo.map(function (c) { return c.id; });
            var key = keyFor(ids);
            var label = combo.map(function (c) { return c.label; }).join(' / ');
            var state = rowState[key] || {};

            var $row = $('<tr></tr>')
                .attr('data-key', key)
                .attr('data-value-ids', ids.join(','));
            if (state.id) $row.attr('data-sku-id', state.id);

            // Image cell — the real file input lives in a persistent container
            // outside the table so a grid rebuild never drops a chosen file.
            var $thumb = $('<div class="pv-thumb"><i class="fas fa-camera"></i>' +
                '<button type="button" class="pv-thumb-clear">&times;</button></div>').attr('data-key', key);
            var preview = previewFor(key, state);
            if (preview) {
                $thumb.addClass('has-img').empty()
                    .append($('<img>').attr('src', preview))
                    .append('<button type="button" class="pv-thumb-clear">&times;</button>');
            }
            $row.append($('<td></td>').append($thumb));

            // Swatch dots make a colour row identifiable at a glance.
            var $name = $('<div class="pv-variant-name"></div>');
            combo.forEach(function (c) {
                var meta = valueLookup[String(c.id)];
                if (meta && meta.attribute.display_type === 'swatch') {
                    $name.append($('<span class="pv-dot"></span>').css('background', meta.value.color || '#d1d5db'));
                }
            });
            $name.append($('<span></span>').text(label));
            $row.append($('<td></td>').append($name));

            function cell(cls, type, value, extra) {
                var $input = $('<input class="pv-input">').addClass(cls).attr('type', type).val(value);
                if (extra) $input.attr(extra);
                return $('<td></td>').append($input);
            }

            $row.append(cell('pv-f-sku', 'text', state.sku || ''));
            $row.append(cell('pv-f-price', 'number', money(state.price !== undefined ? state.price : basePrice()), { step: '0.01', min: '0' }));
            $row.append(cell('pv-f-compare', 'number', money(state.compare_at_price), { step: '0.01', min: '0' }));
            $row.append(cell('pv-f-cost', 'number', money(state.cost), { step: '0.01', min: '0' }));
            $row.append(cell('pv-f-stock', 'number', state.stock_qty !== undefined && state.stock_qty !== null ? state.stock_qty : 0, { step: '1', min: '0' }));
            $row.append(cell('pv-f-weight', 'number', money(state.weight), { step: '0.001', min: '0' }));

            $row.append($('<td class="pv-center"></td>').append(
                $('<input type="checkbox" class="pv-f-active">')
                    .prop('checked', state.is_active === undefined ? true : !!state.is_active)
            ));
            $row.append($('<td></td>').append(
                '<button type="button" class="pv-row-remove" title="Remove this variant"><i class="fas fa-trash"></i></button>'
            ));

            if (state.is_active === 0) $row.addClass('is-off');

            return $row;
        }

        function previewFor(key, state) {
            var $input = $fileInputs.find('input[data-key="' + key + '"]');
            if ($input.length && $input.data('preview')) return $input.data('preview');
            if (state.removeImage) return null;
            return state.image || null;
        }

        function render() {
            captureRows();

            var groups = selectedGroups();
            var combos = groups.length ? cartesian(groups) : [];

            // Drop any combination the admin deleted, unless the options changed
            // in a way that makes it new again.
            combos = combos.filter(function (combo) {
                return !removedKeys[keyFor(combo.map(function (c) { return c.id; }))];
            });

            $tbody.empty();
            combos.forEach(function (combo) { $tbody.append(buildRow(combo)); });

            var count = combos.length;
            $panel.toggle(count > 0);
            $countBadge.toggle(count > 0).text(count + (count === 1 ? ' variant' : ' variants'));
            $tooMany.toggle(count > MANY_VARIANTS);

            pruneFileInputs();
        }

        /* ---------------------------------------------------------------- images */

        function ensureFileInput(key) {
            var $input = $fileInputs.find('input[data-key="' + key + '"]');
            if (!$input.length) {
                $input = $('<input type="file" accept="image/*">')
                    .attr('name', 'variant_images[' + key + ']')
                    .attr('data-key', key);
                $fileInputs.append($input);
            }
            return $input;
        }

        function pruneFileInputs() {
            $fileInputs.find('input[type=file]').each(function () {
                var key = $(this).attr('data-key');
                if (!$tbody.find('tr[data-key="' + key + '"]').length) $(this).remove();
            });
        }

        $section.on('click', '.pv-thumb', function (e) {
            if ($(e.target).hasClass('pv-thumb-clear')) return;
            ensureFileInput($(this).attr('data-key')).trigger('click');
        });

        $section.on('click', '.pv-thumb-clear', function (e) {
            e.stopPropagation();
            var $thumb = $(this).closest('.pv-thumb');
            var key = $thumb.attr('data-key');

            $fileInputs.find('input[data-key="' + key + '"]').remove();
            rowState[key] = $.extend(rowState[key] || {}, { image: null, removeImage: true });

            $thumb.removeClass('has-img').empty()
                .append('<i class="fas fa-camera"></i>')
                .append('<button type="button" class="pv-thumb-clear">&times;</button>');
        });

        $fileInputs.on('change', 'input[type=file]', function () {
            var input = this;
            var key = $(input).attr('data-key');
            if (!input.files || !input.files[0]) return;

            var reader = new FileReader();
            reader.onload = function (e) {
                $(input).data('preview', e.target.result);
                rowState[key] = $.extend(rowState[key] || {}, { removeImage: false });
                $tbody.find('tr[data-key="' + key + '"] .pv-thumb')
                    .addClass('has-img')
                    .empty()
                    .append($('<img>').attr('src', e.target.result))
                    .append('<button type="button" class="pv-thumb-clear">&times;</button>');
            };
            reader.readAsDataURL(input.files[0]);
        });

        /* ---------------------------------------------------------------- events */

        $hasVariants.on('change', function () {
            var on = $(this).is(':checked');
            $body.toggle(on);
            if (on && !$options.find('.pv-option').length) addOption();
            if (!on) { $panel.hide(); $countBadge.hide(); }
            if (on) render();
        });

        $addOption.on('click', function () { addOption(); render(); });

        $section.on('click', '.pv-remove-option', function () {
            $(this).closest('.pv-option').remove();
            removedKeys = {};
            refreshAttributeSelects();
            render();
        });

        $section.on('change', '.pv-attr-select', function () {
            var $option = $(this).closest('.pv-option');
            renderValues($option);
            removedKeys = {};
            refreshAttributeSelects();
            render();
        });

        $section.on('change', '.pv-value-cb', function () {
            $(this).closest('.pv-chip').toggleClass('is-on', $(this).is(':checked'));
            removedKeys = {};
            render();
        });

        $section.on('click', '.pv-select-all', function () {
            var $values = $(this).closest('.pv-values');
            var allOn = $values.find('.pv-value-cb:not(:checked)').length === 0;
            $values.find('.pv-value-cb').prop('checked', !allOn)
                .closest('.pv-chip').toggleClass('is-on', !allOn);
            removedKeys = {};
            render();
        });

        $section.on('click', '.pv-row-remove', function () {
            var $row = $(this).closest('tr');
            captureRows();
            removedKeys[$row.attr('data-key')] = true;
            render();
        });

        $section.on('change', '.pv-f-active', function () {
            $(this).closest('tr').toggleClass('is-off', !$(this).is(':checked'));
        });

        $section.find('#pv-bulk-apply').on('click', function () {
            var price = $section.find('#pv-bulk-price').val();
            var stock = $section.find('#pv-bulk-stock').val();
            var cost = $section.find('#pv-bulk-cost').val();

            $tbody.find('tr').each(function () {
                if (price !== '') $(this).find('.pv-f-price').val(price);
                if (stock !== '') $(this).find('.pv-f-stock').val(stock);
                if (cost !== '') $(this).find('.pv-f-cost').val(cost);
            });

            $section.find('#pv-bulk-price, #pv-bulk-stock, #pv-bulk-cost').val('');
        });

        /* ---------------------------------------------------------------- submit */

        $form.on('submit.pvariants', function () {
            if (!$hasVariants.is(':checked')) { $json.val('[]'); return; }

            captureRows();

            var variants = [];
            $tbody.find('tr').each(function () {
                var $row = $(this);
                var key = $row.attr('data-key');
                var ids = ($row.attr('data-value-ids') || '').split(',').filter(Boolean);
                var state = rowState[key] || {};

                variants.push({
                    id: $row.attr('data-sku-id') || null,
                    key: key,
                    attribute_value_ids: ids,
                    sku: $row.find('.pv-f-sku').val(),
                    price: $row.find('.pv-f-price').val(),
                    compare_at_price: $row.find('.pv-f-compare').val(),
                    cost: $row.find('.pv-f-cost').val(),
                    stock_qty: $row.find('.pv-f-stock').val(),
                    weight: $row.find('.pv-f-weight').val(),
                    remove_image: state.removeImage ? 1 : 0,
                    is_active: $row.find('.pv-f-active').is(':checked') ? 1 : 0
                });
            });

            $json.val(JSON.stringify(variants));
        });

        /* ---------------------------------------------------------------- restore */

        if (existing.length) {
            existing.forEach(function (variant) {
                var ids = (variant.attribute_value_ids || []).map(String);
                if (!ids.length) return;
                rowState[keyFor(ids)] = {
                    id: variant.id,
                    sku: variant.sku,
                    price: variant.price,
                    compare_at_price: variant.compare_at_price,
                    cost: variant.cost,
                    stock_qty: variant.stock_qty,
                    weight: variant.weight,
                    image: variant.image,
                    is_active: variant.is_active ? 1 : 0
                };
            });

            // Rebuild the option rows from whichever attributes the saved
            // variants were built on.
            var byAttribute = {};
            existing.forEach(function (variant) {
                (variant.attribute_value_ids || []).forEach(function (id) {
                    var meta = valueLookup[String(id)];
                    if (!meta) return;
                    var attributeId = meta.attribute.id;
                    byAttribute[attributeId] = byAttribute[attributeId] || {};
                    byAttribute[attributeId][String(id)] = true;
                });
            });

            var attributeIds = Object.keys(byAttribute);
            if (attributeIds.length) {
                $hasVariants.prop('checked', true);
                $body.show();
                attributeIds.slice(0, MAX_OPTIONS).forEach(function (attributeId) {
                    addOption(attributeId, Object.keys(byAttribute[attributeId]));
                });
                render();
            }
        }

        if (!$options.find('.pv-option').length && $hasVariants.is(':checked')) addOption();
    }

    // The edit screen renders the same partial, so init on both paths.
    $(document).on('product-form-ready', function (e, $scope) {
        initVariantBuilder(($scope || $(document)).find('#variants-section'));
    });

    $(function () { initVariantBuilder($('#variants-section')); });
})();
</script>
