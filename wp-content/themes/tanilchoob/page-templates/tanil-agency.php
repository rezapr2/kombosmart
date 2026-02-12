<?php

/**
 * Template Name: Tanil Agency
 * Description: A custom Tanil Agency page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
?>
<div id="page-tanil-agency" class="page-template-tanil-agency">
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?>
        </div>
    </section>
    <?php
        $get_branch_rules = get_field('get_branch_rules', $page_id);
        if( $get_branch_rules ):
    ?>
    <section class="container flex flex-col gap-20 items-center">
        <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40 w-100">
            شرایط اخذ نمایندگی
        </h2>

        <div class="get_branch_rules flex flex-col gap-20 w-100">
            <?php 
                $i = 0;
                foreach($get_branch_rules as $branch_rule): 
                    $i++;
                ?>
                <div class="rule flex flex-col md:flex-row items-center md:items-start gap-10">
                    <div class="index yekan-16 md:yekan-28 color-black-80 bg-black-05 flex flex-shrink-0 item-center"><?php echo $i; ?></div>
                    <div class="flex flex-col">
                        <div class="title yekan-18 md:yekan-24 text-center md:text-right color-black-80"><?php echo $branch_rule['title']; ?></div>
                        <div class="description yekan-12 md:yekan-20 text-center md:text-right color-black-50"><?php echo $branch_rule['description']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        $form_download_btn = get_field('form_download_btn', $page_id);
        if(isset($form_download_btn['url']) && $form_download_btn):
            ?>
            <div class="form_download_btn flex mt-25">
                <a href="<?php echo $form_download_btn['url']; ?>" class="btn bg-primary color-white yekan-14 md:yekan-18 flex items-center gap-07">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.32031 11.6797L11.8803 14.2397L14.4403 11.6797" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.875 4V14.17" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 12.1797C20 16.5997 17 20.1797 12 20.1797C7 20.1797 4 16.5997 4 12.1797" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo $form_download_btn['title']; ?>
                </a>
            </div>
            <?php
        endif;
        ?>
    </section>

    <?php endif; ?>

    <?php
        $get_branch_steps = get_field('get_branch_steps', $page_id);
        if( $get_branch_steps ):
    ?>
    <section class="container flex flex-col gap-20">
        <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40 w-100">
            مسیر اخذ نمایندگی تانیل چوب
        </h2>

        <div class="get_branch_steps flex flex-col md:flex-row items-center md:items-start gap-50 md:gap-20 justify-between relative">
            <?php 
                foreach($get_branch_steps as $branch_step): 
                ?>
                    <div class="step flex flex-col gap-15 items-center relative">
                        <?php if(isset($branch_step['image']['url'])): ?>
                            <div class="image_wrapper w-100 flex item-center">
                                <img src="<?php echo $branch_step['image']['url']; ?>" alt="<?php echo $branch_step['image']['alt']; ?>">
                            </div>
                        <?php endif; ?>
                        <div class="title yekan-18 color-black-70"><?php echo $branch_step['title']; ?></div>
                    </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php endif; ?>


    <?php
    $form_id = get_field('form_id', $page_id) ?: [];
    if ($form_id):
        ?>
        <section class="container flex flex-col">
            <div class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40">
                 ثبت درخواست 
            </div>
            <div class="request-branch-area flex flex-col-reverse md:flex-row bg-black-03">
                
                <div class="contact-form">
                    <?php
                    if (shortcode_exists('contact-form-7')) {
                        echo do_shortcode('[contact-form-7 id="' . $form_id . '"]');
                    }
                    ?>
                </div>
                <div class="flex flex-col items-center cta-wrapper flex-shrink-0">
                    <?php
                    $request_branch_icon = Helper::getAssetUri('/images/request_branch.png');
                    echo '<img src="' . esc_url($request_branch_icon) . '" alt="Request Branch Icon" />';
                    ?>
                    <div class="text yekan-16 md:yekan-22 text-center color-black-80">
                        براى مشاوره و كسب اطلاعات بيشتر جهت دريافت نمايندکی فروش تانیل چوب ، اطلاعات خود را براى ما ازطريق این لیست ارسال كنيد تا کارشناسان مان در اسرع وقت با شما تماس بگیرند
                    </div>
                </div>
            </div>
            

        </section>
    <?php endif; ?>
    <?php
    $faqs = get_field('faqs', $page_id) ?: [];
    if ($faqs):
        ?>
        <section class="container flex flex-col gap-20">
            <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40">
                سوالات متداول 
            </h2>

            <div class="faqs flex flex-col gap-20">
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item slide-down-wrapper flex flex-col gap-20">
                        <div
                            class="faq-question slide-down-trigger  flex justify-between items-center cursor-pointer">
                            <h3 class="yekan-14 md:yekan-24 color-black-80 regular"><?php echo $faq['question']; ?></h3>
                        </div>
                        <div class="faq-answer slide-down-content yekan-12 md:yekan-24 text-center md:text-right color-black-70" style="display: none;">
                            <p><?php echo $faq['answer']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
        $get_branch_benefits = get_field('get_branch_steps_benefits', $page_id);
        if( $get_branch_benefits ):
    ?>
    <section class="container flex flex-col gap-20 items-center">
        <div class="page_blocks_title text-center border-box yekan-18 md:yekan-28 bold color-primary mt-40 w-100">
            مزیت های نمایندگی تانیل چوب
        </div>

        <div class="branch_benefits flex flex-col md:flex-row gap-20 w-100">
            <?php 
                foreach($get_branch_benefits as $branch_benefit): 
                ?>
                <div class="benefit border-box flex flex-col gap-15 items-center relative w-100">
                        <?php if(isset($branch_benefit['image']['url'])): ?>
                            <div class="image_wrapper w-100 flex item-center">
                                <img src="<?php echo $branch_benefit['image']['url']; ?>" alt="<?php echo $branch_benefit['image']['alt']; ?>">
                            </div>
                        <?php endif; ?>
                        <div class="title yekan-14 md:yekan-20 bold color-primary text-center mt-10"><?php echo $branch_benefit['title']; ?></div>
                        <div class="description yekan-12 md:yekan-16 color-black-70 text-center"><?php echo $branch_benefit['description']; ?></div>
                    </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php endif; ?>
                        
    <section class="call-us flex flex-col items-center mt-40 bg-black-05">
        <div class="title yekan-18 md:yekan-30 bold color-primary">
            برای دريافت نمايندگى اقدام كنيد
        </div>
        <div class="subtitle yekan-12 md:yekan-24 text-center md:text-right color-black-60">
            كارشناسان ما آماده باسخگويى به سوالات وراهنمايى شما هستند
        </div>
        <div class="button mt-25">
            <a href="<?php echo get_field('button_url', $page_id) ?: '#'; ?>" class="yekan-14 md:yekan-20 color-white bg-primary block">
                <?php echo get_field('button_text', $page_id) ?: 'تماس با ما'; ?>
            </a>
        </div>
    </section>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */