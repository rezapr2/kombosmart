<?php

/**
 * Floating WhatsApp chat button, shown on every page (included from footer.php).
 *
 * The link comes from Theme Options → شبکه های اجتماعی → لینک واتس اپ (see Helper::whatsapp_link()).
 * Rendered only when a link is set.
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

$whatsapp_link = \TanilChoob\Theme\Helper::whatsapp_link();

if (!$whatsapp_link) {
    return;
}
?>
<a class="whatsapp-float flex items-center" href="<?php echo esc_url($whatsapp_link); ?>" target="_blank" rel="noopener noreferrer" aria-label="گفتگو در واتس‌اپ">
    <span class="whatsapp-float__icon flex items-center flex-shrink-0" aria-hidden="true">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.21 3.07.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.05 21.5h-.01a9.43 9.43 0 0 1-4.8-1.32l-.35-.2-3.57.93.96-3.48-.23-.36a9.42 9.42 0 0 1-1.45-5.03C2.6 6.83 6.84 2.6 12.06 2.6c2.52 0 4.9.99 6.68 2.77a9.37 9.37 0 0 1 2.76 6.68c0 5.21-4.24 9.45-9.45 9.45zm8.04-17.49A11.3 11.3 0 0 0 12.05.7C5.79.7.7 5.79.7 12.04c0 2 .52 3.95 1.52 5.67L.6 23.3l5.72-1.5a11.33 11.33 0 0 0 5.72 1.46h.01c6.26 0 11.35-5.09 11.35-11.34 0-3.03-1.18-5.88-3.32-8.02z"/></svg>
    </span>
    <span class="whatsapp-float__label yekan-14">گفتگو در واتس‌اپ</span>
</a>
