<?php




function pd_theme_settings_scripts_admin() {

    wp_enqueue_style( 'pd_theme_settings_css', plugins_url('/pd_theme_settings/assets/css/style.css'));    

}

add_action('admin_enqueue_scripts', 'pd_theme_settings_scripts_admin', 2000);




function pd_theme_settings_scripts_front() {

    $scripts = get_option('scripts');

    $css_styles = [];
    $js_scripts = [];


    if ($scripts['scripts_font_awesome']) :
        if ( $scripts['scripts_font_awesome'] == 'on' ) {
            $css_styles[] = ["font-awesome", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css", "5.15.1"];
        }
    endif;
    if ($scripts['scripts_owl_carousel']) :
        if ( $scripts['scripts_owl_carousel'] == 'on' ) {
            $css_styles[] = ["owl-carousel", plugins_url("/pd_theme_settings/assets/css/owl.carousel.css")];
            $css_styles[] = ["owl-carousel-default", plugins_url("/pd_theme_settings/assets/css/owl.theme.default.css")];
            $js_scripts[] = ["owl-carousel-min", plugins_url("/pd_theme_settings/assets/js/owl.carousel.min.js"), "array('jquery')", "1.0.0", "true"];
        }
    endif;
    if ($scripts['scripts_parallax']) :
        if ( $scripts['scripts_parallax'] == 'on' ) {
            $js_scripts[] = ["parallax-min", plugins_url("/pd_theme_settings/assets/js/parallax.min.js"), "array('jquery')", "1.0.0", "true"];
        }    
    endif;
    if ($scripts['scripts_vanilla_lazyload']) :
        if ( $scripts['scripts_vanilla_lazyload'] == 'on' ) {
            $js_scripts[] = ["parallax-min", "https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.4.0/dist/lazyload.min.js", "", "17.4.0", "true"];
        }
    endif;

    foreach($css_styles as $css_style) {
        wp_enqueue_style( $css_style[0], $css_style[1], $css_style[2]);
    }
    foreach($js_scripts as $js_script) {
        wp_enqueue_script( $js_script[0], $js_script[1], $js_script[2], $js_script[3], $js_script[4]);
    }    

}

add_action('wp_enqueue_scripts', 'pd_theme_settings_scripts_front');



