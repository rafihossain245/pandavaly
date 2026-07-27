<script id="variants-js-script">
(function () {
    function initVariantBuilder($scope) {
        var $pickers = $scope.find('.attribute-picker');
        if (!$pickers.length) return;

        var $generateBtn = $scope.find('#generate-variants-btn');
        var $tableWrapper = $scope.find('#variants-table-wrapper');
        var $tbody = $scope.find('#variants-tbody');
        var $variantsJson = $scope.find('#variants_json');
        var existingDataEl = $scope.find('#existing-variants-data')[0] || $('#existing-variants-data')[0];
        var existingVariants = [];
        try {
            existingVariants = existingDataEl ? JSON.parse(existingDataEl.textContent || '[]') : [];
        } catch (e) {
            existingVariants = [];
        }

        var variantData = {};

        function buildExistingDataMap() {
            existingVariants.forEach(function (variant) {
                var ids = (variant.attribute_value_ids || []).map(String).sort();
                var key = ids.join('-');
                if (!key) return;
                variantData[key] = {
                    id: variant.id,
                    barcode: variant.barcode,
                    mrp: variant.mrp,
                    cost: variant.cost,
                    weight: variant.weight,
                    is_active: variant.is_active ? 1 : 0
                };
            });
        }

        function preselectFromExisting() {
            if (!existingVariants.length) return;
            var usedValueIds = {};
            existingVariants.forEach(function (variant) {
                (variant.attribute_value_ids || []).forEach(function (id) {
                    usedValueIds[String(id)] = true;
                });
            });

            $pickers.each(function () {
                var $picker = $(this);
                var $checkboxes = $picker.find('.attribute-value-checkbox');
                var hasMatch = false;
                $checkboxes.each(function () {
                    if (usedValueIds[String($(this).val())]) {
                        $(this).prop('checked', true);
                        hasMatch = true;
                    }
                });
                if (hasMatch) {
                    $picker.find('.attribute-toggle').prop('checked', true);
                    $picker.find('.attribute-values').show();
                }
            });
        }

        function getSelectedAttributeGroups() {
            var groups = [];
            $pickers.each(function () {
                var $picker = $(this);
                if (!$picker.find('.attribute-toggle').is(':checked')) return;

                var values = [];
                $picker.find('.attribute-value-checkbox:checked').each(function () {
                    values.push({ id: String($(this).val()), label: $(this).data('value') });
                });

                if (values.length) {
                    groups.push({
                        attribute_id: $picker.data('attribute_id'),
                        attribute_name: $picker.data('attribute_name'),
                        values: values
                    });
                }
            });
            return groups;
        }

        function cartesianProduct(groups) {
            return groups.reduce(function (acc, group) {
                var result = [];
                acc.forEach(function (combo) {
                    group.values.forEach(function (val) {
                        result.push(combo.concat([{
                            attribute_id: group.attribute_id,
                            attribute_name: group.attribute_name,
                            value_id: val.id,
                            value_label: val.label
                        }]));
                    });
                });
                return result;
            }, [[]]);
        }

        function captureCurrentRows() {
            $tbody.find('tr').each(function () {
                var $row = $(this);
                var key = $row.data('key');
                if (!key) return;
                variantData[key] = {
                    id: $row.data('sku-id') || null,
                    barcode: $row.find('.variant-barcode').val(),
                    mrp: $row.find('.variant-mrp').val(),
                    cost: $row.find('.variant-cost').val(),
                    weight: $row.find('.variant-weight').val(),
                    is_active: $row.find('.variant-active').is(':checked') ? 1 : 0
                };
            });
        }

        function renderVariants() {
            captureCurrentRows();

            var groups = getSelectedAttributeGroups();
            var combos = groups.length ? cartesianProduct(groups) : [];

            $tbody.empty();

            if (!combos.length) {
                $tableWrapper.hide();
                return;
            }

            combos.forEach(function (combo) {
                var ids = combo.map(function (c) { return c.value_id; }).sort();
                var key = ids.join('-');
                var label = combo.map(function (c) { return c.value_label; }).join(' / ');
                var saved = variantData[key] || {};

                var $row = $('<tr></tr>')
                    .attr('data-key', key)
                    .attr('data-attribute-value-ids', ids.join(','));

                if (saved.id) {
                    $row.attr('data-sku-id', saved.id);
                }

                $row.append($('<td></td>').text(label));
                $row.append($('<td></td>').append(
                    $('<input type="text" class="form-input w-full px-2 py-1 border border-gray-300 rounded-md variant-barcode">')
                        .val(saved.barcode || '')
                ));
                $row.append($('<td></td>').append(
                    $('<input type="number" step="0.01" min="0" class="form-input w-full px-2 py-1 border border-gray-300 rounded-md variant-mrp">')
                        .val(saved.mrp != null ? saved.mrp : '')
                ));
                $row.append($('<td></td>').append(
                    $('<input type="number" step="0.01" min="0" class="form-input w-full px-2 py-1 border border-gray-300 rounded-md variant-cost">')
                        .val(saved.cost != null ? saved.cost : '')
                ));
                $row.append($('<td></td>').append(
                    $('<input type="number" step="0.01" min="0" class="form-input w-full px-2 py-1 border border-gray-300 rounded-md variant-weight">')
                        .val(saved.weight != null ? saved.weight : '')
                ));
                $row.append($('<td class="text-center"></td>').append(
                    $('<input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 variant-active">')
                        .prop('checked', saved.is_active === undefined ? true : !!saved.is_active)
                ));

                $tbody.append($row);
            });

            $tableWrapper.show();
        }

        function serializeVariants() {
            var variants = [];
            $tbody.find('tr').each(function () {
                var $row = $(this);
                var idsAttr = $row.data('attribute-value-ids');
                var attributeValueIds = idsAttr ? String(idsAttr).split(',').filter(Boolean) : [];

                variants.push({
                    id: $row.data('sku-id') || null,
                    attribute_value_ids: attributeValueIds,
                    barcode: $row.find('.variant-barcode').val(),
                    mrp: $row.find('.variant-mrp').val(),
                    cost: $row.find('.variant-cost').val(),
                    weight: $row.find('.variant-weight').val(),
                    is_active: $row.find('.variant-active').is(':checked') ? 1 : 0
                });
            });
            $variantsJson.val(JSON.stringify(variants));
        }

        $pickers.on('change', '.attribute-toggle', function () {
            var $picker = $(this).closest('.attribute-picker');
            $picker.find('.attribute-values').toggle($(this).is(':checked'));
            if (!$(this).is(':checked')) {
                $picker.find('.attribute-value-checkbox').prop('checked', false);
            }
        });

        $generateBtn.on('click', function () {
            renderVariants();
        });

        $scope.closest('form').on('submit', function () {
            serializeVariants();
        });

        buildExistingDataMap();
        preselectFromExisting();

        if (existingVariants.length) {
            renderVariants();
        }
    }

    $(document).on('product-form-ready', function (e, $scope) {
        var $section = ($scope || $(document)).find('#variants-section');
        if (!$section.length) {
            $section = $('#variants-section');
        }
        initVariantBuilder($section);
    });

    $(function () {
        initVariantBuilder($('#variants-section'));
    });
})();
</script>
