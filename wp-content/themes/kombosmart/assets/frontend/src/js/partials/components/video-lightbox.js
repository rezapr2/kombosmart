/**
 * Video Lightbox Functionality - jQuery Plugin
 * 
 * Usage:
 * 1. Add class 'video-lightbox' to any element
 * 2. Add data-video-url attribute with the video URL
 * 3. Initialize with $('.video-lightbox').videoLightbox();
 */
(function($) {
    'use strict';
    
    // Create the lightbox modal if it doesn't exist
    function createLightboxModal() {
        if ($('#video-lightbox-modal').length === 0) {
            const modalHTML = `
                <div id="video-lightbox-modal" class="video-lightbox-modal">
                    <div class="video-lightbox-content relative">
                        <span class="video-lightbox-close">&times;</span>
                        <div class="video-container"></div>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);
        }
    }
    
    // Get video embed code based on URL
    function getVideoEmbed(videoUrl) {
        let videoEmbed = '';
        
        // Handle YouTube videos
        if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            // Extract YouTube ID
            let youtubeId = '';
            if (videoUrl.includes('youtube.com/watch?v=')) {
                youtubeId = videoUrl.split('v=')[1].split('&')[0];
            } else if (videoUrl.includes('youtu.be/')) {
                youtubeId = videoUrl.split('youtu.be/')[1];
            }
            
            if (youtubeId) {
                videoEmbed = `<iframe width="100%" height="100%" src="https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            }
        } 
        // Handle Vimeo videos
        else if (videoUrl.includes('vimeo.com')) {
            // Extract Vimeo ID
            const vimeoId = videoUrl.split('vimeo.com/')[1];
            if (vimeoId) {
                videoEmbed = `<iframe width="100%" height="100%" src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            }
        }
        // Handle Aparat videos
        else if (videoUrl.includes('aparat.com')) {
            if (videoUrl.includes('/embed/')) {
                videoEmbed = `<iframe width="100%" height="100%" src="${videoUrl}" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            } else {
                // Watch links look like https://www.aparat.com/v/{hash}
                const aparatMatch = videoUrl.match(/aparat\.com\/v\/([A-Za-z0-9]+)/);
                if (aparatMatch) {
                    videoEmbed = `<iframe width="100%" height="100%" src="https://www.aparat.com/video/video/embed/videohash/${aparatMatch[1]}/vt/frame?autoplay=true" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                }
            }
        }
        // Handle direct video files
        else if (videoUrl.match(/\.(mp4|webm|ogg)$/i)) {
            videoEmbed = `<video width="100%" height="100%" controls autoplay><source src="${videoUrl}" type="video/${videoUrl.split('.').pop()}">Your browser does not support the video tag.</video>`;
        }
        
        return videoEmbed;
    }
    
    // Close the lightbox modal
    function closeLightbox() {
        const $modal = $('#video-lightbox-modal');
        $modal.hide();
        $modal.find('.video-container').empty();
        $('body').removeClass('video-lightbox-open');
    }
    
    // Plugin definition
    $.fn.videoLightbox = function(options) {
        // Default options
        const settings = $.extend({
            // You can add custom options here
        }, options);
        
        // Create the lightbox modal
        createLightboxModal();
        
        // Get modal elements
        const $modal = $('#video-lightbox-modal');
        const $videoContainer = $modal.find('.video-container');
        const $closeBtn = $modal.find('.video-lightbox-close');
        
        // Set up close button event
        $closeBtn.off('click').on('click', closeLightbox);
        
        // Close when clicking outside content
        $modal.off('click').on('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
        
        // Close when pressing ESC key
        $(document).off('keydown.videoLightbox').on('keydown.videoLightbox', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeLightbox();
            }
        });
        
        // Process each element
        return this.each(function() {
            const $this = $(this);
            
            // Add click event
            $this.off('click.videoLightbox').on('click.videoLightbox', function(e) {
                e.preventDefault();
                
                // Get video URL
                const videoUrl = $this.data('video-url');
                if (!videoUrl) return;
                
                // Get video embed code
                const videoEmbed = getVideoEmbed(videoUrl);
                if (!videoEmbed) return;
                
                // Insert video embed
                $videoContainer.html(videoEmbed);
                
                // Show modal
                $modal.css('display', 'flex');
                
                // Prevent body scrolling
                $('body').addClass('video-lightbox-open');
            });
        });
    };
    
    // Auto-initialize on document ready
    $(document).ready(function() {
        $('.video-lightbox').videoLightbox();
    });
    
})(jQuery);