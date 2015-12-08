<?php
/*
set up the sign-up widget
*/
add_action( 'widgets_init', function(){
    register_widget( 'JoinSlack_Widget' );
  });
class JoinSlack_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 0.1.0
	 */
	function __construct() {
		$this->defaults = array(
			'title'            => '',
			'text'             => '',
			'after_text'       => '',
      'button_text'      => '',
      'image'            => plugin_dir_url(__FILE__) . 'images/slack_rgb-300x88.png'
		);

		$widget_ops = array(
			'classname'   => 'joinslack-widget',
			'description' => __( 'Displays Slack Join form', 'fullworks-slack' ),
		);

		parent::__construct( 'join-slack', __( 'Join Slack', 'fullworks-slack' ), $widget_ops );

    add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
	}

  /**
     * Upload the Javascripts for the media uploader
     */
    public function upload_scripts()
    {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('upload_media_widget', plugin_dir_url(__FILE__) . 'js/upload-media.js', array('jquery'));

        wp_enqueue_style('thickbox');
    }

	/**
	 * Echo the widget content.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

    // Start building the output.
    $output = '';
    $output = $this->render( $args, $instance );
	  echo $output;

	}

  /**
	 * Generate the widget output.
	 *
	 * This is typically done in the widget() method, but moving it to a
	 * separate method allows for the routine to be easily overloaded by a class
	 * extending this one without having to reimplement all the caching and
	 * filtering, or resorting to adding a filter, calling the parent method,
	 * then removing the filter.
	 *
	 * @since 3.0.0
	 *
	 * @param array   $args     Registered sidebar arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array   $instance The widget instance settings.
	 * @return string HTML output.
	 */
	public function render( $args, $instance ) {



		/**
		 * Widget HTML output.
		 *
		 * @since 3.0.0
		 *
		 * @param string $output   Widget output.
		 * @param array  $args     Registered sidebar arguments including before_title, after_title, before_widget, and after_widget.
		 * @param array  $instance The widget instance settings.
		 * @param string $id_base  Widget type id.
		 */

     // check if hiding for not logged in
     $options = get_option( 'fullworks-slack_options',
           array(
             'slack_team' => '',
             'API_token' => '',
             'frontend_hide' => '',
             )
             );
      $output = '';

  $user = wp_get_current_user();
  $setroles = isset($options['roles_show'])? $options['roles_show']: array();
  $validrole = false;
  if (empty($setroles) || (count(array_intersect($setroles, (array) $user->roles)) > 0 ) )
    $validrole = true;

   if ( ('1' != $options['frontend_hide']) || ( is_user_logged_in()) )  {
     if (( $validrole) || ( !is_user_logged_in())) {


   $output .= $args['before_widget'] . '<div class="join-slack">';
   if ( ! empty( $instance['title'] ) )
 			  $output .= $args['before_title'] . $instance['title'] . $args['after_title'];


      $output .= '<div class"join-slack-logo"><img src="'. esc_url($instance['image']).'" alt="'. __('Slack Logo','fullworks-slack'). '" ></div>';

    $output .= wpautop( $instance['text'] ); // We run KSES on update

    $slack_invited = false;
    if(!empty($_POST['slackemail'])) {

      $data = array(
        'email' => $_POST['slackemail'],
        'channels' => '',
        'first_name' => '',
        'token' => $options['API_token'],
        'set_active' => 'true',
        '_attempts' => '1',
      );
      $slack_method = esc_url( 'https://' . $options['slack_team'] .'.slack.com/api/users.admin.invite?t=1' );
      $response = wp_remote_post( $slack_method, array( 'body' => $data ) );

      if( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
  			$return = json_decode( wp_remote_retrieve_body( $response ) );
  			if ( $return->ok ) {
          $slack_invited = true;
          $output  .= '<div class="success">';
  				$output  .=  __( 'Invite Request Submitted, check your emails', 'fullworks-slack' );
          $output .= '</div>';
        } else {
  			if ( isset( $return->error ) ) {
          $output  .= '<div class="error">';
  				switch( $return->error ) {
  					case 'already_in_team' :
  						$output  .=  __( 'You are already in this team!', 'fullworks-slack' );
              $output .= '</div>';
  					break;
  					case 'already_invited' :
  					case 'sent_recently' :
  						$output  .=  __( 'You have already been invited to this team!', 'fullworks-slack' );
              $output .= '</div>';
  					break;
  					case 'invalid_auth' :
  						$output  .=  __( 'API Token incorrect!', 'fullworks-slack' );
              $output .= '</div>';
            break;
            case 'not_authed' :
  						$output  .=  __( 'No API Token set!', 'fullworks-slack' );
              $output .= '</div>';
            break;
            case 'domain_mismatch' :
              $output  .=  __( 'This email domain is not permitted for this team', 'fullworks-slack' );
              $output .= '</div>';
  					break;
            case 'invalid_email' :
              $output  .=  __( 'Email not valid', 'fullworks-slack' );
              $output .= '</div>';
  					break;
  					default:
  						$output  .=  sprintf( __( 'Error: %s', 'fullworks-slack' ), esc_html( $return->error ) );
              $output .= '</div>';
  					break;

  				}
  			}
      }
  		} else {
        $output  .= '<div class="error">';
  			$output  .= sprintf( __( 'Error: %s', 'fullworks-slack' ), wp_remote_retrieve_response_code( $response ) );
        $output .= '</div>';
  		}

      }
      if (!$slack_invited)
      if ( (!is_user_logged_in()) && (isset($options['roles_show']))) {
        $output .= '<a class="button" href="'. wp_login_url().'">'. __('Login','fullworks-slack').'</a>';
        if ( get_option( 'users_can_register' ) ) {
    // A message or code to show if the registration is allowed.
        $output .= '<a href="'. wp_registration_url().'">'. __('Register','fullworks-slack').'</a>';
      }
      } else
      {
        $output .= '<form id="join-slack-request"';
        if ( !current_theme_supports( 'html5' ) )
                $output .= 'action="#"';
        $output .= 'method="post"><label for="subbox" class="screenread">';
        $output .= esc_attr( $instance['input_text'] );
        $output .= '</label><input type="';
        $output .= current_theme_supports( 'html5' ) ? 'email' : 'text';
        $output .= '"  value="';
        $output .= esc_attr( $instance['input_text'] );
        $output .= '" id="slackemail" name="slackemail"';
        if ( current_theme_supports( 'html5' ) )
          $output .= 'placeholder="'. __('Your Email','fullworks-slack').'" ';
          $output .= 'required="required"';
        $output .= '/> <input type="submit" value="';
        $output .= esc_attr( $instance['button_text'] );
        $output .= '" id="subbutton" /></form>';
      }
  	$output .= wpautop( $instance['after_text'] ); // We run KSES on update
		$output .= '</div>' . $args['after_widget'];
  } //end check logged in and valid role
  } // end check if logged in

		return $output;
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If false is returned, the instance won't be saved / updated.
	 *
	 * @since 0.1.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title']         = strip_tags( $new_instance['title'] );
		$new_instance['text']          = wp_kses_post( $new_instance['text']);
		$new_instance['after_text']    = wp_kses_post( $new_instance['after_text']);
    $new_instance['button_text']         = strip_tags( $new_instance['button_text'] );
    $new_instance['image']         = esc_url( $new_instance['image'] );

		return $new_instance;
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 0.1.0
	 *
	 * @param array $instance Current settings.
	 */
	function form( $instance ) {
		// Merge with defaults
    if (empty($instance['image'])) unset($instance['image']);
		$instance = wp_parse_args( (array) $instance, $this->defaults );


		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'fullworks-slack' ); ?>:</label><br />
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
    <?php
      if(isset($instance['image']))
        {
            $image = $instance['image'];

            ?>
            <p>
            <img src="<?php echo esc_url( $image );?>" />
            </p>

            <?php
        }



     ?>
    <p>
            <label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Logo:' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="widefat" type="text" size="36"  value="<?php echo esc_url( $image ); ?>" />
            <input class="upload_image_button button button-primary" type="button" value="Upload Image" />
    </p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Text To Show Before Form', 'fullworks-slack' ); ?>:</label><br />
			<textarea id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" class="widefat" rows="6" cols="4"><?php echo htmlspecialchars( $instance['text'] ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>"><?php _e( 'Text To Show After Form', 'fullworks-slack' ); ?>:</label><br />
			<textarea id="<?php echo esc_attr( $this->get_field_id( 'after_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_text' ) ); ?>" class="widefat" rows="6" cols="4"><?php echo htmlspecialchars( $instance['after_text'] ); ?></textarea>
		</p>

		<p>
			<?php $button_text = empty( $instance['button_text'] ) ? __( 'Go', 'fullworks-slack' ) : $instance['button_text']; ?>
			<label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php _e( 'Button Text', 'fullworks-slack' ); ?>:</label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" value="<?php echo esc_attr( $button_text ); ?>" class="widefat" />
		</p>

	<?php
	}

}






?>
