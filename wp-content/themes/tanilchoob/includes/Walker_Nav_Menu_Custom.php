<?php

namespace TanilChoob\Theme;

class Walker_Nav_Menu_Custom extends \Walker_Nav_Menu
{

    /**
     * Start the element output.
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $attributes  = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) . '"' : '';
        $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn) . '"' : '';
        $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url) . '"' : '';

        $item_output = isset($args->before) ? $args->before : '';
        $item_output .= '<a class="flex items-center" ' . $attributes . '>';

        // Get SVG icon based on menu item classes
        $svg_icon = $this->get_svg_by_class($classes);
        if ($svg_icon) {
            $item_output .= $svg_icon;
        }

        $item_output .= isset($args->link_before) ? $args->link_before : '';

        // Show text unless it's an icon-only menu item
        if (! $this->is_icon_only($classes)) {
            $item_output .= apply_filters('the_title', $item->title, $item->ID);
        }

        $item_output .= isset($args->link_after) ? $args->link_after : '';
        $item_output .= '</a>';
        $item_output .= isset($args->after) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * Get SVG icon based on menu item classes
     */
    private function get_svg_by_class($classes)
    {
        $svg_icons = [
            'menu-icon-home' => '<svg class="icon home-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>',

            'menu-icon-signpost' => '<svg class="icon signpost" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M14.3502 1.6665H7.46695C7.13362 1.6665 6.81693 1.78317 6.5586 1.98317L4.73359 3.44151C4.00026 4.02484 4.00026 5.13316 4.73359 5.71649L6.5586 7.17483C6.81693 7.38316 7.14195 7.4915 7.46695 7.4915H14.3502C15.1586 7.4915 15.8086 6.8415 15.8086 6.03316V3.1165C15.8086 2.3165 15.1586 1.6665 14.3502 1.6665Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5.66732 10H12.5507C12.884 10 13.2007 10.1167 13.459 10.3167L15.284 11.775C16.0174 12.3583 16.0174 13.4667 15.284 14.05L13.459 15.5083C13.2007 15.7167 12.8757 15.825 12.5507 15.825H5.66732C4.85898 15.825 4.20898 15.175 4.20898 14.3667V11.45C4.20898 10.65 4.85898 10 5.66732 10Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 10V7.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 18.3335V15.8335" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7.5 18.3335H12.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

            'menu-icon-info-circle' => '<svg class="icon info-circle" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M9.99935 18.3332C14.5827 18.3332 18.3327 14.5832 18.3327 9.99984C18.3327 5.4165 14.5827 1.6665 9.99935 1.6665C5.41602 1.6665 1.66602 5.4165 1.66602 9.99984C1.66602 14.5832 5.41602 18.3332 9.99935 18.3332Z" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 6.6665V10.8332" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.99609 13.3335H10.0036" stroke="#4D4D4D" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

            'menu-icon-document-text' => '<svg class="icon document-text" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.5 5.83317V14.1665C17.5 16.6665 16.25 18.3332 13.3333 18.3332H6.66667C3.75 18.3332 2.5 16.6665 2.5 14.1665V5.83317C2.5 3.33317 3.75 1.6665 6.66667 1.6665H13.3333C16.25 1.6665 17.5 3.33317 17.5 5.83317Z" stroke="#4D4D4D" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12.084 3.75V5.41667C12.084 6.33333 12.834 7.08333 13.7507 7.08333H15.4173" stroke="#4D4D4D" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 10.8335H9.99935" stroke="#4D4D4D" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.66602 14.1665H13.3327" stroke="#4D4D4D" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

            'menu-icon-call' => '<svg class="icon call" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M18.3077 15.2748C18.3077 15.5748 18.241 15.8832 18.0993 16.1832C17.9577 16.4832 17.7744 16.7665 17.5327 17.0332C17.1244 17.4832 16.6743 17.8082 16.166 18.0165C15.666 18.2248 15.1244 18.3332 14.541 18.3332C13.691 18.3332 12.7827 18.1332 11.8243 17.7248C10.866 17.3165 9.90768 16.7665 8.95768 16.0748C7.99935 15.3748 7.09102 14.5998 6.22435 13.7415C5.36602 12.8748 4.59102 11.9665 3.89935 11.0165C3.21602 10.0665 2.66602 9.1165 2.26602 8.17484C1.86602 7.22484 1.66602 6.3165 1.66602 5.44984C1.66602 4.88317 1.76602 4.3415 1.96602 3.8415C2.16602 3.33317 2.48268 2.8665 2.92435 2.44984C3.45768 1.92484 4.04102 1.6665 4.65768 1.6665C4.89102 1.6665 5.12435 1.7165 5.33268 1.8165C5.54935 1.9165 5.74102 2.0665 5.89102 2.28317L7.82435 5.00817C7.97435 5.2165 8.08268 5.40817 8.15768 5.5915C8.23268 5.7665 8.27435 5.9415 8.27435 6.09984C8.27435 6.29984 8.21602 6.49984 8.09935 6.6915C7.99102 6.88317 7.83268 7.08317 7.63268 7.28317L6.99935 7.9415C6.90768 8.03317 6.86602 8.1415 6.86602 8.27484C6.86602 8.3415 6.87435 8.39984 6.89102 8.4665C6.91602 8.53317 6.94102 8.58317 6.95768 8.63317C7.10768 8.90817 7.36602 9.2665 7.73268 9.69984C8.10768 10.1332 8.50768 10.5748 8.94102 11.0165C9.39102 11.4582 9.82435 11.8665 10.266 12.2415C10.6993 12.6082 11.0577 12.8582 11.341 13.0082C11.3827 13.0248 11.4327 13.0498 11.491 13.0748C11.5577 13.0998 11.6243 13.1082 11.6993 13.1082C11.841 13.1082 11.9493 13.0582 12.041 12.9665L12.6743 12.3415C12.8827 12.1332 13.0827 11.9748 13.2743 11.8748C13.466 11.7582 13.6577 11.6998 13.866 11.6998C14.0243 11.6998 14.191 11.7332 14.3743 11.8082C14.5577 11.8832 14.7494 11.9915 14.9577 12.1332L17.716 14.0915C17.9327 14.2415 18.0827 14.4165 18.1743 14.6248C18.2577 14.8332 18.3077 15.0415 18.3077 15.2748Z" stroke="#4D4D4D" stroke-width="1.25" stroke-miterlimit="10"/>
</svg>
',

            'menu-icon-location-tick' => '<svg  class="icon location-tick"  width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3.01675 7.07484C4.65842 -0.141827 15.3501 -0.133494 16.9834 7.08317C17.9418 11.3165 15.3084 14.8998 13.0001 17.1165C11.3251 18.7332 8.67508 18.7332 6.99175 17.1165C4.69175 14.8998 2.05842 11.3082 3.01675 7.07484Z" stroke="#4D4D4D" stroke-width="1.25"/>
<path d="M7.70898 9.58333L8.95898 10.8333L12.2923 7.5" stroke="#4D4D4D" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
'
        ];

        // Check if any of the menu item classes match our icon classes
        foreach ($classes as $class) {
            if (isset($svg_icons[$class])) {
                return $svg_icons[$class];
            }
        }

        return null;
    }

    /**
     * Check if menu item should be icon-only (no text)
     */
    private function is_icon_only($classes)
    {
        $icon_only_classes = [
            'menu-icon-only',
            'icon-only'
        ];

        foreach ($classes as $class) {
            if (in_array($class, $icon_only_classes)) {
                return true;
            }
        }

        return false;
    }
}
