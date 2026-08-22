{{--
    Live product suggestions for the header search box.

    Attaches itself to every `.header-search form` on the page (the layout renders
    a desktop and a mobile one), so there is no markup to duplicate — the dropdown
    element is created per form at runtime. Full results remain the Shop page
    filtered by `q`, which the footer row links to.
--}}
<style>
    .header-search { position: relative; }

    .hs-suggest {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        box-shadow: 0 8px 26px rgba(17, 24, 39, .13);
        overflow: hidden;
        display: none;
    }
    .hs-suggest.is-open { display: block; }

    .hs-list { list-style: none; margin: 0; padding: 4px; max-height: 380px; overflow-y: auto; }

    .hs-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 10px;
        border-radius: 6px;
        text-decoration: none;
        color: #111;
    }
    .hs-item:hover,
    .hs-item.is-active { background: #f7f8fa; color: #111; }

    .hs-thumb {
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        border: 1px solid #f0f0f0;
        border-radius: 5px;
        background: #fafafa;
        object-fit: contain;
        padding: 3px;
    }
    .hs-thumb-blank { display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 14px; }

    .hs-body { min-width: 0; flex: 1; }
    .hs-name {
        font-size: 13.5px;
        font-weight: 500;
        line-height: 1.35;
        margin-bottom: 3px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hs-name mark { background: var(--primary-soft); color: inherit; padding: 0; font-weight: 700; }
    .hs-price { font-size: 13px; font-weight: 700; color: var(--primary); }
    .hs-compare { font-size: 11.5px; color: #9aa8b8; text-decoration: line-through; margin-left: 6px; font-weight: 400; }
    .hs-meta { font-size: 11.5px; color: #9aa8b8; margin-top: 2px; }
    .hs-out { color: #dc2626; }

    .hs-foot {
        display: block;
        padding: 10px 14px;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
        text-align: center;
    }
    .hs-foot:hover { background: #f3f4f6; color: var(--primary); }

    .hs-note { padding: 22px 16px; text-align: center; font-size: 13px; color: #8a8a8a; }
    .hs-note i { display: block; font-size: 20px; color: #d8dde3; margin-bottom: 8px; }
</style>

<script>
(function () {
    var ENDPOINT = @json(route('search.suggestions'));
    var SHOP_URL = @json(route('shop'));
    var MIN_CHARS = 2;
    var DEBOUNCE_MS = 250;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /** Bolds the typed term inside a product name. Escapes first. */
    function highlight(name, term) {
        var safe = escapeHtml(name);
        if (!term) return safe;

        var pattern = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp('(' + pattern + ')', 'gi'), '<mark>$1</mark>');
    }

    function attach(form) {
        var input = form.querySelector('input[name="q"]');
        if (!input) return;

        var panel = document.createElement('div');
        panel.className = 'hs-suggest';
        form.parentNode.appendChild(panel);

        input.setAttribute('autocomplete', 'off');

        var timer = null;
        var controller = null;
        var items = [];
        var activeIndex = -1;

        function close() {
            panel.classList.remove('is-open');
            activeIndex = -1;
        }

        function open() {
            panel.classList.add('is-open');
        }

        function setActive(index) {
            if (!items.length) return;

            activeIndex = (index + items.length) % items.length;
            items.forEach(function (el, i) {
                el.classList.toggle('is-active', i === activeIndex);
            });
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function render(data) {
            var term = data.query || '';

            if (!data.results.length) {
                panel.innerHTML = '<div class="hs-note"><i class="fas fa-magnifying-glass"></i>'
                    + 'No products found for &ldquo;' + escapeHtml(term) + '&rdquo;</div>';
                items = [];
                open();
                return;
            }

            var html = '<ul class="hs-list">';

            data.results.forEach(function (product) {
                var thumb = product.thumbnail
                    ? '<img class="hs-thumb" src="' + escapeHtml(product.thumbnail) + '" alt="">'
                    : '<div class="hs-thumb hs-thumb-blank"><i class="fas fa-image"></i></div>';

                // Brand is highlighted too — otherwise a row that matched only on
                // its brand looks like an unrelated result.
                var meta = [];
                if (product.brand) meta.push(highlight(product.brand, term));
                if (!product.in_stock) meta.push('<span class="hs-out">Out of stock</span>');

                html += '<li><a class="hs-item" href="' + escapeHtml(product.url) + '">'
                    + thumb
                    + '<div class="hs-body">'
                    + '<div class="hs-name">' + highlight(product.name, term) + '</div>'
                    + '<div class="hs-price">' + escapeHtml(product.price)
                    + (product.compare_at ? '<span class="hs-compare">' + escapeHtml(product.compare_at) + '</span>' : '')
                    + '</div>'
                    + (meta.length ? '<div class="hs-meta">' + meta.join(' &middot; ') + '</div>' : '')
                    + '</div></a></li>';
            });

            html += '</ul>';

            // Only worth offering when there is more than the dropdown shows.
            if (data.total > data.results.length) {
                html += '<a class="hs-foot" href="' + SHOP_URL + '?q=' + encodeURIComponent(term) + '">'
                    + 'See all ' + data.total + ' results</a>';
            }

            panel.innerHTML = html;
            items = Array.prototype.slice.call(panel.querySelectorAll('.hs-item'));
            activeIndex = -1;
            open();
        }

        function fetchSuggestions(term) {
            if (controller) controller.abort();
            controller = new AbortController();

            fetch(ENDPOINT + '?q=' + encodeURIComponent(term), {
                signal: controller.signal,
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) { return response.json(); })
                .then(render)
                .catch(function (error) {
                    if (error.name !== 'AbortError') close();
                });
        }

        input.addEventListener('input', function () {
            var term = input.value.trim();
            clearTimeout(timer);

            if (term.length < MIN_CHARS) {
                if (controller) controller.abort();
                close();
                return;
            }

            timer = setTimeout(function () { fetchSuggestions(term); }, DEBOUNCE_MS);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= MIN_CHARS && panel.innerHTML) open();
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') return close();

            if (!panel.classList.contains('is-open') || !items.length) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActive(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(activeIndex - 1);
            } else if (event.key === 'Enter' && activeIndex > -1) {
                // Let a plain Enter submit the form and land on the Shop page.
                event.preventDefault();
                window.location.href = items[activeIndex].getAttribute('href');
            }
        });

        document.addEventListener('click', function (event) {
            if (!form.parentNode.contains(event.target)) close();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.header-search form').forEach(attach);
    });
})();
</script>
