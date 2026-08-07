{{--
    Shared behaviour for the product create/edit forms: the specifications repeater.

    Rows still post the original specification_name[] / specification_value[] pairs,
    so ProductController needs no change — only the row chrome and the delete
    affordance differ. Markup here mirrors the server-rendered rows in both pages.
--}}
@once
    {{-- Suggesting names keeps specs consistent between products; without it the
         same attribute ends up stored as "Weight", "weight" and "Net Weight". --}}
    <datalist id="pf-spec-names">
        <option value="Net Weight"></option>
        <option value="Country of Origin"></option>
        <option value="Ingredients"></option>
        <option value="Shelf Life"></option>
        <option value="Storage Instructions"></option>
        <option value="Packaging Type"></option>
        <option value="Flavour"></option>
        <option value="Certification"></option>
        <option value="Expiry Date"></option>
    </datalist>

    <script>
        (() => {
            const container = document.getElementById('specification-container');
            const addBtn = document.getElementById('add-spec');
            if (!container || !addBtn) return;

            const emptyState = document.getElementById('pf-spec-empty');
            const INPUT = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 ' +
                'placeholder-gray-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20';
            const ROW = 'mb-2.5 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2.5 ' +
                'sm:grid-cols-[minmax(0,1fr)_minmax(0,1.35fr)_auto]';
            const DEL = 'remove-spec flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 ' +
                'bg-white text-xs text-gray-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-500';

            const rowMarkup = () => `
                <div class="${ROW}">
                    <input type="text" name="specification_name[]" list="pf-spec-names"
                           class="col-span-2 sm:col-span-1 ${INPUT}" placeholder="e.g. Net Weight">
                    <input type="text" name="specification_value[]"
                           class="${INPUT}" placeholder="e.g. 500 g">
                    <button type="button" class="${DEL}" title="Remove this specification"
                            aria-label="Remove this specification">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>`;

            const rows = () => container.querySelectorAll(':scope > div');

            const syncEmptyState = () => {
                if (emptyState) emptyState.classList.toggle('hidden', rows().length > 0);
            };

            addBtn.addEventListener('click', () => {
                container.insertAdjacentHTML('beforeend', rowMarkup());
                syncEmptyState();
                const all = rows();
                all[all.length - 1].querySelector('input').focus();
            });

            // Delegated so rows rendered server-side (an existing product's specs)
            // are removable too, not just ones added in this session.
            container.addEventListener('click', (e) => {
                const btn = e.target.closest('.remove-spec');
                if (!btn) return;
                const row = btn.closest('div');
                if (row && row.parentElement === container) row.remove();
                syncEmptyState();
            });

            syncEmptyState();
        })();
    </script>
@endonce
