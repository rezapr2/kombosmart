<?php
/**
 * Template Name: Contact Us
 * Description: A custom Contact Us page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="container mt-25 mb-25">
    <div class="contact-hero flex gap-20 items-center justify-center">
        <a class="back-btn circle-radius bg-black-03 flex item-center" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Back to home">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.75 7.75H14.75M14.75 7.75L7.75 0.75M14.75 7.75L7.75 14.75" stroke="#909090" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="yekan-28 color-black bold"><?php the_title(); ?></h1>
    </div>
    <?php if (has_post_thumbnail()) : ?>
        <div class="contact-featured mt-20 flex justify-center">
            <?php the_post_thumbnail('large', ['class' => 'radius-16']); ?>
        </div>
    <?php endif; ?>
</div>

<div class="container mb-40">
    <div class="flex gap-20 items-start">
        <div class="contact-info flex-1 bg-black-03 px-25 py-25 radius-16">
            <h2 class="yekan-20 color-black-80 mb-15"><?php esc_html_e('Contact Information', 'tanilchoob'); ?></h2>
            <ul class="flex flex-col gap-10">
                <li class="flex items-center gap-10">
                    <span class="yekan-16 color-black-60"><?php esc_html_e('Phone:', 'tanilchoob'); ?></span>
                    <a class="yekan-16 color-primary" href="tel:+98XXXXXXXXXX">+98 XX XXX XXXX</a>
                </li>
                <li class="flex items-center gap-10">
                    <span class="yekan-16 color-black-60"><?php esc_html_e('Email:', 'tanilchoob'); ?></span>
                    <a class="yekan-16 color-primary" href="mailto:info@example.com">info@example.com</a>
                </li>
                <li class="flex items-start gap-10">
                    <span class="yekan-16 color-black-60"><?php esc_html_e('Address:', 'tanilchoob'); ?></span>
                    <span class="yekan-16 color-black-80">123 Example Street, City, Country</span>
                </li>
            </ul>
        </div>

        <div class="contact-form flex-1 bg-black-03 px-25 py-25 radius-16">
            <h2 class="yekan-20 color-black-80 mb-15"><?php esc_html_e('Send us a message', 'tanilchoob'); ?></h2>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="flex flex-col gap-12" novalidate>
                <input type="hidden" name="action" value="tanilchoob_contact">
                <?php wp_nonce_field('tanilchoob_contact_action', 'tanilchoob_contact_nonce'); ?>
                <div class="flex flex-col gap-06">
                    <label for="contact_name" class="yekan-16 color-black-80"><?php esc_html_e('Your name', 'tanilchoob'); ?> <span class="required">*</span></label>
                    <input id="contact_name" name="contact_name" type="text" class="yekan-16" required autocomplete="name" />
                </div>

                <div class="flex flex-col gap-06">
                    <label for="contact_email" class="yekan-16 color-black-80"><?php esc_html_e('Email (optional)', 'tanilchoob'); ?></label>
                    <input id="contact_email" name="contact_email" type="email" class="yekan-16" autocomplete="email" />
                </div>

                <div class="flex flex-col gap-06">
                    <label for="contact_phone" class="yekan-16 color-black-80"><?php esc_html_e('Phone (optional)', 'tanilchoob'); ?></label>
                    <input id="contact_phone" name="contact_phone" type="tel" class="yekan-16" autocomplete="tel" />
                </div>

                <div class="flex flex-col gap-06">
                    <label for="contact_message" class="yekan-16 color-black-80"><?php esc_html_e('Message', 'tanilchoob'); ?> <span class="required">*</span></label>
                    <textarea id="contact_message" name="contact_message" rows="6" class="yekan-16" required></textarea>
                </div>

                <button type="submit" class="yekan-18 color-white bg-black px-20 py-12 radius-08 border-none pointer">
                    <?php esc_html_e('Send', 'tanilchoob'); ?>
                </button>

                <?php if (!empty($_GET['contact'])) : ?>
                    <?php if ($_GET['contact'] === 'success') : ?>
                        <p class="yekan-16 color-primary mt-10"><?php esc_html_e('Your message has been sent. Thank you!', 'tanilchoob'); ?></p>
                    <?php elseif ($_GET['contact'] === 'error') : ?>
                        <p class="yekan-16 color-error mt-10"><?php echo esc_html(isset($_GET['reason']) ? $_GET['reason'] : __('An error occurred. Please try again.', 'tanilchoob')); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="contact-content mt-25">
        <?php while (have_posts()) : the_post(); ?>
            <div class="entry-content yekan-16 color-black-80">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */