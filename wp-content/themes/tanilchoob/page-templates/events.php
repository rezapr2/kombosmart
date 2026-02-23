<?php

/**
 * Template Name: Events
 * Description: A custom Events page template.
 * @package TanilChoob
 */


get_header(); 

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();

$end_off_sale = get_field('end_off_sale', $page_id);
$off_products = get_field('off_products', $page_id);
$see_all_url = get_field('see_all_url', $page_id);
$customers_commetns = get_field('customers_commetns', $page_id);
$event_form_id = get_field('event_form_id', $page_id);
?>


<div class="home-page wrapper">
    <?php
    $page_id = get_the_ID();

    /* Hero Slider */
    get_template_part('template-parts/home/hero_slider');


    if( isset($end_off_sale) && $end_off_sale ): ?>
    <div class="md:container">
        <div class=" countdown_wrapper flex flex-col gap-10 md:gap-30 justify-center items-center">
            <div class="yekan-20 md:yekan-30 bold color-black">زمان باقی مانده تا پایان جشنواره:</div>
            <div class="countdown-timer regular" data-end-date="<?php echo esc_attr($end_off_sale); ?>">
                <div class="countdown-timer__time flex gap-10 md:gap-20 w-full h-100 justify-evenly">
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__seconds yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">ثانیه</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__minutes yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">دقیقه</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__hours yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">ساعت</div>
							</div>
							<div class="countdown-timer__item flex flex-col text-center item-center">
								<div class="countdown-timer__days yekan-18 md:yekan-30">00</div>
								<div class="countdown-timer__label yekan-12 md:yekan-20">روز</div>
							</div>
						</div>
            </div>
        </div>
    </div>

    <?php endif; ?>
        <div class="container">
            <div class="events_description flex flex-col items-center gap-04">
                <div class="color-primary yekan-16 md:yekan-28 text-center">
                    رویدادهای خاص تانیل چوب درمناسب های مختلف
                </div>
                <div class="yekan-12 md:yekan-20 color-black-60 text-center description">
                    ما در تانیل چوب در مناسبت هایی مانند عید نوروز ، بلک فردایدی و اعیاد مذهبی ، تخفیف هایی باورنکردنی روی محصولات خاص ارائه می دهیم
                </div>
            </div>
        </div>
    <?php
    if(isset($off_products) && $off_products): ?>
    <div class="container off_products flex flex-col items-center">
        <div class="title yekan-20 md:yekan-30 bold text-center color-black">محصولات <span>تخفیف</span> خورده</div>
        <div class="products grid grid-cols-1 md:grid-cols-4 w-full">
            <?php 

                $off_product_ids = array_map('absint', (array) $off_products);
                $args = [
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => count($off_product_ids),
                    'post__in' => $off_product_ids,
                    'orderby' => 'post__in',
                ];
                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                    get_template_part('template-parts/cards/product-card', null, ['post_id' => get_the_ID()]);
                    endwhile;
                    wp_reset_postdata();
                endif;
            ?>
        </div>
        <?php if(isset($see_all_url['url']) && $see_all_url):?>
            <a href="<?php echo esc_url($see_all_url['url']); ?>" class="button see_all color-primary yekan-14 md:yekan-18"><?php echo $see_all_url['title']; ?></a>
        <?php endif; ?>
    </div>
    <?php endif;
    /* Features */
    get_template_part('template-parts/home/features');

    if(isset($customers_commetns) && $customers_commetns): ?>
        <div class="container customers_commetns_wrapper flex flex-col">
            <div class="title yekan-20 md:yekan-30 bold text-center color-primary">نظرات مشتریان</div>
            <div class="customers_commetns grid grid-cols-1 md:grid-cols-2">
                <?php foreach($customers_commetns as $customer): ?>
                    <div class="comment flex flex-col gap-15">
                        <div class="name yekan-18 md:yekan-20 color-black-50"><?php echo isset($customer['name']) ? $customer['name'] : ''; ?></div>
                        <div class="comment_text yekan-14 md:yekan-24 color-black-80"><?php echo isset($customer['comment_text']) ? $customer['comment_text'] : ''; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="md:container mb-20 md:mb-40 mt-20 md:mt-40">
	<div id="help_cta" class="cta help-cta py-40 px-25 md:px-40">
		<div class="flex flex-col md:flex-row justify-between items-center">
			<div class="flex flex-col">
				<div class="yekan-24 md:yekan-34 text-center md:text-right color-white">با مدیریت فروش تماس بگیرید </div>
				<div class="yekan-18 md:yekan-24 text-center md:text-right color-white-70 mb-25 md:mb-0">از طريق تماس يا واتساب مى توانيد سفارش دهيد يا از رويدادهاى بعدى مطلع شويد.</div>
			</div>
			<div class="flex flex-col gap-04">
				<?php if(isset($contact_box['whatsapp_link']) && $contact_box['whatsapp_link']): ?>
				<a href="<?php echo $contact_box['whatsapp_link']; ?>" class="btn flex items-center gap-10 yekan-22 color-white">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6.9 20.6C8.4 21.5 10.2 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 13.8 2.5 15.5 3.3 17L2.44044 20.306C2.24572 21.0549 2.93892 21.7317 3.68299 21.5191L6.9 20.6Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M16.5 14.8485C16.5 15.0105 16.4639 15.177 16.3873 15.339C16.3107 15.501 16.2116 15.654 16.0809 15.798C15.86 16.041 15.6167 16.2165 15.3418 16.329C15.0714 16.4415 14.7784 16.5 14.4629 16.5C14.0033 16.5 13.512 16.392 12.9937 16.1715C12.4755 15.951 11.9572 15.654 11.4434 15.2805C10.9251 14.9025 10.4339 14.484 9.9652 14.0205C9.501 13.5525 9.08187 13.062 8.70781 12.549C8.33826 12.036 8.04081 11.523 7.82449 11.0145C7.60816 10.5015 7.5 10.011 7.5 9.543C7.5 9.237 7.55408 8.9445 7.66224 8.6745C7.77041 8.4 7.94166 8.148 8.18052 7.923C8.46895 7.6395 8.78443 7.5 9.11793 7.5C9.24412 7.5 9.37031 7.527 9.48297 7.581C9.60015 7.635 9.70381 7.716 9.78493 7.833L10.8305 9.3045C10.9116 9.417 10.9702 9.5205 11.0108 9.6195C11.0513 9.714 11.0739 9.8085 11.0739 9.894C11.0739 10.002 11.0423 10.11 10.9792 10.2135C10.9206 10.317 10.835 10.425 10.7268 10.533L10.3843 10.8885C10.3348 10.938 10.3122 10.9965 10.3122 11.0685C10.3122 11.1045 10.3167 11.136 10.3257 11.172C10.3393 11.208 10.3528 11.235 10.3618 11.262C10.4429 11.4105 10.5826 11.604 10.7809 11.838C10.9837 12.072 11.2 12.3105 11.4344 12.549C11.6778 12.7875 11.9121 13.008 12.151 13.2105C12.3853 13.4085 12.5791 13.5435 12.7323 13.6245C12.7549 13.6335 12.7819 13.647 12.8135 13.6605C12.8495 13.674 12.8856 13.6785 12.9261 13.6785C13.0028 13.6785 13.0613 13.6515 13.1109 13.602L13.4534 13.2645C13.5661 13.152 13.6743 13.0665 13.7779 13.0125C13.8816 12.9495 13.9852 12.918 14.0979 12.918C14.1835 12.918 14.2737 12.936 14.3728 12.9765C14.472 13.017 14.5756 13.0755 14.6883 13.152L16.18 14.2095C16.2972 14.2905 16.3783 14.385 16.4279 14.4975C16.473 14.61 16.5 14.7225 16.5 14.8485Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10"/>
					</svg>
					<span>پیام در واتساپ</span>
				</a>
				<?php endif; ?>
				<?php if(isset($contact_box['phone_link']) && $contact_box['phone_link']): ?>
				<a href="<?php echo $contact_box['phone_link']; ?>" class="btn flex items-center gap-10 yekan-22 color-white">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="#734B6C" stroke-width="1.5" stroke-miterlimit="10"/>
					</svg>
					<span>تماس تلفنی</span>
				</a>
				<?php endif; ?>
			</div>
		</div>

	</div>

    
</div>
<?php if(isset($event_form_id) && $event_form_id): ?>
        <div class="event_form_wrapper container">
            <div class="event_form flex flex-col md:flex-row items-center justify-between">
                <div class="yekan-22 color-black text-center">
                    برای اطلاع از رويدادهای بعدی شماره تماس خود را وارد کنید:
                </div>
                
                <?php 
                    if ($event_form_id && shortcode_exists('contact-form-7')) {
                        echo do_shortcode('[contact-form-7 id="' . $event_form_id . '" title="فرم اطلاع از رویداد"]');
                    }       
                ?>
                
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
