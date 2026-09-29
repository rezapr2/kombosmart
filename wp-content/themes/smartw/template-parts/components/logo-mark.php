<?php
/**
 * Brand mark used when no logo image is set in theme options (header, footer, login).
 */
$mark_id = wp_unique_id('sw-mark-');
?>
<svg class="logo-mark" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="<?php echo esc_attr($mark_id); ?>" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#6C3BFF"/>
            <stop offset=".55" stop-color="#B23BFF"/>
            <stop offset="1" stop-color="#FF3D81"/>
        </linearGradient>
    </defs>
    <rect width="40" height="40" rx="12" fill="url(#<?php echo esc_attr($mark_id); ?>)"/>
    <path d="M22.5 7 12 22.2h7.2L17.5 33 28 17.8h-7.2L22.5 7Z" fill="#fff"/>
</svg>
