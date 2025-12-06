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
                    var isVideo = (img.type === 'video');
                    var full = img.full || img.src || '';
                    var thumb = img.thumb || img.thumbnail || '';
                    if (!full && !isVideo) return;

                    if (isVideo) {
                        var embed = img.embed || '';
                        var videoUrl = img.video_url || '';
                        var mainHtml = '<div class="swiper-slide video-slide"><div class="video-wrapper">';
                        if (embed) {
                            mainHtml += embed;
                        } else if (videoUrl) {
                            mainHtml += '<video controls playsinline src="' + videoUrl + '"></video>';
                        }
                        mainHtml += '</div></div>';
                        $mainWrapper.append(mainHtml);

                        var thumbHtml = '<div class="swiper-slide"><div class="thumb-item video-thumb">';
                        if (thumb) {
                            thumbHtml += '<img src="' + thumb + '" alt="" />';
                        }
                        thumbHtml += '<span class="video-icon absolute center z-index-5" aria-hidden="true">'
                            + '<svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">'
                            + '<foreignObject x="-9.96202" y="-9.96202" width="62.924" height="62.924"><div xmlns="http://www.w3.org/1999/xhtml" style="backdrop-filter:blur(4.98px);clip-path:url(#bgblur_0_1_1115_clip_path);height:100%;width:100%"></div></foreignObject><circle data-figma-bg-blur-radius="9.96202" cx="21.5" cy="21.5" r="21.5" fill="white" fill-opacity="0.62"/>'
                            + '<path d="M31.1807 20.0795C32.1525 20.6405 32.1525 22.0431 31.1807 22.6042L17.5155 30.4938C16.5437 31.0549 15.3291 30.3535 15.3291 29.2315L15.3291 13.4522C15.3291 12.3301 16.5437 11.6288 17.5155 12.1898L31.1807 20.0795Z" fill="white"/>'
                            + '<defs><clipPath id="bgblur_0_1_1115_clip_path" transform="translate(9.96202 9.96202)"><circle cx="21.5" cy="21.5" r="21.5"/></clipPath></defs>'
                            + '</svg>'
                            + '</span>';
                        thumbHtml += '</div></div>';
                        $thumbWrapper.append(thumbHtml);
                    } else {
                        $mainWrapper.append('<div class="swiper-slide"><img src="' + full + '" alt="" /></div>');
                        $thumbWrapper.append('<div class="swiper-slide"><div class="thumb-item"><img src="' + (thumb || full) + '" alt="" /></div></div>');
                    }
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
