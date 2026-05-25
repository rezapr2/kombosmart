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
            'name'                       => _x('گروه بندی مشتریان', 'Taxonomy General Name', 'tanilchoob'),
            'singular_name'              => _x('گروه مشتری', 'Taxonomy Singular Name', 'tanilchoob'),
            'menu_name'                  => __('گروه بندی مشتریان', 'tanilchoob'),
            'all_items'                  => __('همه گروه‌ها', 'tanilchoob'),
            'parent_item'                => __('گروه والد', 'tanilchoob'),
            'parent_item_colon'          => __('گروه والد:', 'tanilchoob'),
            'new_item_name'              => __('نام گروه جدید', 'tanilchoob'),
            'add_new_item'               => __('افزودن گروه جدید', 'tanilchoob'),
            'edit_item'                  => __('ویرایش گروه', 'tanilchoob'),
            'update_item'                => __('به‌روزرسانی گروه', 'tanilchoob'),
            'view_item'                  => __('مشاهده گروه', 'tanilchoob'),
            'separate_items_with_commas' => __('گروه‌ها را با کاما جدا کنید', 'tanilchoob'),
            'add_or_remove_items'        => __('افزودن یا حذف گروه‌ها', 'tanilchoob'),
            'choose_from_most_used'      => __('انتخاب از پرکاربردترین‌ها', 'tanilchoob'),
            'popular_items'              => __('گروه‌های پرکاربرد', 'tanilchoob'),
            'search_items'               => __('جستجوی گروه‌ها', 'tanilchoob'),
            'not_found'                  => __('گروهی یافت نشد', 'tanilchoob'),
            'no_terms'                   => __('هیچ گروهی وجود ندارد', 'tanilchoob'),
            'items_list'                 => __('لیست گروه‌ها', 'tanilchoob'),
            'items_list_navigation'      => __('ناوبری لیست گروه‌ها', 'tanilchoob'),
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
