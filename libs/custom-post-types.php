<?php


// jak zrobić pętlę po wszystkich funkcjach ? 






        function pd_theme_setting_cpt() {

            $cpts_arrays_wrap = get_option('cpt');

            foreach($cpts_arrays_wrap as $cpt_arrays) {
            foreach($cpt_arrays as $cpt) {

                $cpt_name = $cpt['cpt_name'] ?? '';
                $cpt_singular_name = $cpt['cpt_singular_name'] ?? '';
                $cpt_add_new = $cpt['cpt_add_new'] ?? '';
                $cpt_add_new_item = $cpt['cpt_add_new_item'] ?? '';

                $cpt_label = $cpt['cpt_label'] ?? '';
                $cpt_archive_slug = $cpt['cpt_archive_slug'] ?? '';
                $cpt_menu_icon = $cpt['cpt_menu_icon'] ?? '';

                $cpt_post_type_name = $cpt['cpt_post_type_name'] ?? '';

                $labels = [
                    "name" => __( "{$cpt_name}", "pd_theme_settings" ),
                    "singular_name" => __( "{$cpt_singular_name}", "pd_theme_settings" ),
                    'add_new' => "{$cpt_add_new}",
                    'add_new_item' => "{$cpt_add_new_item}"
                ];

                $args = [
                    "label" => __( "{$cpt_label}", "pd_theme_settings" ),
                    "labels" => $labels,
                    "description" => "",
                    "public" => true,
                    "publicly_queryable" => true,
                    "show_ui" => true,
                    "show_in_rest" => true,
                    "rest_base" => "",
                    "rest_controller_class" => "WP_REST_Posts_Controller",
                    "has_archive" => false,
                    "show_in_menu" => true,
                    "show_in_nav_menus" => true,
                    "delete_with_user" => false,
                    "exclude_from_search" => false,
                    "capability_type" => "page",
                    "map_meta_cap" => true,
                    "hierarchical" => false,
                    "rewrite" => [ "slug" => "{$cpt_archive_slug}", "with_front" => true ],
                    "query_var" => true,
                    "menu_icon" => "{$cpt_menu_icon}",
                    "supports" => [ "title", "custom-fields", "thumbnail", "editor"],
                ];

                register_post_type( "{$cpt_post_type_name}", $args );

            }

            }
            flush_rewrite_rules();
        }




add_action( 'init', 'pd_theme_setting_cpt');



// $pd_theme_setting_cpts zawiera nazwę funkcji

// foreach($pd_theme_setting_cpts as $pd_theme_setting_cpt) {
//     
// }