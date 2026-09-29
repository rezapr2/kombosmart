<?php
// Skip links whose target page isn't configured in theme options.
if (empty($args['url']) || $args['url'] === '#') {
	return;
}
?>
<div class="consult-cta w-full bg-white flex items-center justify-between gap-10">
	<div class="flex items-center gap-10">
		<div class="divider bg-black hidden md:flex"></div>
		<?php if (isset($args['icon'])) {
			echo $args['icon'];
		} ?>
		<span class="color-black yekan-16 md:yekan-24"><?php echo $args['label']; ?></span>
	</div>

	<!-- Click CTA -->
	<a href="<?php echo $args['url']; ?>" class="more-btn bg-black-05 yekan-18 color-black" aria-label="<?php echo esc_attr(sprintf(__('کلیک کنید برای %s', 'tanilchoob'), $args['label'])); ?>">
		<span class="hidden md:flex">
		کلیک کنید
		</span>
		<div class="icon flex md:hidden">
			<svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M13.2622 15.5307L9.74219 12.0007L13.2622 8.4707" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</div>

	</a>
</div>