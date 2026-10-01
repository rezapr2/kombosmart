'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        const $products = $('#category-products');
        const $data = $('.infinite-scroll-data');
        const $trigger = document.querySelector('.infinite-scroll-trigger');
        const $loaderWrap = $('.loadmore-wrapper');
        const $loaderBtn = $('.loadmore-btn');

        if (!$products.length || !$data.length) return;

        let currentPage = parseInt($data.data('current-page'), 10) || 1;
        const maxPages = parseInt($data.data('max-pages'), 10) || 1;
        let isLoading = false;
        let isDone = currentPage >= maxPages;

        function nextUrl(page) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('paged', String(page));
                return url.toString();
            } catch (e) {
                // Fallback for very old browsers
                const sep = window.location.href.indexOf('?') === -1 ? '?' : '&';
                return window.location.href + sep + 'paged=' + String(page);
            }
        }

        function setLoadingState(loading) {
            isLoading = loading;
            if (loading) {
                $loaderWrap.removeClass('is-hidden').addClass('is-loading');
                $loaderBtn.prop('disabled', true);
            } else {
                $loaderWrap.removeClass('is-loading');
                $loaderBtn.prop('disabled', true);
                if (isDone) {
                    $loaderWrap.addClass('is-hidden');
                }
            }
        }

        function appendProductsFromHtml(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nextContainer = doc.querySelector('.category-products');
            if (!nextContainer) return false;
            const nodes = Array.from(nextContainer.children || []);
            if (!nodes.length) return false;
            nodes.forEach((node) => {
                if (node.nodeType === 1) {
                    const imported = document.importNode(node, true);
                    $products.append(imported);
                }
            });
            return true;
        }

        async function loadNextPage() {
            if (isLoading || isDone) return;
            if (currentPage >= maxPages) {
                isDone = true;
                setLoadingState(false);
                return;
            }
            const nextPage = currentPage + 1;
            setLoadingState(true);
            try {
                const currentUrl = new URL(window.location.href);
                const orderby = currentUrl.searchParams.get('orderby') || 'date';
                const body = new URLSearchParams({
                    action: 'tanilchoob_category_products_load',
                    nonce: (window.kombosmart && kombosmart.ajax && kombosmart.ajax.nonce) ? kombosmart.ajax.nonce : '',
                    paged: String(nextPage),
                    query_vars: (window.kombosmart && kombosmart.ajax && kombosmart.ajax.posts) ? kombosmart.ajax.posts : '{}',
                    orderby: orderby,
                });

                const res = await fetch((window.kombosmart && kombosmart.ajax && kombosmart.ajax.url) ? kombosmart.ajax.url : '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString(),
                });
                const json = await res.json();
                if (json && json.success && json.data && json.data.html) {
                    $products.append(json.data.html);
                    currentPage = nextPage;
                    isDone = currentPage >= maxPages;
                } else {
                    isDone = true;
                }
            } catch (err) {
                console.error('[taxonomy-product_cat] Infinite load failed:', err);
                isDone = true;
            } finally {
                setLoadingState(false);
            }
        }

        // IntersectionObserver for infinite trigger
        if ($trigger && !isDone && 'IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        loadNextPage();
                    }
                });
            }, { rootMargin: '400px 0px 400px 0px', threshold: 0 });
            io.observe($trigger);
        } else {
            // Fallback: scroll listener
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(() => {
                    const nearBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 600);
                    if (nearBottom) loadNextPage();
                    ticking = false;
                });
            }, { passive: true });
        }

        // Initial state
        setLoadingState(false);
        if (isDone) {
            $loaderWrap.addClass('is-hidden');
        }
    });
})(jQuery);
