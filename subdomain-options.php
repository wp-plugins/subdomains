<?php

class PA_Subdomain_Options
{

    protected $options;

    public function __construct()
    {
        add_action( 'admin_menu', array( &$this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( &$this, 'page_init' ) );
        
        $this->options = get_option( 'subdomain_api_option' );
    }
    
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Subdomain Settings', 
            'manage_options', 
            'subdomain-admin', 
            array( &$this, 'create_admin_page' )
        );
    }
    
    public function create_admin_page()
    { ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Subdomain Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'subdomain_option_group' );   
                do_settings_sections( 'subdomain-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'subdomain_option_group', // Option group
            'subdomain_api_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'subdomain_setting_section', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'subdomain-admin' // Page
        );  

        add_settings_field(
            'subdomain_cat', // ID
            'Select Categories', // Title 
            array( $this, 'subdomain_cat_callback' ), // Callback
            'subdomain-admin', // Page
            'subdomain_setting_section' // Section           
        ); 
    }

    public function sanitize( $input )
    {
        $input['subdomain_cat'] = $_REQUEST['post_category'];
        return $input;
    }

    public function print_section_info()
    {
        print 'Please select one or more categories:';
    }

    public function subdomain_cat_callback()
    {
        $selected = $this->options;
        $args = array('selected_cats' => $selected['subdomain_cat'],'checked_ontop'  => false );
        wp_terms_checklist( 0, $args);
        
    }
}
