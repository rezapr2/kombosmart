<?php

namespace TanilChoob\Theme\PostType;

use TanilChoob\Theme\Abstracts\PostType;
use TanilChoob\Theme\Helper;

class Story extends PostType
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register()
    {
        $name = 'استوری';
        $singular = 'استوری';
        $labels = array(
            'name' => $name,
            'singular_name' => $singular,            
            'add_new' => sprintf(_x('افزودن %s', 'smartw', 'smartw'), $singular),
            'add_new_item' => sprintf(__('افزودن %s', 'smartw'), $singular),
            'edit_item' => sprintf(__('ویرایش %s', 'smartw'), $singular),
            'new_item' => sprintf(__('جدید %s', 'smartw'), $singular),
            'view_item' => sprintf(__('دیدن %s', 'smartw'), $singular),
            'view_items' => sprintf(__('دیدن %s', 'smartw'), $name),
            'search_items' => sprintf(__('جستجو %s', 'smartw'), $name),
            'not_found' => sprintf(__('هیچ %s یافت نشد', 'smartw'), $name),
            'not_found_in_trash' => sprintf(__('هیچ %s در زباله دان یافت نشد', 'smartw'), $name),
            'parent_item_colon' => sprintf(__('Parent %s:', 'smartw'), $singular),
            'menu_name' => sprintf(__('%s', 'smartw'), $name),
        );


        $args = array(
            'label' => __('Stories', 'smartw'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-format-status',
            'has_archive' => false,
            'supports' => ['title', 'thumbnail'],
            'hierarchical' => false,
            'description' => '',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'menu_position' => null,
            'show_in_nav_menus' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'query_var' => true,
            'can_export' => true,
            'capability_type' => 'post',
        );

        register_post_type('story', $args);
    }
}

new Story();
