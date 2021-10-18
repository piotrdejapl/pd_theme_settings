<?php




//avoid direct calls to this file where wp core files not present
if (!function_exists ('add_action')) {
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
}

define('HOWTO_METABOX_ADMIN_PAGE_NAME', 'howto_metaboxes');

//class that reperesent the complete plugin
class howto_metabox_plugin {


    /**
     * Holds an instance of the project
     *
     * @Prefix_Add_CMB2_To_Settings_Page
     **/
    protected static $instance = null;



    //constructor of class, PHP4 compatible construction for backward compatibility
    function howto_metabox_plugin() {
        //add filter for WordPress 2.8 changed backend box system !
        add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
        //register callback for admin menu  setup
        add_action('admin_menu', array(&$this, 'on_admin_menu')); 
        //register the callback been used if options of page been submitted and needs to be processed
        add_action('admin_post_save_howto_metaboxes_general', array(&$this, 'on_save_changes'));
        add_action( 'cmb2_admin_init', 'yourprefix_register_demo_metabox' );
    }
	
    //for WordPress 2.8 we have to tell, that we support 2 columns !
    function on_screen_layout_columns($columns, $screen) {
        if ($screen == $this->pagehook) {
            $columns[$this->pagehook] = 2;
        }
        return $columns;
    }


	
    //extend the admin menu
    function on_admin_menu() {
        //add our own option page, you can also add it to different sections or use your own one
        $this->pagehook = add_options_page('Howto Metabox Page Title', "HowTo Metaboxes", 'manage_options', HOWTO_METABOX_ADMIN_PAGE_NAME, array(&$this, 'on_show_page'));
        //register  callback gets call prior your own page gets rendered
        add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
    }


    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }


	
    //will be executed if wordpress core detects this page has to be rendered
    function on_load_page() {
        //ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        //add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
        add_meta_box('howto-metaboxes-contentbox-1', 'Contentbox 1 Title', array(&$this, 'on_contentbox_1_content'), $this->pagehook, 'normal', 'core');
    }
	
    //executed to show the plugins complete admin page
    function on_show_page() {
 
        //we need the global screen column value to beable to have a sidebar in WordPress 2.8
        global $screen_layout_columns;
        //add a 3rd content box now for demonstration purpose, boxes added at start of page rendering can't be switched on/off, 
        //may be needed to ensure that a special box is always available
        //define some data can be given to each metabox during rendering
        $data = array('My Data 1', 'My Data 2', 'Available Data 1');
        ?>
        <div id="howto-metaboxes-general" class="wrap">
        <?php screen_icon('options-general'); ?>
        <h2>Metabox Showcase Plugin Page</h2>
        <form action="admin-post.php" method="post">
            <?php wp_nonce_field('howto-metaboxes-general'); ?>
            <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
            <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
            <input type="hidden" name="action" value="save_howto_metaboxes_general" />
		
            <div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
                <div id="side-info-column" class="inner-sidebar">
                    <?php do_meta_boxes($this->pagehook, 'side', $data); ?>
                </div>
                <div id="post-body" class="has-sidebar">
                    <div id="post-body-content" class="has-sidebar-content">
                        <?php do_meta_boxes($this->pagehook, 'normal', $data); ?>

                        <p>
                            <input id="submit" type="submit" value="Save Changes" class="button-primary" name="Submit"/>	
                        </p>
                    </div>
                </div>
                <br class="clear"/>
								
            </div>	
        </form>
        </div>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($) {
            // close postboxes that should be closed
            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            // postboxes setup
            postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
        });
        //]]>
    </script>
		
        <?php
    }

    //executed if the post arrives initiated by pressing the submit button of form
    function on_save_changes() {

        $cmb = cmb2_get_metabox( 'my_metabox', 'tralala' );
        if ( $cmb ) {

            $hookup = new CMB2_hookup( $cmb );

            if ( $hookup->can_save( 'options-page' ) ) {
                $cmb->save_fields( 'my_metabox', 'options-page', $_POST );
            }
        }        
        //user permission check
        if ( !current_user_can('manage_options') )
            wp_die( __('Cheatin&#8217; uh?') );			
        //cross check the given referer
        check_admin_referer('howto-metaboxes-general');

        //process here your on $_POST validation and / or option saving
		
        //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
        wp_redirect($_POST['_wp_http_referer']);	



		
	
    }

    //below you will find for each registered metabox the callback method, that produces the content inside the boxes
    //i did not describe each callback dedicated, what they do can be easily inspected and compare with the admin page displayed
	

