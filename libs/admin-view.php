<?php



//avoid direct calls to this file where wp core files not present
if (!function_exists ('add_action')) {
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
}

define('HOWTO_METABOX_ADMIN_PAGE_NAME', 'howto_metaboxes');

//class that reperesent the complete plugin
class PD_Theme_Settings {



    //constructor of class, PHP4 compatible construction for backward compatibility
    function __construct() {
        //add filter for WordPress 2.8 changed backend box system !
        add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
        add_action('cmb2_admin_init', 'cmb2_conditionals_init');
        //register callback for admin menu  setup
        add_action('admin_menu', array(&$this, 'on_admin_menu')); 
        //register the callback been used if options of page been submitted and needs to be processed
        add_action('admin_post_save_howto_metaboxes_general', array(&$this, 'on_save_changes'));
        add_action('cmb2_init', 'yourprefix_register_demo_metabox');


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
        $this->pagehook = add_options_page('Howto Metabox Page Title', "_Ustawienia_Motywu_", 'manage_options', HOWTO_METABOX_ADMIN_PAGE_NAME, array(&$this, 'on_show_page'), 999);

        //register  callback gets call prior your own page gets rendered
        add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
        settings_errors();
    }




	
    //will be executed if wordpress core detects this page has to be rendered
    function on_load_page() {
        //ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        //add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
        add_meta_box('scripts', 'Jakie skrypty mają się ładować na stronie?', array(&$this, 'on_contentbox_1_content'), $this->pagehook, 'normal', 'core');
        add_meta_box('breadcrumbs', 'Czy na stronie ma być Breadcrumbs ?', array(&$this, 'on_contentbox_2_content'), $this->pagehook, 'normal', 'core');
        add_meta_box('custom_post_types', 'Customowe Typy Postów (CPT)', array(&$this, 'on_contentbox_3_content'), $this->pagehook, 'normal', 'core');
        add_meta_box('conditional', 'test', array(&$this, 'on_contentbox_4_content'), $this->pagehook, 'normal', 'core');
    }

	
    //executed to show the plugins complete admin page
    function on_show_page() {
 
        //we need the global screen column value to beable to have a sidebar in WordPress 2.8
        global $screen_layout_columns;
        //add a 3rd content box now for demonstration purpose, boxes added at start of page rendering can't be switched on/off, 
        //may be needed to ensure that a special box is always available
        //define some data can be given to each metabox during rendering
        $data = '';
        ?>
        <div id="howto-metaboxes-general" class="wrap">
        <?php screen_icon('options-general'); ?>
        <h2>Ustawienia Motywu</h2>
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
                            <input id="submit" type="submit" value="Zapisz ustawienia motywu" class="button-primary" name="Submit"/>	
                        </p>
                    </div>
                </div>
                <br class="clear"/>
                <?php require_once ABSPATH . 'wp-admin/admin-footer.php'; ?>
            </div>	
        </form>
            <?php settings_errors(); ?>
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

        $cmb = cmb2_get_metabox( 'scripts');
        if ( $cmb ) {

            $hookup = new CMB2_hookup( $cmb );

            if ( $hookup->can_save( 'options-page' ) ) {
                $cmb->save_fields( 'scripts', 'options-page', $_POST );
            }
        } 

        $cmb2 = cmb2_get_metabox( 'cpt');
        if ( $cmb2 ) {

            $hookup = new CMB2_hookup( $cmb2 );

            if ( $hookup->can_save( 'options-page' ) ) {
                $cmb2->save_fields( 'cpt', 'options-page', $_POST );
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

    cmb2_get_metabox( 'scripts', 'scripts', 'options-page' )->show_form();

}    

function on_contentbox_2_content($data) {

    CMB2_hookup::enqueue_cmb_css();

    cmb2_get_metabox( 'breadcrumbs', 'breadcrumbs', 'options-page' )->show_form();

}

function on_contentbox_3_content($data) {

    CMB2_hookup::enqueue_cmb_css();

    cmb2_get_metabox( 'cpt', 'cpt', 'options-page' )->show_form();

}


    function on_contentbox_4_content($data) {

        CMB2_hookup::enqueue_cmb_css();

        cmb2_get_metabox( 'yourprefix_conditions_demo_metabox', 'yourprefix_conditions_demo_metabox', 'options-page' )->show_form();

    }




	
}

$pd_theme_settings = new PD_Theme_Settings();

/* function mega() {
    return ustawienia_motywu::get_instance();
}
mega(); */




/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function yourprefix_register_demo_metabox() {
    /**
     * Sample metabox to demonstrate each field type included
     */





    $scripts = new_cmb2_box( array(
        'id'            => 'scripts',  // Belgrove Bouncing Castles
        'title'         => esc_html__( 'Scripts', 'cmb2' ),
        'object_types' => array( 'options-page' ),
        'hookup'       => true,
    ) );


    // Regular text field
    $scripts->add_field( array(
        'name'       => __( 'Font Awesome', 'cmb2' ),
        'id'         => 'scripts_font_awesome',
        'type'       => 'checkbox'

    ) );

    // Regular text field
    $scripts->add_field( array(
        'name'       => __( 'Owl Carousel', 'cmb2' ),
        'id'         => 'scripts_owl_carousel',
        'type'       => 'checkbox'

    ) );

    // Regular text field
    $scripts->add_field( array(
        'name'       => __( 'Parallax', 'cmb2' ),
        'id'         => 'scripts_parallax',
        'type'       => 'checkbox'

    ) );

    // Tytuł
    $scripts->add_field( array(
        'name' => __( 'Vanilla LazyLoad', 'cmb2' ),
        'id'   => 'scripts_vanilla_lazyload',
        'type' => 'checkbox',
    ) );





    $cpt = new_cmb2_box( array(
        'id'            => 'cpt',  // Belgrove Bouncing Castles
        'title'         => esc_html__( 'Custom Post Types', 'cmb2' ),
        'object_types' => array( 'options-page' ),
        'hookup'       => true,
        'object_types' => array( 'options-page' ),
    ) );


    $cpt_group = $cpt->add_field( array(
        'id'          => 'cpt_group',
        'name'        => 'Lista CPT',
        'type'        => 'group',
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => 'Custom Post Type {#}',
            'add_button'    => 'Dodaj Kolejy CPT',
            'remove_button' => 'Usuń',
            'closed'        => true,  // Repeater fields closed by default - neat & compact.
            'sortable'      => true,  // Allow changing the order of repeated groups.
        ),
    ) );

    $cpt->add_group_field( $cpt_group, array(
    'name'       => __( 'post_type_name', 'cmb2' ),
    'id'         => 'cpt_post_type_name',
    'type'       => 'text'
    ) );    



    $cpt->add_group_field( $cpt_group, array(
        'name'       => __( 'name', 'cmb2' ),
        'id'         => 'cpt_name',
        'type'       => 'text'

    ) );

    $cpt->add_group_field( $cpt_group, array(
        'name'       => __( 'singular_name', 'cmb2' ),
        'id'         => 'cpt_singular_name',
        'type'       => 'text'

    ) );


    $cpt->add_group_field( $cpt_group, array(
    'name'       => __( 'add_new', 'cmb2' ),
    'id'         => 'cpt_add_new',
    'type'       => 'text'

    ) );


    $cpt->add_group_field( $cpt_group, array(
        'name' => __( 'add_new_item', 'cmb2' ),
        'id'   => 'cpt_add_new_item',
        'type' => 'text',
    ) );


    $cpt->add_group_field( $cpt_group, array(
        'name' => __( 'label', 'cmb2' ),
        'id'   => 'cpt_label',
        'type' => 'text',
    ) );

    $cpt->add_group_field( $cpt_group, array(
        'name' => __( 'archive_slug', 'cmb2' ),
        'id'   => 'cpt_archive_slug',
        'type' => 'text',
    ) );

    $cpt->add_group_field( $cpt_group, array(
        'name' => __( 'menu_icon', 'cmb2' ),
        'id'   => 'cpt_menu_icon',
        'type' => 'text',
    ) );



    // breadcrumbs 

    $breadcrumbs = new_cmb2_box( array(
        'id'            => 'breadcrumbs',  // Belgrove Bouncing Castles
        'title'         => esc_html__( 'Breadcrumbs', 'cmb2' ),
        'object_types' => array( 'options-page' ),
        'hookup'       => true,
        'object_types' => array( 'options-page' ),
    ) );


    // Regular text field
    $breadcrumbs->add_field( array(
        'name'       => __( 'Breadcrumbs', 'cmb2' ),
        'id'         => 'breadcrumbs_check',
        'type'       => 'checkbox'
    ) );




}




























add_action( 'cmb2_init', 'yourprefix_register_conditionals_demo_metabox' );


function yourprefix_register_conditionals_demo_metabox() {

    // Start with an underscore to hide fields from custom fields list.
    $prefix = 'yourprefix_conditions_demo_';

    /**
     * Sample metabox to demonstrate the different conditions you can set.
     */
    $cmb_demo = new_cmb2_box( array(
        'id'            => $prefix . 'metabox',
        'title'         => 'Test Metabox',
        'object_types' => array( 'options-page' ),
        'hookup'       => true,
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Address',
        'desc'       => 'Write down an address for showing the other address options',
        'id'         => $prefix . 'address',
        'type'       => 'text',
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Zipcode',
        'id'         => $prefix . 'zipcode',
        'type'       => 'text_medium',
        'attributes' => array(
            'required'            => true, // Will be required only if visible.
            'data-conditional-id' => $prefix . 'address',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Country',
        'id'         => $prefix . 'country',
        'type'       => 'text_medium',
        'attributes' => array(
            'required'            => true, // Will be required only if visible.
            'data-conditional-id' => $prefix . 'address',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name' => 'Checkbox',
        'id'   => $prefix . 'checkbox',
        'type' => 'checkbox',
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Show if checked',
        'id'         => $prefix . 'show_if_checked',
        'type'       => 'text',
        'attributes' => array(
            'data-conditional-id' => $prefix . 'checkbox',
            // Works too: 'data-conditional-value' => 'on'.
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Show if unchecked',
        'id'         => $prefix . 'show_if_unchecked',
        'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'    => $prefix . 'checkbox',
            'data-conditional-value' => 'off',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'             => 'Reason',
        'id'               => $prefix . 'reason',
        'type'             => 'select',
        'show_option_none' => true,
        'options'          => array(
            'one'   => 'Reason 1',
            'two'   => 'Reason 2',
            'three' => 'Reason 3',
            'other' => 'Other reason',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Other reason detail',
        'desc'       => 'Write down the reason',
        'id'         => $prefix . 'other_reason_detail',
        'type'       => 'textarea',
        'attributes' => array(
            'required'               => true, // Will be required only if visible.
            'data-conditional-id'    => $prefix . 'reason',
            'data-conditional-value' => 'other',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'             => 'Reason 2',
        'id'               => $prefix . 'reason_2',
        'type'             => 'select',
        'show_option_none' => true,
        'options'          => array(
            'one'            => 'Reason 1',
            'two'            => 'Reason 2',
            'three'          => 'Reason 3',
            'other_price'    => 'Other reason based on the price',
            'other_quality'  => 'Other reason based on the quality',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Other reason detail',
        'desc'       => 'Write down the reason',
        'id'         => $prefix . 'other_reason_detail_2',
        'type'       => 'textarea',
        'attributes' => array(
            'required'               => true, // Will be required only if visible.
            'data-conditional-id'    => $prefix . 'reason_2',
            'data-conditional-value' => wp_json_encode( array( 'other_price', 'other_quality' ) ),
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'             => 'Sizes',
        'id'               => $prefix . 'sizes',
        'type'             => 'radio',
        'show_option_none' => true,
        'options'          => array(
            'xs'     => 'XS',
            's'      => 'S',
            'm'      => 'M',
            'l'      => 'L',
            'xl'     => 'XL',
            'custom' => 'Custom',
        ),
        'attributes'       => array(
            'required'       => 'required',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Custom description',
        'desc'       => 'Write a description for your custom size',
        'id'         => $prefix . 'size_custom_description',
        'type'       => 'textarea',
        'required'   => true,
        'attributes' => array(
            'required'               => true, // Will be required only if visible.
            'data-conditional-id'    => $prefix . 'sizes',
            'data-conditional-value' => 'custom',
        ),
    ) );

    // Example using conditionals with multi-check checkboxes.
    $cmb_demo->add_field( array(
        'name'    => __( 'Test Multi Checkbox', 'cmb2' ),
        'desc'    => __( 'field description (optional)', 'cmb2' ),
        'id'      => $prefix . 'multi-checkbox',
        'type'    => 'multicheck',
        'options' => array(
            'check1' => __( 'Check One', 'cmb2' ),
            'check2' => __( 'Check Two', 'cmb2' ),
            'check3' => __( 'Check Three', 'cmb2' ),
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Multi-check: Shown if *any* checkbox is checked',
        'id'         => $prefix . 'multi-check-detail-test-no-value',
        'type'       => 'text',
        'attributes' => array(
            'required'            => true, // Will be required only if visible.
            'data-conditional-id' => $prefix . 'multi-checkbox',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Multi-check: Only shown if checkbox 2 is checked',
        'id'         => $prefix . 'multi-check-detail-test-string',
        'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'    => $prefix . 'multi-checkbox',
            'data-conditional-value' => 'check2',
        ),
    ) );

    $cmb_demo->add_field( array(
        'name'       => 'Multi-check : Shown if either checkbox 1 *or* 3 is checked',
        'id'         => $prefix . 'multi-check-detail-test-array',
        'type'       => 'text',
        'attributes' => array(
            'data-conditional-id'    => $prefix . 'multi-checkbox',
            'data-conditional-value' => wp_json_encode( array( 'check1', 'check3' ) ),
        ),
    ) );

    // Example conditionals within a group.
    $group_id = $cmb_demo->add_field( array(
        'id'          => $prefix . 'repeatable-group',
        'type'        => 'group',
        'description' => 'Repeatable group',
        'options'     => array(
            'group_title'   => 'Entry {#}', // Since version 1.1.4, {#} gets replaced by row number.
            'add_button'    => 'Add Another Entry',
            'remove_button' => 'Remove Entry',
            'sortable'      => true, // Beta.
        ),
    ) );

    $cmb_demo->add_group_field( $group_id, array(
        'name' => 'Checkbox in group',
        'id'   => 'checkbox',
        'type' => 'checkbox',
    ) );

    $cmb_demo->add_group_field( $group_id, array(
        'name'       => 'Dependant field',
        'id'         => 'dependant',
        'type'       => 'text_small',
        'attributes' => array(
            'required'               => true, // Will be required only if visible.
            'data-conditional-id'    => wp_json_encode( array( $group_id, 'checkbox' ) ),
            'data-conditional-value' => 'on',
        ),
    ) );
}

?>