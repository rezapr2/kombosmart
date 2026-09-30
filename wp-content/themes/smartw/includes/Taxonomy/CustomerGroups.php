<?php

namespace TanilChoob\Theme\PostType;

use TanilChoob\Theme\Abstracts\Taxonomy;

class CustomerGroups extends Taxonomy
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register()
    {
        $labels = array(
            'name'                       => _x('گروه بندی مشتریان', 'Taxonomy General Name', 'smartw'),
            'singular_name'              => _x('گروه مشتری', 'Taxonomy Singular Name', 'smartw'),
            'menu_name'                  => __('گروه بندی مشتریان', 'smartw'),
            'all_items'                  => __('همه گروه‌ها', 'smartw'),
            'parent_item'                => __('گروه والد', 'smartw'),
            'parent_item_colon'          => __('گروه والد:', 'smartw'),
            'new_item_name'              => __('نام گروه جدید', 'smartw'),
            'add_new_item'               => __('افزودن گروه جدید', 'smartw'),
            'edit_item'                  => __('ویرایش گروه', 'smartw'),
            'update_item'                => __('به‌روزرسانی گروه', 'smartw'),
            'view_item'                  => __('مشاهده گروه', 'smartw'),
            'separate_items_with_commas' => __('گروه‌ها را با کاما جدا کنید', 'smartw'),
            'add_or_remove_items'        => __('افزودن یا حذف گروه‌ها', 'smartw'),
            'choose_from_most_used'      => __('انتخاب از پرکاربردترین‌ها', 'smartw'),
            'popular_items'              => __('گروه‌های پرکاربرد', 'smartw'),
            'search_items'               => __('جستجوی گروه‌ها', 'smartw'),
            'not_found'                  => __('گروهی یافت نشد', 'smartw'),
            'no_terms'                   => __('هیچ گروهی وجود ندارد', 'smartw'),
            'items_list'                 => __('لیست گروه‌ها', 'smartw'),
            'items_list_navigation'      => __('ناوبری لیست گروه‌ها', 'smartw'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => false,
            'meta_box_cb' => false,
        );
        register_taxonomy('customer_group', array('tc_message'), $args);
    }
}

new CustomerGroups();