function on_contentbox_1_content($data) {

    CMB2_hookup::enqueue_cmb_css();

    cmb2_get_metabox( 'my_metabox', 'tralala', 'options-page')->show_form();

}

	
}

$my_howto_metabox_plugin = new howto_metabox_plugin();

/* function mega() {
    return howto_metabox_plugin::get_instance();
}
mega(); */



add_action( 'cmb2_admin_init', 'yourprefix_register_demo_metabox' );
/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function yourprefix_register_demo_metabox() {
    /**
     * Sample metabox to demonstrate each field type included
     */
    $cmb_demo = new_cmb2_box( array(
        'id'            => 'my_metabox',
        'title'         => esc_html__( 'Settings 1', 'cmb2' ),
        'hookup'       => false,
        'object_types' => array( 'options-page' ),

    ) );

    $cmb_demo->add_field( array(
        'name'       => esc_html__( 'Test Text', 'cmb2' ),
        'desc'       => esc_html__( 'field description (optional)', 'cmb2' ),
        'id'         => 'my_metabox_text',
        'type'       => 'text',
        'show_on_cb' => 'yourprefix_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        // 'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
        // 'column'          => true, // Display field value in the admin post-listing columns
    ) );



}














// class Prefix_Add_CMB2_To_Settings_Page {

//     /**
//      * Option key, and option page slug
//      * @var string
//      */
//     protected $key = 'myprefix_settings';

//     /**
//      * Settings page metabox id
//      * @var string
//      */
//     protected $metabox_id = 'myprefix_setting_metabox';

//     /**
//      * Settings page screen id where metabox should show.
//      * @var string
//      */
//     protected $screen_id = 'settings_page_howto_metaboxes';

//     /**
//      * Holds an instance of the project
//      *
//      * @Prefix_Add_CMB2_To_Settings_Page
//      **/
//     protected static $instance = null;

//     /**
//      * Constructor
//      * @since 0.1.0
//      */
//     protected function __construct() {}

//     /**
//      * Get the running object
//      *
//      * @return Prefix_Add_CMB2_To_Settings_Page
//      **/
//     public static function get_instance() {
//         if( is_null( self::$instance ) ) {
//             self::$instance = new self();
//             self::$instance->hooks();
//         }
//         return self::$instance;
//     }

//     /**
//      * Initiate our hooks
//      * @since 0.1.0
//      */
//     public function hooks() {
//         add_action( 'cmb2_admin_init', array( $this, 'register_metabox' ) );
//         add_action( 'current_screen', array( $this, 'maybe_save' ) );
//         add_filter( 'admin_footer' , array( $this , 'maybe_hookup_fields' ), 2 /* Early before all scripts are output. */ );
//     }

//     /**
//      * Add the options metabox to the array of metaboxes
//      * @since  0.1.0
//      */
//     function register_metabox() {
//         $cmb = new_cmb2_box( array(
//             'id'           => $this->metabox_id,
//             'hookup'       => false,
//             'object_types' => array( 'options-page' ),
//         ) );

//         // Set our CMB2 fields

//         $cmb->add_field( array(
//             'name' => __( 'Test Text', 'myprefix' ),
//             'desc' => __( 'field description (optional)', 'myprefix' ),
//             'id'   => 'test_text',
//             'type' => 'text',
//             // 'default' => 'Default Text',
//         ) );

//         $cmb->add_field( array(
//             'name'    => __( 'Test Color Picker', 'myprefix' ),
//             'desc'    => __( 'field description (optional)', 'myprefix' ),
//             'id'      => 'test_colorpicker',
//             'type'    => 'colorpicker',
//             'default' => '#bada55',
//         ) );

//     }

//     /**
//      * Register our setting to WP
//      * @since  0.1.0
//      */
//     public function maybe_save() {
//         if ( empty( $_POST ) ) {
//             return;
//         }

//         $url = wp_get_referer();
//         // Check if our screen id is in the referrer url.
//         if ( false === strpos( $url, $this->screen_id ) ) {
//             return;
//         }

//         // Hook into whitelist_options as we know it's only called if the default save-checks have finished.
//         add_filter( 'whitelist_options', array( $this, 'save_our_options' ) );
//     }

