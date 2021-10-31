<?php

/*
*
*   Plugin Name: Ustawienia Motywu
*   Description: Plugin umożliwia kontrolę nad skryptami i funkcjonalnościami które mają się ładować na stronie. Oprócz tego daje możliwość dodawania własnych custom post types. 
*/



/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */

 
 
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/CMB2/init.php';
}


if ( file_exists( dirname( __FILE__ ) . './cmb2/plugins/cmb2-conditionals.php' ) ) {
    require_once dirname( __FILE__ ) . './cmb2/plugins/cmb2-conditionals.php';

}






require_once(plugin_dir_path(__FILE__) . './libs/scripts.php');
require_once(plugin_dir_path(__FILE__) . './libs/custom-post-types.php');
// require_once(plugin_dir_path(__FILE__) . './libs/breadcrumbs.php');
require_once(plugin_dir_path(__FILE__) . './libs/admin-view.php');



