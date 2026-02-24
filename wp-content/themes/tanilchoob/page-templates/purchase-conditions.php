<?php

/**
 * Template Name: Purchase Conditions
 * Description: A custom Purchase Conditions page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
$why_tanil = get_field('why_tanil', $page_id);
$compare_purchase_conditions = get_field('compare_purchase-conditions', $page_id);
$form_id = get_field('form_id', $page_id);
$contact_box_title = get_field('contact_box_title', $page_id);
$contact_box_subtitle = get_field('contact_box_subtitle', $page_id);
$contact_box_button = get_field('contact_box_button', $page_id);
?>
<div id="page-purchase-conditions" class="container page-template-purchase-conditions mt-60">
    <section class="hero flex flex-col items-center gap-20">
        <h1 class="title color-white bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-80 yekan-20 md:yekan-34 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?></div>
        <div class="line hidden md:flex">
            <svg width="256" height="9" viewBox="0 0 256 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M254.354 5.29782C203.929 0.62909 152.651 3.21143 102.1 2.37081C68.6723 1.80839 35.2424 0.453704 1.83921 0.00013565C0.837726 -0.0119595 0.0140726 0.786298 0.000163192 1.7902C-0.0131415 2.7941 0.788758 3.61659 1.78963 3.62868C35.1892 4.08225 68.6154 5.43694 102.039 5.99936C152.5 6.83998 203.681 4.25158 254.015 8.91427C255.013 9.00498 255.896 8.27325 255.992 7.27539C256.083 6.27754 255.345 5.39458 254.354 5.29782Z" fill="#F0DBF8" />
            </svg>
        </div>
    </section>
    <section class="why_tanil flex flex-col items-center gap-30">
        <div class="flex flex-col items-center gap-20">
            <div class="title flex items-center gap-10">
                <svg width="30" height="28" viewBox="0 0 30 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.3256 10.2929C10.5408 8.00374 8.28434 5.27766 6.58869 2.10157C6.24956 1.46896 5.46046 1.22765 4.82133 1.56678C4.18872 1.90591 3.94737 2.69506 4.2865 3.33419C6.15172 6.81027 8.61698 9.79724 11.6692 12.3081C12.2235 12.7646 13.0517 12.6864 13.5082 12.132C13.9648 11.5711 13.88 10.7494 13.3256 10.2929Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8386 1.37817C11.982 4.01295 12.1125 6.64773 12.2756 9.28251C12.3212 9.9999 12.9408 10.5477 13.6582 10.5021C14.3755 10.4564 14.9234 9.83686 14.8777 9.11947C14.7147 6.49121 14.5842 3.86296 14.4407 1.22818C14.4016 0.512746 13.782 -0.0383444 13.0647 0.00209038C12.3473 0.0425252 11.7929 0.65882 11.8386 1.37817Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M21.5246 1.91281C19.8551 4.4302 17.9638 6.75191 16.0595 9.09974C15.603 9.66061 15.6877 10.4824 16.2486 10.9324C16.8094 11.3889 17.6312 11.3041 18.0812 10.7432C20.0377 8.33671 21.9812 5.94322 23.703 3.35409C24.1008 2.75409 23.9312 1.94542 23.3312 1.54759C22.7377 1.14977 21.9225 1.31281 21.5246 1.91281Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M27.9901 11.2717C23.464 11.4738 18.6574 11.4804 14.1314 11.1934C13.414 11.1543 12.7944 11.6956 12.7488 12.4195C12.7031 13.1369 13.2509 13.7564 13.9683 13.8021C18.5857 14.0891 23.4901 14.0825 28.1075 13.8804C28.8249 13.8477 29.3857 13.2347 29.3531 12.5173C29.3205 11.7999 28.7074 11.239 27.9901 11.2717Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.6637 12.0997C14.455 15.517 17.3441 18.8692 19.9333 22.4432C20.3572 23.0236 21.1724 23.154 21.7594 22.7301C22.3398 22.3062 22.4702 21.4909 22.0463 20.9105C19.4246 17.2974 16.5028 13.9062 13.6789 10.4496C13.2224 9.89529 12.4006 9.81052 11.8463 10.267C11.2854 10.7236 11.2071 11.5453 11.6637 12.0997Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5129 13.0911C13.2433 16.652 13.4455 20.0564 13.4977 23.6629C13.5042 24.3803 14.0977 24.9542 14.8151 24.9477C15.539 24.9346 16.1129 24.3476 16.0999 23.6237C16.0477 19.8476 15.8325 16.2933 15.0695 12.5629C14.9195 11.8585 14.2347 11.402 13.5238 11.552C12.8195 11.6955 12.3629 12.3868 12.5129 13.0911Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6287 12.8044C10.5157 17.0305 9.42653 21.7261 7.16349 25.8609C6.81784 26.487 7.05257 27.2826 7.68518 27.6283C8.31779 27.9739 9.10698 27.7391 9.45263 27.113C11.7287 22.9522 12.8309 18.2304 14.9635 13.9718C15.283 13.3261 15.0222 12.5435 14.383 12.2174C13.7374 11.8978 12.9548 12.1587 12.6287 12.8044Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.7897 11.8457C9.58314 12.5892 5.38314 12.9283 1.15053 13.4305C0.433142 13.5153 -0.0755534 14.161 0.00922919 14.8784C0.0940118 15.5958 0.739724 16.1045 1.45712 16.0197C5.73538 15.5175 9.98754 15.1653 14.2462 14.4153C14.9506 14.2849 15.4267 13.6066 15.3028 12.9023C15.1723 12.1914 14.5005 11.7153 13.7897 11.8457Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7188 9.86356C15.8514 9.82443 15.0296 9.76572 14.1818 9.6418C13.4644 9.53746 12.8057 10.0331 12.7014 10.744C12.597 11.4549 13.0926 12.1201 13.8035 12.2244C14.7426 12.3614 15.6492 12.4266 16.6014 12.4657C17.3188 12.4983 17.9318 11.944 17.9579 11.2201C17.9905 10.5027 17.4362 9.89617 16.7188 9.86356Z" fill="#6A7BCC" />
                </svg>

                <div class="yekan-20 md:yekan-30 color-black">
                    چرا تانيل چوب را انتخاب كنيد؟
                </div>
            </div>
            <?php
            if ($why_tanil['description']) {
                echo '<p class="description yekan-14 md:yekan-20 color-black-80 text-center">' . $why_tanil['description'] . '</p>';
            }

            if ($why_tanil['cards']) {
                echo '<div class="cards flex flex-col-reverse md:flex-row justify-center gap-20 mt-30">';
                foreach ($why_tanil['cards'] as $card) {
                    echo '<div class="card flex flex-col items-center gap-10 bg-black-03">';
                    echo isset($card['icon']) ? '<img src="' . $card['icon']['url'] . '" alt="' . $card['title'] . '" />' : '';
                    echo isset($card['title']) ? '<div class="title yekan-18 md:yekan-24 bold">' . $card['title'] . '</div>' : '';
                    echo isset($card['description']) ? '<div class="description yekan-14 md:yekan-20 color-black-70 text-center">' . $card['description'] . '</div>' : '';
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
        <?php if (isset($why_tanil['banner_image'])): ?>
            <div class="banner">
                <?php if (isset($why_tanil['banner_link']['url'])): ?>
                    <a href="<?php echo $why_tanil['banner_link']['url']; ?>" target="_blank">
                    <?php endif; 
                    $banner_image_mobile = isset($why_tanil['banner_image_mobile']['url']);
                    ?>
                    <img <?php if ($banner_image_mobile){echo 'class="hidden md:flex"';} ?> src="<?php echo $why_tanil['banner_image']['url']; ?>" alt="<?php echo $why_tanil['banner_image']['alt'] ?: $why_tanil['title']; ?>" />
                    <?php if ($banner_image_mobile): ?>
                    <img class="md:hidden" src="<?php echo $why_tanil['banner_image_mobile']['url']; ?>" alt="<?php echo $why_tanil['banner_image_mobile']['alt'] ?: $why_tanil['title']; ?>" />
                    <?php endif; ?>
                    <?php if (isset($why_tanil['banner_link']['url'])): ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    <section class="compare_purchase-conditions flex flex-col items-center gap-20 mt-30">
        <div class="flex flex-col items-center md:gap-20">
            <div class="title flex items-center gap-10">
                <svg width="30" height="28" viewBox="0 0 30 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.3256 10.2929C10.5408 8.00374 8.28434 5.27766 6.58869 2.10157C6.24956 1.46896 5.46046 1.22765 4.82133 1.56678C4.18872 1.90591 3.94737 2.69506 4.2865 3.33419C6.15172 6.81027 8.61698 9.79724 11.6692 12.3081C12.2235 12.7646 13.0517 12.6864 13.5082 12.132C13.9648 11.5711 13.88 10.7494 13.3256 10.2929Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8386 1.37817C11.982 4.01295 12.1125 6.64773 12.2756 9.28251C12.3212 9.9999 12.9408 10.5477 13.6582 10.5021C14.3755 10.4564 14.9234 9.83686 14.8777 9.11947C14.7147 6.49121 14.5842 3.86296 14.4407 1.22818C14.4016 0.512746 13.782 -0.0383444 13.0647 0.00209038C12.3473 0.0425252 11.7929 0.65882 11.8386 1.37817Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M21.5246 1.91281C19.8551 4.4302 17.9638 6.75191 16.0595 9.09974C15.603 9.66061 15.6877 10.4824 16.2486 10.9324C16.8094 11.3889 17.6312 11.3041 18.0812 10.7432C20.0377 8.33671 21.9812 5.94322 23.703 3.35409C24.1008 2.75409 23.9312 1.94542 23.3312 1.54759C22.7377 1.14977 21.9225 1.31281 21.5246 1.91281Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M27.9901 11.2717C23.464 11.4738 18.6574 11.4804 14.1314 11.1934C13.414 11.1543 12.7944 11.6956 12.7488 12.4195C12.7031 13.1369 13.2509 13.7564 13.9683 13.8021C18.5857 14.0891 23.4901 14.0825 28.1075 13.8804C28.8249 13.8477 29.3857 13.2347 29.3531 12.5173C29.3205 11.7999 28.7074 11.239 27.9901 11.2717Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.6637 12.0997C14.455 15.517 17.3441 18.8692 19.9333 22.4432C20.3572 23.0236 21.1724 23.154 21.7594 22.7301C22.3398 22.3062 22.4702 21.4909 22.0463 20.9105C19.4246 17.2974 16.5028 13.9062 13.6789 10.4496C13.2224 9.89529 12.4006 9.81052 11.8463 10.267C11.2854 10.7236 11.2071 11.5453 11.6637 12.0997Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5129 13.0911C13.2433 16.652 13.4455 20.0564 13.4977 23.6629C13.5042 24.3803 14.0977 24.9542 14.8151 24.9477C15.539 24.9346 16.1129 24.3476 16.0999 23.6237C16.0477 19.8476 15.8325 16.2933 15.0695 12.5629C14.9195 11.8585 14.2347 11.402 13.5238 11.552C12.8195 11.6955 12.3629 12.3868 12.5129 13.0911Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6287 12.8044C10.5157 17.0305 9.42653 21.7261 7.16349 25.8609C6.81784 26.487 7.05257 27.2826 7.68518 27.6283C8.31779 27.9739 9.10698 27.7391 9.45263 27.113C11.7287 22.9522 12.8309 18.2304 14.9635 13.9718C15.283 13.3261 15.0222 12.5435 14.383 12.2174C13.7374 11.8978 12.9548 12.1587 12.6287 12.8044Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M13.7897 11.8457C9.58314 12.5892 5.38314 12.9283 1.15053 13.4305C0.433142 13.5153 -0.0755534 14.161 0.00922919 14.8784C0.0940118 15.5958 0.739724 16.1045 1.45712 16.0197C5.73538 15.5175 9.98754 15.1653 14.2462 14.4153C14.9506 14.2849 15.4267 13.6066 15.3028 12.9023C15.1723 12.1914 14.5005 11.7153 13.7897 11.8457Z" fill="#6A7BCC" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7188 9.86356C15.8514 9.82443 15.0296 9.76572 14.1818 9.6418C13.4644 9.53746 12.8057 10.0331 12.7014 10.744C12.597 11.4549 13.0926 12.1201 13.8035 12.2244C14.7426 12.3614 15.6492 12.4266 16.6014 12.4657C17.3188 12.4983 17.9318 11.944 17.9579 11.2201C17.9905 10.5027 17.4362 9.89617 16.7188 9.86356Z" fill="#6A7BCC" />
                </svg>

                <div class="yekan-20 md:yekan-30 color-black">
                    مقايسه سريع شرايط خريد
                </div>
            </div>
            <?php if ($compare_purchase_conditions['purchase-conditions']): ?>
                <div class="conditions-table-wrapper mt-20 w-100">
                    <table class="conditions-table">
                        <thead>
                            <tr>
                                <th>نوع خرید</th>
                                <th>بیعانه</th>
                                <th>زمان پرداخت باقی مانده</th>
                                <th>تحویل کالا</th>
                                <th>مدارک موردنیاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compare_purchase_conditions['purchase-conditions'] as $row): ?>
                                <tr>
                                    <td><?php echo isset($row['type']) ? $row['type'] : '-'; ?></td>
                                    <td><?php echo isset($row['pre_pay']) ? $row['pre_pay'] : '-'; ?></td>
                                    <td><?php echo isset($row['payment_remain_time']) ? $row['payment_remain_time'] : '-'; ?></td>
                                    <td><?php echo isset($row['delivery_time']) ? $row['delivery_time'] : '-'; ?></td>
                                    <td><?php echo isset($row['required_documents']) ? $row['required_documents'] : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="line mt-30">
                    <svg width="256" height="9" viewBox="0 0 256 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M254.354 5.29782C203.929 0.62909 152.651 3.21143 102.1 2.37081C68.6723 1.80839 35.2424 0.453704 1.83921 0.00013565C0.837726 -0.0119595 0.0140726 0.786298 0.000163192 1.7902C-0.0131415 2.7941 0.788758 3.61659 1.78963 3.62868C35.1892 4.08225 68.6154 5.43694 102.039 5.99936C152.5 6.83998 203.681 4.25158 254.015 8.91427C255.013 9.00498 255.896 8.27325 255.992 7.27539C256.083 6.27754 255.345 5.39458 254.354 5.29782Z" fill="#F0DBF8" />
                    </svg>
                </div>

            <?php endif; ?>
        </div>
    </section>
    <?php if ($form_id): ?>
        <section class="form flex flex-col items-center gap-20 mt-30">
            <div class="flex flex-col items-center gap-20 w-100">
                <div class="title flex items-center gap-10">
                    <svg width="30" height="28" viewBox="0 0 30 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.3256 10.2929C10.5408 8.00374 8.28434 5.27766 6.58869 2.10157C6.24956 1.46896 5.46046 1.22765 4.82133 1.56678C4.18872 1.90591 3.94737 2.69506 4.2865 3.33419C6.15172 6.81027 8.61698 9.79724 11.6692 12.3081C12.2235 12.7646 13.0517 12.6864 13.5082 12.132C13.9648 11.5711 13.88 10.7494 13.3256 10.2929Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8386 1.37817C11.982 4.01295 12.1125 6.64773 12.2756 9.28251C12.3212 9.9999 12.9408 10.5477 13.6582 10.5021C14.3755 10.4564 14.9234 9.83686 14.8777 9.11947C14.7147 6.49121 14.5842 3.86296 14.4407 1.22818C14.4016 0.512746 13.782 -0.0383444 13.0647 0.00209038C12.3473 0.0425252 11.7929 0.65882 11.8386 1.37817Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M21.5246 1.91281C19.8551 4.4302 17.9638 6.75191 16.0595 9.09974C15.603 9.66061 15.6877 10.4824 16.2486 10.9324C16.8094 11.3889 17.6312 11.3041 18.0812 10.7432C20.0377 8.33671 21.9812 5.94322 23.703 3.35409C24.1008 2.75409 23.9312 1.94542 23.3312 1.54759C22.7377 1.14977 21.9225 1.31281 21.5246 1.91281Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M27.9901 11.2717C23.464 11.4738 18.6574 11.4804 14.1314 11.1934C13.414 11.1543 12.7944 11.6956 12.7488 12.4195C12.7031 13.1369 13.2509 13.7564 13.9683 13.8021C18.5857 14.0891 23.4901 14.0825 28.1075 13.8804C28.8249 13.8477 29.3857 13.2347 29.3531 12.5173C29.3205 11.7999 28.7074 11.239 27.9901 11.2717Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.6637 12.0997C14.455 15.517 17.3441 18.8692 19.9333 22.4432C20.3572 23.0236 21.1724 23.154 21.7594 22.7301C22.3398 22.3062 22.4702 21.4909 22.0463 20.9105C19.4246 17.2974 16.5028 13.9062 13.6789 10.4496C13.2224 9.89529 12.4006 9.81052 11.8463 10.267C11.2854 10.7236 11.2071 11.5453 11.6637 12.0997Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5129 13.0911C13.2433 16.652 13.4455 20.0564 13.4977 23.6629C13.5042 24.3803 14.0977 24.9542 14.8151 24.9477C15.539 24.9346 16.1129 24.3476 16.0999 23.6237C16.0477 19.8476 15.8325 16.2933 15.0695 12.5629C14.9195 11.8585 14.2347 11.402 13.5238 11.552C12.8195 11.6955 12.3629 12.3868 12.5129 13.0911Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6287 12.8044C10.5157 17.0305 9.42653 21.7261 7.16349 25.8609C6.81784 26.487 7.05257 27.2826 7.68518 27.6283C8.31779 27.9739 9.10698 27.7391 9.45263 27.113C11.7287 22.9522 12.8309 18.2304 14.9635 13.9718C15.283 13.3261 15.0222 12.5435 14.383 12.2174C13.7374 11.8978 12.9548 12.1587 12.6287 12.8044Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.7897 11.8457C9.58314 12.5892 5.38314 12.9283 1.15053 13.4305C0.433142 13.5153 -0.0755534 14.161 0.00922919 14.8784C0.0940118 15.5958 0.739724 16.1045 1.45712 16.0197C5.73538 15.5175 9.98754 15.1653 14.2462 14.4153C14.9506 14.2849 15.4267 13.6066 15.3028 12.9023C15.1723 12.1914 14.5005 11.7153 13.7897 11.8457Z" fill="#6A7BCC" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7188 9.86356C15.8514 9.82443 15.0296 9.76572 14.1818 9.6418C13.4644 9.53746 12.8057 10.0331 12.7014 10.744C12.597 11.4549 13.0926 12.1201 13.8035 12.2244C14.7426 12.3614 15.6492 12.4266 16.6014 12.4657C17.3188 12.4983 17.9318 11.944 17.9579 11.2201C17.9905 10.5027 17.4362 9.89617 16.7188 9.86356Z" fill="#6A7BCC" />
                    </svg>

                    <div class="yekan-20 md:yekan-30 color-black">
                        پرسش سریع درباره شرایط پرداخت
                    </div>
                </div>
                <div class="form-area bg-black-03 mt-30 w-100">
                    <div class="flex flex-col md:flex-row items-center w-100 justify-between">
                        <div class="contact-form">
                            <?php
                            if ($form_id && shortcode_exists('contact-form-7')) {
                                echo do_shortcode('[contact-form-7 id="' . $form_id . '" title="فرم تماس با ما"]');
                            }
                            ?>
                        </div>
                        <div class="text-area flex flex-col items-center">
                            <?php if(isset($contact_box_title) && $contact_box_title): ?>
                            <div class="yekan-20 md:yekan-30 color-primary bold">
                                <?php echo $contact_box_title; ?>
                            </div>
                            <?php endif;
                            if (isset($contact_box_subtitle) && $contact_box_subtitle): ?>
                            <div class="yekan-14 md:yekan-18 text-center md:text-right color-black-80">
                                <?php echo $contact_box_subtitle; ?>
                            </div>
                            <?php endif;
                            if(isset($contact_box_button) && $contact_box_button): ?>
                                <a href="<?php echo $contact_box_button['url'] ?: '#'; ?>" class="button yekan-14 md:yekan-18 color-black mt-25">
                                    <?php echo $contact_box_button['title'] ?: 'تماس با مشاور فروش'; ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */