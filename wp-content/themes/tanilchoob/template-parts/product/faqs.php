<?php
use TanilChoob\Theme\Helper;

$faqs = isset($faqs) ? $faqs : get_query_var('faqs');
$faq_page_link = Helper::get_options_field( 'faq_page_link' ) ?: '#faq-items';

// --- SCHEMA GENERATION START ---
if ($faqs) {
    $faq_schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => []
    ];
    foreach ($faqs as $faq) {
        $faq_schema['mainEntity'][] = [
            '@type' => 'Question',
            'name'  => wp_strip_all_tags($faq['question']),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags($faq['answer'])
            ]
        ];
    }
    echo '<script type="application/ld+json">' . wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
// --- SCHEMA GENERATION END ---
?>

<?php if ($faqs) : ?>
<div class="product-faqs flex flex-col gap-20 mb-40">
	<div class="container flex flex-col-reverse md:flex-row justify-between">
		<div class="faq-items-wrapper flex flex-col gap-20">
			<h2 class="faq-title yekan-28 bold color-primary text-center md:text-right">سوالات متداول</h2>
			<div class="faq-items flex flex-col gap-10">
				<?php foreach ($faqs as $faq) : ?>
					<div class="faq-item slide-down-wrapper flex flex-col">
						<div class="faq-question slide-down-trigger flex justify-between items-center pointer">
							<h3 class="yekan-16 md:yekan-20 color-black-80 regular"><?php echo $faq['question']; ?></h3>
						</div>
						<p class="faq-answer slide-down-content yekan-14 md:yekan-20 text-center md:text-right color-black" style="display: none;"><?php echo $faq['answer']; ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="faqs-desc flex flex-col items-center justify-center">
			<?php
			$faq_icon = Helper::getAssetUri('/images/faq_icon.png');
			echo '<img src="' . esc_url($faq_icon) . '" alt="FAQ Icon" />';
			?>
			<div class="faq-icon-text yekan-22 md:yekan-26 color-black-80">شما عزیزان می توانید با مراجعه به بخش <a href="<?php echo esc_url($faq_page_link); ?>" class="color-white bg-black px-25 inline-block">( پرسش های متدوال)</a> بخش تمامی سوالات احتمالی خود را دریافت کنید</div>
		</div>
	</div>
</div>
<?php endif; ?>
