<?php

/**
 * About Tanil Section Template
 *
 * @package TanilChoob
 */

use TanilChoob\Theme\Helper;

$page_id = get_the_ID();
// Get ACF fields if they exist
$about_tanil = get_field('about_tanil', $page_id);
$title = $about_tanil['title'] ?: 'ما <strong> کیفیت </strong> را عرضه می‌کنیم';
$description = $about_tanil['description'];
$video_url = $about_tanil['video_link'];
$video_poster = $about_tanil['video_poster'];
$cta_text = 'آشنایی بیشتر';
$cta_url = $about_tanil['more_btn_link'] ?: '#';
?>

<section class="about-tanil mb-40">
    <div class="about-tanil__inner flex justify-between items-center">
        <div class="about-tanil__content flex flex-col flex-shrink-0">
            <div class="about-tanil__header flex items-center gap-10">
                <div class="about-tanil__subtitle">
                    <?php
                    $about_tanil = Helper::getAssetUri('images/about_tanil.png');
                    echo '<img src="' . esc_url($about_tanil) . '" alt="About Tanil" />';
                    ?>
                </div>
                <span class="div"></span>
                <p class="about-tanil__title yekan-26"><?php echo ($title); ?></p>
            </div>

            <div class="about-tanil__description yekan-18 color-black-50 text-justify">
                <?php echo wp_kses_post($description); ?>
            </div>
            <div class="flex justify-between items-center">
                <a href="<?php echo esc_url($cta_url); ?>" class="about-tanil__cta-btn transition flex items-center justify-between yekan-16">
                    <?php echo esc_html($cta_text); ?>
                    <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-left-circle.svg')); ?>
                </a>

                <div class="about-tanil__social">
                    <div class="about-tanil__social-links flex items-center justify-center">
                        <a href="#" class="about-tanil__social-link" aria-label="Telegram">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM16.64 8.8C16.49 10.38 15.84 14.22 15.51 15.99C15.37 16.74 15.09 16.99 14.83 17.02C14.25 17.07 13.81 16.64 13.25 16.27C12.37 15.69 11.87 15.33 11.02 14.77C10.03 14.12 10.67 13.76 11.24 13.18C11.39 13.03 13.95 10.7 14 10.49C14.0069 10.4476 14.0035 10.4043 13.99 10.3636C13.9764 10.3229 13.9532 10.2861 13.9225 10.2562C13.8918 10.2263 13.8544 10.2041 13.8134 10.1916C13.7724 10.1791 13.729 10.1768 13.687 10.185C13.625 10.195 13.563 10.215 13.51 10.245C13.45 10.28 12.09 11.15 9.44 12.87C9.01 13.15 8.62 13.29 8.28 13.28C7.9 13.27 7.18 13.05 6.64 12.86C5.98 12.63 5.46 12.51 5.51 12.14C5.53 11.94 5.79 11.75 6.29 11.55C9.1 10.35 10.94 9.53 11.79 9.09C14.24 7.83 14.77 7.63 15.13 7.63C15.21 7.63 15.39 7.65 15.51 7.75C15.6 7.83 15.63 7.94 15.64 8.02C15.63 8.12 15.65 8.48 15.64 8.8H16.64Z" fill="currentColor" />
                            </svg>
                        </a>
                        <a href="#" class="about-tanil__social-link" aria-label="WhatsApp">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.05 4.91C18.1332 3.98392 17.0412 3.24967 15.8376 2.75005C14.6341 2.25043 13.3431 1.99546 12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91ZM12.04 20.15C10.56 20.15 9.11 19.75 7.85 19L7.55 18.82L4.43 19.65L5.28 16.62L5.08 16.31C4.25822 15.0117 3.82209 13.4783 3.82 11.91C3.82 7.37 7.49 3.7 12.03 3.7C14.23 3.7 16.28 4.55 17.85 6.12C18.6291 6.89435 19.2494 7.81347 19.6729 8.82198C20.0964 9.83049 20.3142 10.9079 20.31 12C20.32 16.54 16.65 20.15 12.04 20.15ZM16.56 13.99C16.31 13.87 15.09 13.28 14.87 13.19C14.64 13.1 14.48 13.06 14.31 13.31C14.14 13.56 13.67 14.11 13.53 14.27C13.39 14.44 13.25 14.46 13 14.33C12.75 14.21 11.94 13.94 11 13.1C10.26 12.44 9.77 11.63 9.62 11.38C9.48 11.13 9.6 11 9.73 10.87C9.84 10.76 9.98 10.58 10.1 10.44C10.22 10.3 10.27 10.19 10.35 10.03C10.43 9.86 10.39 9.72 10.33 9.6C10.27 9.48 9.77 8.26 9.56 7.76C9.36 7.28 9.15 7.34 9 7.33C8.86 7.32 8.7 7.32 8.53 7.32C8.36 7.32 8.1 7.38 7.87 7.63C7.65 7.88 7 8.47 7 9.69C7 10.91 7.87 12.09 7.99 12.25C8.11 12.42 9.77 14.99 12.33 16.04C12.97 16.32 13.48 16.49 13.88 16.62C14.54 16.82 15.14 16.79 15.61 16.73C16.14 16.66 17.12 16.14 17.33 15.55C17.54 14.97 17.54 14.47 17.48 14.37C17.42 14.27 17.25 14.21 17 14.09L16.56 13.99Z" fill="currentColor" />
                            </svg>
                        </a>
                        <a href="#" class="about-tanil__social-link" aria-label="Instagram">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C14.717 2 15.056 2.01 16.122 2.06C17.187 2.11 17.912 2.277 18.55 2.525C19.21 2.779 19.766 3.123 20.322 3.678C20.8305 4.1779 21.224 4.78259 21.475 5.45C21.722 6.087 21.89 6.813 21.94 7.878C21.987 8.944 22 9.283 22 12C22 14.717 21.99 15.056 21.94 16.122C21.89 17.187 21.722 17.912 21.475 18.55C21.2247 19.2178 20.8311 19.8226 20.322 20.322C19.822 20.8303 19.2173 21.2238 18.55 21.475C17.913 21.722 17.187 21.89 16.122 21.94C15.056 21.987 14.717 22 12 22C9.283 22 8.944 21.99 7.878 21.94C6.813 21.89 6.088 21.722 5.45 21.475C4.78233 21.2245 4.17753 20.8309 3.678 20.322C3.16941 19.8222 2.77593 19.2175 2.525 18.55C2.277 17.913 2.11 17.187 2.06 16.122C2.013 15.056 2 14.717 2 12C2 9.283 2.01 8.944 2.06 7.878C2.11 6.812 2.277 6.088 2.525 5.45C2.77524 4.78218 3.1688 4.17732 3.678 3.678C4.17767 3.16923 4.78243 2.77573 5.45 2.525C6.088 2.277 6.812 2.11 7.878 2.06C8.944 2.013 9.283 2 12 2ZM12 7C10.6739 7 9.40215 7.52678 8.46447 8.46447C7.52678 9.40215 7 10.6739 7 12C7 13.3261 7.52678 14.5979 8.46447 15.5355C9.40215 16.4732 10.6739 17 12 17C13.3261 17 14.5979 16.4732 15.5355 15.5355C16.4732 14.5979 17 13.3261 17 12C17 10.6739 16.4732 9.40215 15.5355 8.46447C14.5979 7.52678 13.3261 7 12 7ZM18.5 6.75C18.5 6.41848 18.3683 6.10054 18.1339 5.86612C17.8995 5.6317 17.5815 5.5 17.25 5.5C16.9185 5.5 16.6005 5.6317 16.3661 5.86612C16.1317 6.10054 16 6.41848 16 6.75C16 7.08152 16.1317 7.39946 16.3661 7.63388C16.6005 7.8683 16.9185 8 17.25 8C17.5815 8 17.8995 7.8683 18.1339 7.63388C18.3683 7.39946 18.5 7.08152 18.5 6.75ZM12 9C12.7956 9 13.5587 9.31607 14.1213 9.87868C14.6839 10.4413 15 11.2044 15 12C15 12.7956 14.6839 13.5587 14.1213 14.1213C13.5587 14.6839 12.7956 15 12 15C11.2044 15 10.4413 14.6839 9.87868 14.1213C9.31607 13.5587 9 12.7956 9 12C9 11.2044 9.31607 10.4413 9.87868 9.87868C10.4413 9.31607 11.2044 9 12 9Z" fill="currentColor" />
                            </svg>
                        </a>
                        <a href="#" class="about-tanil__social-link" aria-label="YouTube">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.543 6.498C22 8.28 22 12 22 12C22 12 22 15.72 21.543 17.502C21.289 18.487 20.546 19.262 19.605 19.524C17.896 20 12 20 12 20C12 20 6.107 20 4.395 19.524C3.45 19.258 2.708 18.484 2.457 17.502C2 15.72 2 12 2 12C2 12 2 8.28 2.457 6.498C2.711 5.513 3.454 4.738 4.395 4.476C6.107 4 12 4 12 4C12 4 17.896 4 19.605 4.476C20.55 4.742 21.292 5.516 21.543 6.498ZM10 15.5L16 12L10 8.5V15.5Z" fill="currentColor" />
                            </svg>
                        </a>
                    </div>
                    <p class="about-tanil__social-text yekan-13">ما را در شبکه‌های اجتماعی دنبال کنید</p>

                </div>
            </div>

            
        </div>
        <div class="about-tanil__video">
            <a class="about-tanil__video-wrapper relative">
                <img class="about-tanil__video-poster flex" src="<?php echo esc_url($video_poster['url']); ?>" alt="<?php echo esc_attr($title); ?>">
                <div class="absolute center">
                        <img class="about-tanil__video-play-icon" src="<?php echo Helper::getAssetUri('images/play_icon.svg'); ?>" alt="Play Icon">
                </div>
            </a>
        </div>


    </div>
</section>