//     /**
//      * Simply used as a options.php life-cycle hook to save our settings
//      * (since there doesn't appear to be any proper hooks)
//      *
//      * @since  0.1.0
//      *
//      * @param  array  $whitelist_options
//      *
//      * @return array
//      */
//     public function save_our_options( $whitelist_options ) {
//         $cmb = cmb2_get_metabox( $this->metabox_id, $this->key );
//         if ( $cmb ) {

//             $hookup = new CMB2_hookup( $cmb );

//             if ( $hookup->can_save( 'options-page' ) ) {
//                 $cmb->save_fields( $this->key, 'options-page', $_POST );
//             }
//         }

//         // Our saving is done, so cleanup.
//         remove_filter( 'whitelist_options', array( $this, 'save_our_options' ) );

//         return $whitelist_options;
//     }

//     /**
//      * Maybe hookup our CMB2 fields.
//      *
//      * @since 0.1.0
//      */
//     public function maybe_hookup_fields() {
//         $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : (object) array( 'id' => null );

//         // Only show on our screen.
//         if ( $this->screen_id !== $screen->id ) {
//             return;
//         }

//         CMB2_hookup::enqueue_cmb_css();
//         $this->admin_page_display();
//     }

//     /**
//      * CMB2 fields output
//      * Wile hide by default in the footer, then use JS to move it inside the form. Hacky, yep.
//      *
//      * @since  0.1.0
//      */
//     public function admin_page_display() {

//     }

//     /**
//      * Public getter method for retrieving protected/private variables
//      * @since  0.1.0
//      * @param  string  $field Field to retrieve
//      * @return mixed          Field value or exception is thrown
//      */
//     public function __get( $field ) {
//         // Allowed fields to retrieve
//         if ( in_array( $field, array( 'key', 'metabox_id', 'screen_id' ), true ) ) {
//             return $this->{$field};
//         }

//         throw new Exception( 'Invalid property: ' . $field );
//     }

// }

// /**
//  * Helper function to get/return the Prefix_Add_CMB2_To_Settings_Page object
//  * @since  0.1.0
//  * @return Prefix_Add_CMB2_To_Settings_Page object
//  */
// function myprefix_cmb2_on_settings() {
//     return Prefix_Add_CMB2_To_Settings_Page::get_instance();
// }


//    myprefix_cmb2_on_settings();
// /**
//  * Wrapper function around cmb2_get_option
//  * @since  0.1.0
//  * @param  string $key     Options array key
//  * @param  mixed  $default Optional default value
//  * @return mixed           Option value
//  */
// function myprefix_get_option( $key = '', $default = false ) {
//     if ( function_exists( 'cmb2_get_option' ) ) {
//         // Use cmb2_get_option as it passes through some key filters.
//         return cmb2_get_option( myprefix_cmb2_on_settings()->key, $key, $default );
//     }
//     // Fallback to get_option if CMB2 is not loaded yet.
//     $opts = get_option( myprefix_cmb2_on_settings()->key, $default );
//     $val = $default;
//     if ( 'all' == $key ) {
//         $val = $opts;
//     } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
//         $val = $opts[ $key ];
//     }
//     return $val;
// }



// // =======================================================================================
// // Now I will execute function above, which should add CMB2 to ustawienia_motywu page
// // =======================================================================================








class Prefix_Add_CMB2_To_Settings_Page {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'myprefix_settings';

    /**
     * Settings page metabox id
     * @var string
     */
    protected $metabox_id = 'myprefix_setting_metabox';

    /**
     * Settings page screen id where metabox should show.
     * @var string
     */
    protected $screen_id = 'options-general';

    /**
     * Holds an instance of the project
     *
     * @Prefix_Add_CMB2_To_Settings_Page
     **/
    protected static $instance = null;

    /**
     * Constructor
     * @since 0.1.0
     */
    protected function __construct() {}

