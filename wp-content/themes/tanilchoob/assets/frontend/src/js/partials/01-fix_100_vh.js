"use strict";

(function ($) {
    jQuery(document).ready(function ($) {
        let vh = window.innerHeight * 0.01;
        $('html').css('--vh', `${vh}px`);
    })
})(jQuery)