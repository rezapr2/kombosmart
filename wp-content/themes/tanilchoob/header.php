<?php
/**
 * Theme Header
 *
 * Header data.
 *
 * @since   1.0.0
 * @package WP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use TanilChoob\Theme\Helper;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <meta name="theme-color" content="#5D0E87">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php get_template_part('template-parts/header'); ?>