    /**
     * Get the running object
     *
     * @return Prefix_Add_CMB2_To_Settings_Page
     **/
    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks() {
        add_action( 'cmb2_admin_init', array( $this, 'register_metabox' ) );
        add_action( 'current_screen', array( $this, 'maybe_save' ) );
        add_filter( 'admin_footer' , array( $this , 'maybe_hookup_fields' ), 2 /* Early before all scripts are output. */ );
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    function register_metabox() {
        $cmb = new_cmb2_box( array(
            'id'           => $this->metabox_id,
            'hookup'       => false,
            'object_types' => array( 'options-page' ),
        ) );

        // Set our CMB2 fields

        $cmb->add_field( array(
            'name' => __( 'Test Text', 'myprefix' ),
            'desc' => __( 'field description (optional)', 'myprefix' ),
            'id'   => 'test_text',
            'type' => 'text',
            // 'default' => 'Default Text',
        ) );

        $cmb->add_field( array(
            'name'    => __( 'Test Color Picker', 'myprefix' ),
            'desc'    => __( 'field description (optional)', 'myprefix' ),
            'id'      => 'test_colorpicker',
            'type'    => 'colorpicker',
            'default' => '#bada55',
        ) );

    }

    /**
     * Register our setting to WP
     * @since  0.1.0
     */
    public function maybe_save() {
        if ( empty( $_POST ) ) {
            return;
        }

        $url = wp_get_referer();
        // Check if our screen id is in the referrer url.
        if ( false === strpos( $url, $this->screen_id ) ) {
            return;
        }

        // Hook into whitelist_options as we know it's only called if the default save-checks have finished.
        add_filter( 'whitelist_options', array( $this, 'save_our_options' ) );
    }

    /**
     * Simply used as a options.php life-cycle hook to save our settings
     * (since there doesn't appear to be any proper hooks)
     *
     * @since  0.1.0
     *
     * @param  array  $whitelist_options
     *
     * @return array
     */
    public function save_our_options( $whitelist_options ) {
        $cmb = cmb2_get_metabox( $this->metabox_id, $this->key );
        if ( $cmb ) {

            $hookup = new CMB2_hookup( $cmb );

            if ( $hookup->can_save( 'options-page' ) ) {
                $cmb->save_fields( $this->key, 'options-page', $_POST );
            }
        }

        // Our saving is done, so cleanup.
        remove_filter( 'whitelist_options', array( $this, 'save_our_options' ) );

        return $whitelist_options;
    }

    /**
     * Maybe hookup our CMB2 fields.
     *
     * @since 0.1.0
     */
    public function maybe_hookup_fields() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : (object) array( 'id' => null );

        // Only show on our screen.
        if ( $this->screen_id !== $screen->id ) {
            return;
        }

        CMB2_hookup::enqueue_cmb_css();
        $this->admin_page_display();
    }

    /**
     * CMB2 fields output
     * Wile hide by default in the footer, then use JS to move it inside the form. Hacky, yep.
     *
     * @since  0.1.0
     */
    public function admin_page_display() {
        ?>
        <div id="cmb2-options-page-<?php echo $this->key; ?>" class="wrap cmb2-options-page <?php echo $this->key; ?>" style="display:none">
            <?php cmb2_get_metabox( $this->metabox_id, $this->key, 'options-page' )->show_form(); ?>
        </div>
        <script type="text/javascript">
            var cmb2 = document.getElementById( 'cmb2-options-page-<?php echo $this->key; ?>' );
            var submit = document.getElementById( 'submit' ).parentNode;
            submit.parentNode.insertBefore( cmb2, submit );
            cmb2.style.display = '';
        </script>
        <?php
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string  $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
/*     public function __get( $field ) {
        // Allowed fields to retrieve
        if ( in_array( $field, array( 'key', 'metabox_id', 'screen_id' ), true ) ) {
            return $this->{$field};
        }

        throw new Exception( 'Invalid property: ' . $field );
    } */

}

/**
 * Helper function to get/return the Prefix_Add_CMB2_To_Settings_Page object
 * @since  0.1.0
 * @return Prefix_Add_CMB2_To_Settings_Page object
 */
function myprefix_cmb2_on_settings() {
    return Prefix_Add_CMB2_To_Settings_Page::get_instance();
}


/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function myprefix_get_option( $key = '', $default = false ) {
    if ( function_exists( 'cmb2_get_option' ) ) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option( myprefix_cmb2_on_settings()->key, $key, $default );
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option( myprefix_cmb2_on_settings()->key, $default );
    $val = $default;
    if ( 'all' == $key ) {
        $val = $opts;
    } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
        $val = $opts[ $key ];
    }
    return $val;
}

// Get it started
myprefix_cmb2_on_settings();