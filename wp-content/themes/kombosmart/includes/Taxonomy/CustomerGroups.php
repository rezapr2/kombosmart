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
            'name'                       => _x('گروه بندی مشتریان', 'Taxonomy General Name', 'kombosmart'),
            'singular_name'              => _x('گروه مشتری', 'Taxonomy Singular Name', 'kombosmart'),
            'menu_name'                  => __('گروه بندی مشتریان', 'kombosmart'),
            'all_items'                  => __('همه گروه‌ها', 'kombosmart'),
            'parent_item'                => __('گروه والد', 'kombosmart'),
            'parent_item_colon'          => __('گروه والد:', 'kombosmart'),
            'new_item_name'              => __('نام گروه جدید', 'kombosmart'),
            'add_new_item'               => __('افزودن گروه جدید', 'kombosmart'),
            'edit_item'                  => __('ویرایش گروه', 'kombosmart'),
            'update_item'                => __('به‌روزرسانی گروه', 'kombosmart'),
            'view_item'                  => __('مشاهده گروه', 'kombosmart'),
            'separate_items_with_commas' => __('گروه‌ها را با کاما جدا کنید', 'kombosmart'),
            'add_or_remove_items'        => __('افزودن یا حذف گروه‌ها', 'kombosmart'),
            'choose_from_most_used'      => __('انتخاب از پرکاربردترین‌ها', 'kombosmart'),
            'popular_items'              => __('گروه‌های پرکاربرد', 'kombosmart'),
            'search_items'               => __('جستجوی گروه‌ها', 'kombosmart'),
            'not_found'                  => __('گروهی یافت نشد', 'kombosmart'),
            'no_terms'                   => __('هیچ گروهی وجود ندارد', 'kombosmart'),
            'items_list'                 => __('لیست گروه‌ها', 'kombosmart'),
            'items_list_navigation'      => __('ناوبری لیست گروه‌ها', 'kombosmart'),
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
