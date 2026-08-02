<script src="{{asset('frontEnd/assets/')}}/js/jquery-3.7.1.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/popper.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/bootstrap.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/axios.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/toastr.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/sweetalert2.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/flat-picker.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/select2.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/swiper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/tween_max.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/wow.min.js"></script>
    <script src="{{asset('frontEnd/assets/')}}/js/script.js"></script>
    @yield('scripts')

    <script>
    /* ===== GLOBAL CART JS ===== */
    (function ($) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Show toast notification
        function showCartToast(msg) {
            var $t = $('#cartToast');
            $('#cartToastMsg').text(msg || 'Product added to cart!');
            $t.addClass('show');
            setTimeout(function () { $t.removeClass('show'); }, 3000);
        }

        // Update all cart UI elements after any cart change
        function syncCartUI(cart, html) {
            var count = cart.count || 0;
            var total = cart.total || 0;
            // badge in header
            $('.total-cart').text(count);
            // sidebar header count
            $('.cs-count').text(count);
            // sidebar subtotal
            $('.cs-total-val').text(numFmt(total));
            // sidebar items
            if (html) $('#cs-items-wrap').html(html);
            // floating cart widget
            updateFloatingCart(count, total);
        }

        function updateFloatingCart(count, total) {
            var $fc = $('.floating-cart');
            if (!$fc.length) return;
            if (count > 0) {
                $fc.show().find('div').eq(0).text('🛍️');
                $fc.find('div').eq(1).text(count + ' Items');
                $fc.find('div').eq(2).text('৳' + numFmt(total));
            } else {
                $fc.hide();
            }
        }

        function numFmt(n) {
            return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // ---- Add to Cart ----
        $(document).on('click', '.add-to-cart-btn', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var origHtml = $btn.html();
            var pid      = $btn.data('product');
            var $form    = $btn.closest('form');
            if (!$form.length) {
                var $buyForm = $('.product-buy-form');
                if ($buyForm.data('product') == pid) {
                    $form = $buyForm;
                }
            }
            var qty      = parseInt($btn.closest('[data-qty]').data('qty') || $form.find('[name=qty]').val() || 1);
            var skuId    = $form.find('.selected-sku-id').val() || '';
            var variantLabel = $form.find('.selected-variant-label').val() || '';

            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("cart.add") }}',
                method: 'POST',
                data: { _token: csrfToken, product_id: pid, qty: qty || 1, sku_id: skuId, variant_label: variantLabel },
                success: function (res) {
                    // Update count badge + pre-load sidebar content; sidebar stays closed
                    $('.total-cart').text(res.cart.count || 0);
                    $('.cs-count').text(res.cart.count || 0);
                    if (res.cart_items_html) $('#cs-items-wrap').html(res.cart_items_html);
                    $('.cs-total-val').text(numFmt(res.cart.total || 0));
                    updateFloatingCart(res.cart.count || 0, res.cart.total || 0);
                    showCartToast('Product added to cart!');
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON ? (xhr.responseJSON.error || 'Failed to add.') : 'Out of stock.';
                    toastr.error(msg);
                },
                complete: function () {
                    $btn.prop('disabled', false).html(origHtml);
                }
            });
        });

        // ---- Newsletter subscribe ----
        $(document).on('submit', '#newsletterForm', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $msg = $('#newsletterMsg');
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    $msg.css('color', '#16a34a').text(res.message || 'Subscribed!');
                    $form[0].reset();
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.email
                        ? xhr.responseJSON.errors.email[0]
                        : 'Could not subscribe. Please try again.';
                    $msg.css('color', '#e53935').text(msg);
                }
            });
        });

        // ---- Combo deal: add all products to cart ----
        $(document).on('click', '.add-combo-to-cart-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var productIds = $btn.data('products') || [];
            if (!productIds.length) return;

            $btn.prop('disabled', true);

            var addNext = function (index) {
                if (index >= productIds.length) {
                    $btn.prop('disabled', false);
                    showCartToast('Combo added to cart!');
                    return;
                }
                $.ajax({
                    url: '{{ route("cart.add") }}',
                    method: 'POST',
                    data: { _token: csrfToken, product_id: productIds[index], qty: 1 },
                    success: function (res) {
                        $('.total-cart').text(res.cart.count || 0);
                        $('.cs-count').text(res.cart.count || 0);
                        if (res.cart_items_html) $('#cs-items-wrap').html(res.cart_items_html);
                        $('.cs-total-val').text(numFmt(res.cart.total || 0));
                        updateFloatingCart(res.cart.count || 0, res.cart.total || 0);
                    },
                    complete: function () {
                        addNext(index + 1);
                    }
                });
            };

            addNext(0);
        });

        // ---- Wishlist toggle ----
        $(document).on('click', '.wishlist-toggle-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var pid = $btn.data('product');
            var $icon = $btn.find('i');

            $.ajax({
                url: '{{ route("wishlist.toggle") }}',
                method: 'POST',
                data: { _token: csrfToken, product_id: pid },
                success: function (res) {
                    if (res.wishlisted) {
                        $icon.removeClass('far').addClass('fas').css('color', '#e53935');
                    } else {
                        $icon.removeClass('fas').addClass('far').css('color', '#999');
                    }
                    $('.total-wishlist').text(res.count || 0);
                },
                error: function (xhr) {
                    if (xhr.status === 401 || xhr.status === 419) {
                        window.location.href = '{{ route("buyer.login") }}';
                    } else {
                        toastr.error('Failed to update wishlist.');
                    }
                }
            });
        });

        // ---- Sidebar remove ----
        $(document).on('click', '.sidebar-remove-btn', function () {
            var key = $(this).data('cart-key');
            $.ajax({
                url: '{{ route("cart.remove") }}',
                method: 'POST',
                data: { _token: csrfToken, cart_key: key },
                success: function (res) {
                    syncCartUI(res.cart, res.cart_items_html);
                },
                error: function () { toastr.error('Failed to remove.'); }
            });
        });

    })(jQuery);
    </script>

    <script>
        ! function(root, factory) {
            if (typeof module === "object" && module.exports) {
                module.exports = factory;
            } else {
                root.charming = factory;
            }
        }(this, function(node, opts) {
            "use strict";
            opts = opts || {};
            var tagName = opts.tagName || "span";
            var classPrefix = (opts.classPrefix != null) ? opts.classPrefix : "char";
            var i = 1;

            function inject(el) {
                var parent = el.parentNode;
                var text = el.nodeValue;
                var len = text.length;
                for (var j = 0; j < len; j++) {
                    var span = document.createElement(tagName);
                    if (classPrefix) {
                        span.className = classPrefix + i;
                        i++;
                    }
                    span.appendChild(document.createTextNode(text[j]));
                    parent.insertBefore(span, el);
                }
                parent.removeChild(el);
            }

            function traverse(el) {
                var childNodes = [].slice.call(el.childNodes);
                for (var k = 0; k < childNodes.length; k++) {
                    traverse(childNodes[k]);
                }
                if (el.nodeType === Node.TEXT_NODE) {
                    inject(el);
                }
            }

            traverse(node);
            return node;
        });
    </script>
    <script>
        // The Slideshow class.
        class Slideshow {
            constructor(el) {

                this.DOM = {
                    el: el
                };

                this.config = {
                    slideshow: {
                        delay: 3000,
                        pagination: {
                            duration: 3,
                        }
                    }
                };

                // Set the slideshow
                this.init();

            }
            init() {

                var self = this;

                // Charmed title
                this.DOM.slideTitle = this.DOM.el.querySelectorAll('.slide-title');
                this.DOM.slideTitle.forEach((slideTitle) => {
                    charming(slideTitle);
                });

                // Set the slider
                this.slideshow = new Swiper(this.DOM.el, {

                    loop: true,
                    // autoplay: false,
                    autoplay: {
                        delay: this.config.slideshow.delay,
                        disableOnInteraction: false,
                    },
                    speed: 500,
                    preloadImages: true,
                    updateOnImagesReady: true,
                    // The hero can sit in a flex column beside side banners,
                    // so re-measure when the container/layout changes.
                    observer: true,
                    observeParents: true,
                    resizeObserver: true,

                    pagination: {
                        el: '.slideshow-pagination',
                        clickable: true,
                        bulletClass: 'slideshow-pagination-item',
                        bulletActiveClass: 'active',
                        clickableClass: 'slideshow-pagination-clickable',
                        modifierClass: 'slideshow-pagination-',
                        renderBullet: function(index, className) {

                            var slideIndex = index,
                                number = (index <= 8) ? '0' + (slideIndex + 1) : (slideIndex + 1);

                            var paginationItem = '<span class="slideshow-pagination-item">';
                            paginationItem += '<span class="pagination-number">' + number + '</span>';
                            paginationItem = (index <= 8) ? paginationItem +
                                '<span class="pagination-separator"><span class="pagination-separator-loader"></span></span>' :
                                paginationItem;
                            paginationItem += '</span>';

                            return paginationItem;

                        },
                    },

                    // Navigation arrows
                    navigation: {
                        nextEl: '.slideshow-navigation-button.next',
                        prevEl: '.slideshow-navigation-button.prev',
                    },

                    // And if we need scrollbar
                    scrollbar: {
                        el: '.swiper-scrollbar',
                    },

                    on: {
                        init: function() {
                            self.animate('next');
                        },
                    }

                });

                // Init/Bind events.
                this.initEvents();

            }
            initEvents() {

                this.slideshow.on('paginationUpdate', (swiper, paginationEl) => this.animatePagination(swiper,
                    paginationEl));

                this.slideshow.on('slideNextTransitionStart', () => this.animate('next'));

                this.slideshow.on('slidePrevTransitionStart', () => this.animate('prev'));

            }
            animate(direction = 'next') {

                // Get the active slide. Slides may be image-only (no caption),
                // so every title lookup below is optional.
                this.DOM.activeSlide = this.DOM.el.querySelector('.swiper-slide-active');
                if (!this.DOM.activeSlide) {
                    return;
                }
                this.DOM.activeSlideImg = this.DOM.activeSlide.querySelector('.slide-image');
                this.DOM.activeSlideTitle = this.DOM.activeSlide.querySelector('.slide-title');
                this.DOM.activeSlideTitleLetters = this.DOM.activeSlideTitle
                    ? this.DOM.activeSlideTitle.querySelectorAll('span')
                    : [];

                // Reverse if prev
                this.DOM.activeSlideTitleLetters = direction === "next" ? this.DOM.activeSlideTitleLetters : [].slice
                    .call(this.DOM.activeSlideTitleLetters).reverse();

                // Get old slide
                this.DOM.oldSlide = direction === "next" ? this.DOM.el.querySelector('.swiper-slide-prev') : this.DOM.el
                    .querySelector('.swiper-slide-next');
                if (this.DOM.oldSlide) {
                    // Get parts
                    this.DOM.oldSlideTitle = this.DOM.oldSlide.querySelector('.slide-title');
                    this.DOM.oldSlideTitleLetters = this.DOM.oldSlideTitle
                        ? this.DOM.oldSlideTitle.querySelectorAll('span')
                        : [];
                    // Animate
                    this.DOM.oldSlideTitleLetters.forEach((letter, pos) => {
                        TweenMax.to(letter, .3, {
                            ease: Quart.easeIn,
                            delay: (this.DOM.oldSlideTitleLetters.length - pos - 1) * .04,
                            y: '50%',
                            opacity: 0
                        });
                    });
                }

                // Animate title
                this.DOM.activeSlideTitleLetters.forEach((letter, pos) => {
                    TweenMax.to(letter, .6, {
                        ease: Back.easeOut,
                        delay: pos * .05,
                        startAt: {
                            y: '50%',
                            opacity: 0
                        },
                        y: '0%',
                        opacity: 1
                    });
                });

                // Animate background
                if (this.DOM.activeSlideImg) {
                    TweenMax.to(this.DOM.activeSlideImg, 1.5, {
                        ease: Expo.easeOut,
                        startAt: {
                            x: direction === 'next' ? 200 : -200
                        },
                        x: 0,
                    });
                }

            }
            animatePagination(swiper, paginationEl) {

                // Animate pagination
                this.DOM.paginationItemsLoader = paginationEl.querySelectorAll('.pagination-separator-loader');
                this.DOM.activePaginationItem = paginationEl.querySelector('.slideshow-pagination-item.active');
                this.DOM.activePaginationItemLoader = this.DOM.activePaginationItem.querySelector(
                    '.pagination-separator-loader');

                // Reset and animate
                TweenMax.set(this.DOM.paginationItemsLoader, {
                    scaleX: 0
                });
                TweenMax.to(this.DOM.activePaginationItemLoader, this.config.slideshow.pagination.duration, {
                    startAt: {
                        scaleX: 0
                    },
                    scaleX: 1,
                });


            }

        }

        // Instantiate only on pages that have the slideshow element
        const slideshowEl = document.querySelector('.slideshow');
        const slideshow = slideshowEl ? new Slideshow(slideshowEl) : null;

        // Swiper measures slide widths at init. Several sliders here live in
        // flex/grid containers (e.g. the hero sits beside its side banners),
        // so re-measure once layout and images have settled, otherwise the
        // loop translate is computed off the wrong width and slides appear
        // shifted or clipped.
        (function () {
            function updateAllSwipers() {
                document.querySelectorAll('.swiper, .swiper-container').forEach(function (el) {
                    if (el.swiper) {
                        el.swiper.update();
                    }
                });
            }
            window.addEventListener('load', function () {
                updateAllSwipers();
                setTimeout(updateAllSwipers, 250);
            });
            window.addEventListener('resize', updateAllSwipers);
        })();
    </script>
    <script>
        // Simple cart interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Quantity controls
            const quantityBtns = document.querySelectorAll('.quantity-btn');
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('.quantity-input');
                    let value = parseInt(input.value);

                    if (this.querySelector('.fa-plus')) {
                        value++;
                    } else if (this.querySelector('.fa-minus') && value > 1) {
                        value--;
                    }

                    input.value = value;

                    // Add animation
                    this.parentElement.classList.add('animate-add');
                    setTimeout(() => {
                        this.parentElement.classList.remove('animate-add');
                    }, 500);

                    updateCartTotal();
                });
            });

            // Remove items
            const removeBtns = document.querySelectorAll('.remove-btn');
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cartItem = this.closest('.cart-item');
                    cartItem.style.opacity = '0';
                    setTimeout(() => {
                        cartItem.remove();
                        updateCartTotal();
                        updateCartCount();
                    }, 300);
                });
            });

            // Update cart total (simplified)
            function updateCartTotal() {
                // In a real implementation, you would calculate based on actual prices and quantities
                console.log('Cart updated');
            }

            // Update cart count
            function updateCartCount() {
                const cartCount = document.querySelector('.cart-count');
                const currentCount = parseInt(cartCount.textContent);
                cartCount.textContent = currentCount - 1;

                if (currentCount - 1 === 0) {
                    document.querySelector('.cart-items').innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <p>Your cart is empty</p>
                                <a href="#" class="continue-shopping" data-bs-dismiss="offcanvas">Start Shopping</a>
                            </div>
                        `;
                }
            }
        });
    </script>