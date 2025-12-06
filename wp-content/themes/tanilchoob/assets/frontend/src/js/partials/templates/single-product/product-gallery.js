'use strict';

(function ($) {
    jQuery(document).ready(function ($) {
        // Initialize product gallery with Swiper
        if ($('.product-gallery-container').length) {
            // Initialize thumbnail slider
            var galleryThumbs = new Swiper('.gallery-thumbs', {
                slidesPerView: 'auto',
                direction: 'vertical',
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
            });
            
            // Initialize main slider
            var galleryMain = new Swiper('.gallery-main', {
                slidesPerView: 1,
                spaceBetween: 10,
                thumbs: {
                    swiper: galleryThumbs
                },
                zoom: {
                    maxRatio: 2,
                    toggle: true
                }
            });
            
            // Add click event to thumbnails
            $('.gallery-thumbs .swiper-slide').on('click', function (e) {
                // If this slide is the "view all" button, open lightbox and stop
                var isMore = $(this).find('.open-gallery-lightbox').length > 0;
                if (isMore) {
                    e.preventDefault();
                    var $btn = $(this).find('.open-gallery-lightbox');
                    var imagesJson = $btn.attr('data-gallery');
                    try {
                        var images = JSON.parse(imagesJson || '[]');
                        openGalleryLightbox(images);
                    } catch (err) {
                        // Silently fail if JSON parse error
                    }
                    return;
                }
                var index = $(this).index();
                galleryMain.slideTo(index);
            });

            // Direct click binding on the "view all" button to ensure reliability
            $(document).on('click', '.open-gallery-lightbox', function (e) {
                e.preventDefault();
                var imagesJson = $(this).attr('data-gallery');
                try {
                    var images = JSON.parse(imagesJson || '[]');
                    openGalleryLightbox(images);
                } catch (err) {
                    // ignore
                }
            });

            // OS Share modal on share button
            $(document).on('click', '.share-button', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var url = $btn.attr('data-share-url') || window.location.href;
                var title = $btn.attr('data-share-title') || document.title;
                var text = $btn.find('span').text() || '';
                if (navigator.share) {
                    navigator.share({ title: title, text: text, url: url }).catch(function () { /* ignore */ });
                } else {
                    // Fallback: copy link to clipboard silently
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).catch(function () { /* ignore */ });
                    }
                }
            });

            // Lightbox creation and initialization
            function openGalleryLightbox(images) {
                if (!images || !images.length) return;

                var $existing = $('#gallery-lightbox-modal');
                if ($existing.length === 0) {
                    var modalHtml = [
                        '<div id="gallery-lightbox-modal" class="gallery-lightbox-modal items-center justify-center" role="dialog" aria-modal="true" aria-label="نمایش همه تصاویر">',
                        '  <div class="gallery-lightbox-content relative bg-white">',
                        '    <button type="button" class="gallery-lightbox-close absolute pointer color-white yekan-34" aria-label="بستن">×</button>',
                        '    <div class="gallery-lightbox-body flex">',
                        '      <div class="product-gallery-main">',
                        '        <div class="swiper-container h-100 gallery-lightbox-main overflow-hidden">',
                        '          <div class="swiper-wrapper h-100"></div>',
                        '        </div>',
                        '      </div>',
                        '      <div class="product-gallery-thumbs">',
                        '        <div class="swiper-container h-100 gallery-lightbox-thumbs overflow-hidden">',
                        '          <div class="swiper-wrapper h-100"></div>',
                        '        </div>',
                        '      </div>',
                        '    </div>',
                        '  </div>',
                        '</div>'
                    ].join('');
                    $('body').append(modalHtml);
                }

                var $modal = $('#gallery-lightbox-modal');
                var $mainWrapper = $modal.find('.gallery-lightbox-main .swiper-wrapper');
                var $thumbWrapper = $modal.find('.gallery-lightbox-thumbs .swiper-wrapper');

                // Populate slides
                $mainWrapper.empty();
                $thumbWrapper.empty();
                images.forEach(function (img) {
                    var full = img.full || img.src || '';
                    var thumb = img.thumb || img.thumbnail || full;
                    if (!full) return;
                    $mainWrapper.append('<div class="swiper-slide"><img src="' + full + '" alt="" /></div>');
                    $thumbWrapper.append('<div class="swiper-slide"><div class="thumb-item"><img src="' + thumb + '" alt="" /></div></div>');
                });

                // Initialize or reinitialize Swipers inside modal
                var modalThumbs = $modal.data('thumbsSwiper');
                var modalMain = $modal.data('mainSwiper');

                if (modalThumbs) {
                    modalThumbs.update();
                } else {
                    modalThumbs = new Swiper('#gallery-lightbox-modal .gallery-lightbox-thumbs', {
                        slidesPerView: 6,
                        spaceBetween: 10,
                        direction: 'vertical',
                        watchSlidesVisibility: true,
                        watchSlidesProgress: true,
                    });
                    $modal.data('thumbsSwiper', modalThumbs);
                }

                if (modalMain) {
                    modalMain.update();
                } else {
                    modalMain = new Swiper('#gallery-lightbox-modal .gallery-lightbox-main', {
                        slidesPerView: 1,
                        spaceBetween: 10,
                        thumbs: { swiper: modalThumbs },
                        zoom: { maxRatio: 2, toggle: true }
                    });
                    $modal.data('mainSwiper', modalMain);
                }

                // Open modal
                $modal.addClass('open');
                $('body').addClass('gallery-lightbox-open');

                // Close handlers
                $modal.off('click.close').on('click.close', function (evt) {
                    if ($(evt.target).is('#gallery-lightbox-modal') || $(evt.target).is('.gallery-lightbox-close')) {
                        closeLightbox();
                    }
                });

                function closeLightbox() {
                    $modal.removeClass('open');
                    $('body').removeClass('gallery-lightbox-open');
                }
            }
        }
    });
})(jQuery);
