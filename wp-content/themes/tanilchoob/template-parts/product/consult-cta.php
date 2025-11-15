<div class="consult-cta w-full bg-white flex items-center justify-between gap-10">
	<div class="flex items-center gap-10">
		<div class="divider bg-black"></div>
		<?php if (isset($args['icon'])) {
			echo $args['icon'];
		} ?>
		<span class="color-black yekan-24"><?php echo $args['label']; ?></span>
	</div>

	<!-- Click CTA -->
	<a href="#<?php echo $args['url']; ?>" class="more-btn bg-black-05 yekan-18 color-black">کلیک کنید</a>
</div>