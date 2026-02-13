/* global jQuery */
(function($){
    $(function(){
        var $catDrawer    = $('#mobile-category-drawer');
        var $catOpenBtn   = $('#mobile-categories-trigger');
        var $catCloseBtn  = $('#close-mobile-drawer');
        var $menuItems    = $('.mobile-drawer-menu > li');
        var $mainDrawer   = $('#mobile-main-menu-drawer');
        var $mainOpenBtn  = $('#mobile-main-menu-trigger');
        var $mainCloseBtn = $('#close-mobile-main-menu');

        function openDrawer($drawer){
            $drawer.removeClass('hidden').css('visibility', 'visible');
            requestAnimationFrame(function(){
                $drawer.addClass('open');
                $('body').css('overflow', 'hidden');
            });
        }

        function closeDrawer($drawer){
            $drawer.removeClass('open');
            setTimeout(function(){
                $drawer.addClass('hidden').css('visibility', '');
                $('body').css('overflow', '');
            }, 300);
        }

        if ($catDrawer.length && $catOpenBtn.length) {
            $catOpenBtn.on('click', function(e){ e.preventDefault(); openDrawer($catDrawer); });
            if ($catCloseBtn.length) { $catCloseBtn.on('click', function(e){ e.preventDefault(); closeDrawer($catDrawer); }); }
        }
        if ($mainDrawer.length && $mainOpenBtn.length) {
            $mainOpenBtn.on('click', function(e){ e.preventDefault(); openDrawer($mainDrawer); });
            if ($mainCloseBtn.length) { $mainCloseBtn.on('click', function(e){ e.preventDefault(); closeDrawer($mainDrawer); }); }
        }

        if ($menuItems.length && $catDrawer.length) {
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
            $catDrawer.find('.drawer-content .mobile-drawer-menu > li.active > ul.sub-menu .sub-menu').hide();
            $catDrawer.find('.drawer-content .mobile-drawer-menu > li.active > ul.sub-menu > li').removeClass('open');
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
