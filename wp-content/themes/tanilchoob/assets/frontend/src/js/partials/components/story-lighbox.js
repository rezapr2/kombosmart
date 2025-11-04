/**
 * Story Lightbox - shows image or video content in a centered modal
 * Reads data from the element's `data-story-content` attribute (JSON).
 */
(function ($) {
    'use strict';

    function createStoryLightboxModal() {
        if ($('#story-lightbox-modal').length === 0) {
            const modalHTML = `
                <div id="story-lightbox-modal" class="story-lightbox-modal" aria-hidden="true" role="dialog">
                    <div class="story-lightbox-content" role="document">
                        <div class="story-header">
                            <img class="story-avatar" src="" alt="" />
                            <div class="story-username yekan-24 color-white"></div>
                            <div class="story-close pointer" aria-label="Close"><svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M29.6995 29.6982L49.4985 49.4972" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M29.6974 49.4972L49.4964 29.6982" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</div>
                        </div>
                        <div class="story-media"></div>
                        <div class="story-caption yekan-28 color-white"></div>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);
        }
    }

    function getVideoEmbed(videoUrl) {
        let videoEmbed = '';
        if (!videoUrl) return '';

        if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            let youtubeId = '';
            if (videoUrl.includes('youtube.com/watch?v=')) {
                youtubeId = videoUrl.split('v=')[1].split('&')[0];
            } else if (videoUrl.includes('youtu.be/')) {
                youtubeId = videoUrl.split('youtu.be/')[1];
            }
            if (youtubeId) {
                videoEmbed = `<iframe width="100%" height="100%" src="https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            }
        } else if (videoUrl.includes('vimeo.com')) {
            const vimeoId = videoUrl.split('vimeo.com/')[1];
            if (vimeoId) {
                videoEmbed = `<iframe width="100%" height="100%" src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            }
        } else if (videoUrl.match(/\.(mp4|webm|ogg)$/i)) {
            videoEmbed = `<video width="100%" height="100%" controls autoplay><source src="${videoUrl}" type="video/${videoUrl.split('.').pop()}">Your browser does not support the video tag.</video>`;
        }
        return videoEmbed;
    }

    function getImageUrl(image) {
        if (!image) return '';
        if (typeof image === 'string') return image;
        if (image.url) return image.url;
        const anyUrl = Object.values(image).find(
            (v) => typeof v === 'string' && /^(https?:)?\/\//.test(v)
        );
        return anyUrl || '';
    }

    function closeLightbox() {
        const $modal = $('#story-lightbox-modal');
        $modal.hide();
        $modal.find('.story-media').empty();
        $modal.attr('aria-hidden', 'true');
        $('body').removeClass('story-lightbox-open');
    }

    $.fn.storyLightbox = function (options) {
        const settings = $.extend({}, options);

        createStoryLightboxModal();

        const $modal = $('#story-lightbox-modal');
        const $media = $modal.find('.story-media');
        const $closeBtn = $modal.find('.story-close');
        const $username = $modal.find('.story-username');
        const $avatar = $modal.find('.story-avatar');
        const $caption = $modal.find('.story-caption');

        $closeBtn.off('click').on('click', closeLightbox);

        $modal.off('click').on('click', function (e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        $(document)
            .off('keydown.storyLightbox')
            .on('keydown.storyLightbox', function (e) {
                if (e.key === 'Escape' && $modal.is(':visible')) {
                    closeLightbox();
                }
            });

        return this.each(function () {
            const $this = $(this);
            $this.css('cursor', 'pointer');

            $this
                .off('click.storyLightbox')
                .on('click.storyLightbox', function (e) {
                    e.preventDefault();

                    let data = null;
                    try {
                        const raw = $this.attr('data-story-content');
                        if (!raw) return;
                        data = typeof raw === 'string' ? JSON.parse(raw) : raw;
                    } catch (err) {
                        console.error('Error parsing story content:', err);
                        return;
                    }

                    const title = data.title || '';
                    const subtitle = data.subtitle || '';
                    const contentType = (data.content_type || '').toLowerCase();
                    const imageUrl = getImageUrl(data.image);
                    const thumbUrl = data.thumbnail_url || imageUrl;
                    const videoUrl = data.video_link || '';

                    let contentHTML = '';
                    if (contentType === 'video' && videoUrl) {
                        contentHTML = getVideoEmbed(videoUrl);
                    } else if (imageUrl) {
                        contentHTML = `<img src="${imageUrl}" alt="${title}" />`;
                    }
                    if (!contentHTML) return;

                    $media.html(contentHTML);
                    $username.text(title);
                    if (thumbUrl) $avatar.attr('src', thumbUrl);
                    $caption.text(subtitle);

                    $modal.css('display', 'flex').attr('aria-hidden', 'false');
                    $('body').addClass('story-lightbox-open');
                });
        });
    };

    $(document).ready(function () {
        $('.story-item').storyLightbox();
    });
})(jQuery);
