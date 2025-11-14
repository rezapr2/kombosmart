<?php
$faqs = isset($faqs) ? $faqs : get_query_var('faqs');
?>
<?php if ($faqs) : ?>
<div class="product-faqs flex flex-col gap-20 mb-40">
	<div class="container flex">
		<div class="flex flex-col gap-20">
			<div class="faq-title yekan-28 bold color-primary">سوالات متداول</div>
			<div class="faq-items flex flex-col gap-10">
				<?php foreach ($faqs as $faq) : ?>
					<div class="faq-item slide-down-wrapper flex flex-col">
						<div class="faq-question slide-down-trigger yekan-20 color-black-80 flex justify-between items-center cursor-pointer">
							<span><?php echo $faq['question']; ?></span>
						</div>
						<div class="faq-answer slide-down-content yekan-20 color-black" style="display: none;"><?php echo $faq['answer']; ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="faqs-desc flex flex-col items-center justify-center">
			<?php
			$faq_icon = function_exists('get_theme_file_uri')
				? get_theme_file_uri('images/faq_icon.png')
				: get_stylesheet_directory_uri() . '/images/faq_icon.png';
			echo '<img src="' . esc_url($faq_icon) . '" alt="FAQ Icon" />';
			?>
			<div class="faq-icon-text yekan-26 color-black-80">شما عزیزان می توانید با مراجعه به بخش <a href="#faq-items" class="color-white bg-black px-25 inline-block">( پرسش های متدوال)</a> بخش تمامی سوالات احتمالی خود را دریافت کنید</div>
		</div>
	</div>

</div>
<?php endif; ?>