/* global jQuery */
(function($){
    $(function(){
        var $drawer    = $('#mobile-category-drawer');
        var $openBtn   = $('#mobile-categories-trigger');
        var $closeBtn  = $('#close-mobile-drawer');
        var $menuItems = $('.mobile-drawer-menu > li');

        if (!$drawer.length || !$openBtn.length) return;

        function openDrawer(e) {
            if (e) e.preventDefault();
            $drawer.removeClass('hidden').css('visibility', 'visible');
            requestAnimationFrame(function(){
                $drawer.addClass('open');
                $('body').css('overflow', 'hidden');
            });
        }

        function closeDrawer(e) {
            if (e) e.preventDefault();
            $drawer.removeClass('open');
            setTimeout(function(){
                $drawer.addClass('hidden').css('visibility', '');
                $('body').css('overflow', '');
            }, 300);
        }

        $openBtn.on('click', openDrawer);
        if ($closeBtn.length) {
            $closeBtn.on('click', closeDrawer);
        }

        if ($menuItems.length) {
            activateItem($menuItems.eq(0));
            $menuItems.each(function(){
                var $item = $(this);
                var $link = $item.find('> a');
                if ($link.length) {
                    $link.on('click', function(e){
                        e.preventDefault();
                        activateItem($item);
                    });
                }
            });
        }

        function activateItem($targetItem) {
            $menuItems.removeClass('active');
            $targetItem.addClass('active');
            $drawer.find('.drawer-content .mobile-drawer-menu > li.active > ul.sub-menu .sub-menu').hide();
            $drawer.find('.drawer-content .mobile-drawer-menu > li.active > ul.sub-menu > li').removeClass('open');
        }

        $(document).on('click', '.mobile-drawer .drawer-content .mobile-drawer-menu > li.active > ul.sub-menu > li > a', function(e){
            var $li = $(this).parent();
            var $submenu = $li.children('.sub-menu');
            if ($submenu.length) {
                e.preventDefault();
                var $siblings = $li.siblings('.open');
                $siblings.removeClass('open').children('.sub-menu').slideUp(300);
                if ($li.hasClass('open')) {
                    $li.removeClass('open');
                    $submenu.slideUp(300);
                } else {
                    $li.addClass('open');
                    $submenu.slideDown(300);
                }
            }
        });
    });
})(jQuery);
