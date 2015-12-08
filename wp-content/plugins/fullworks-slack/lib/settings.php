<?php
/*
set up the plugin settings page
- Slack team
- API Token
*/
class FullworksSlackSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    // private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __('Settings Admin','fullworks-slack'),
            __('Fullworks Slack','fullworks-slack'),
            'manage_options',
            'fullworks-slack-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'fullworks-slack_options',
              array(
                'slack_team' => '',
                'API_token' => '',
                )
                );

        print_r($this->options);

        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e("Fullworks Slack","fullworks-slack"); ?></h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'fullworks_slack_options_group' );
                do_settings_sections( 'fullworks-slack-settings-admin' );
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
            'fullworks_slack_options_group', // Option group
            'fullworks-slack_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_1', // ID
            __('General Settings'), // Title
            array( $this, 'print_section_info' ), // Callback
            'fullworks-slack-settings-admin' // Page
        );

        add_settings_field(
            'slack_team', // ID
            __('Slack Team Name','fullworks-slack'), // Title
            array( $this, 'slack_team_callback' ), // Callback
            'fullworks-slack-settings-admin', // Page
            'setting_section_1' // Section
        );
        add_settings_field(
            'API_token', // ID
            __('Slack API Token','fullworks-slack'), // Title
            array( $this, 'API_token_callback' ), // Callback
            'fullworks-slack-settings-admin', // Page
            'setting_section_1' // Section
        );
        add_settings_section(
            'setting_section_2', // ID
            __('Join Slack -  Options'), // Title
            array( $this, 'print_section_info' ), // Callback
            'fullworks-slack-settings-admin' // Page
        );
        add_settings_field(
            'frontend_hide', // ID
            __('Hide when not logged in','fullworks-slack'), // Title
            array( $this, 'frontend_hide_callback' ), // Callback
            'fullworks-slack-settings-admin', // Page
            'setting_section_2' // Section
        );
        add_settings_field(
            'roles_show', // ID
            __('Show only to','fullworks-slack'), // Title
            array( $this, 'roles_show_callback' ), // Callback
            'fullworks-slack-settings-admin', // Page
            'setting_section_2' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
       $new_input = $input; // no validation yet

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print __('Enter your settings below:','fullworks-slack');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function slack_team_callback()
    {

        printf(
            '<span class="url">https://</span><input type="text" class="regular-text" id="slack_team" name="fullworks-slack_options[slack_team]" value="%s" /><span class="url">.slack.com</span>',
            isset( $this->options['slack_team'] ) ? esc_attr( $this->options['slack_team']) : ''
        );
        echo '<p class="description">'.__("Enter your Slack Team name","join-slack").'</p>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function API_token_callback()
    {

        printf(
            '<input type="text" class="regular-text" id="API_token" name="fullworks-slack_options[API_token]" value="%s" />',
            isset( $this->options['API_token'] ) ? esc_attr( $this->options['API_token']) : ''
        );
        echo '<p class="description">'.__("Enter Slack Web API Token","fullworks-slack").'</p>';
        echo '<p class="description">'.__("Visit <a target='blank' href='https://api.slack.com/web'>https://api.slack.com/web</a> and create a token","fullworks-slack").'</p>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function frontend_hide_callback()
    {

        echo '<input name="fullworks-slack_options[frontend_hide]" type="checkbox" value="1" '. checked( '1', $this->options['frontend_hide'] , false) . ' />';
        echo '<p class="description">'.__("Tick this box to hide all output unless in the role(s) selected below","fullworks-slack").'</p>';

    }

    public function roles_show_callback()
    {
      global $wp_roles;
      $setroles = isset($this->options['roles_show'])? $this->options['roles_show']: array();?>

      <select multiple name="fullworks-slack_options[roles_show][]">
        <?php foreach ( $wp_roles->roles as $key=>$value ): ?>
          <option value="<?php echo $key; ?>" <?php echo (in_array($key,$setroles) )? 'selected' : '';?> ><?php echo $value['name']; ?></option>
        <?php endforeach; ?>
      </select>
      <?php
        echo '<p class="description">'.__("Select all roles you want to be able to use this feature (multi-select), none selected will make it available to non logged in users","fullworks-slack").'</p>';

    }



}



 ?>
