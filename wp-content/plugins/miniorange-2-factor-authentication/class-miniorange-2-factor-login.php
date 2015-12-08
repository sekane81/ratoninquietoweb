<?Php
/** miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This library is miniOrange Authentication Service. 
Contains Request Calls to Customer service.

**/
include_once dirname( __FILE__ ) . '/miniorange_2_factor_common_login.php';
class Miniorange_Mobile_Login{

	public function my_login_redirect() {
		if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
			session_start();
		}
	
		if (isset($_POST['miniorange_login_nonce'])){			
			$nonce = $_POST['miniorange_login_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request';
				$this->mo_auth_show_error_message();
			} else {
				//validation and sanitization
				$username = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2fa_username'] ) ) {
					$_SESSION['mo2f-login-message'] = 'Please enter username to proceed';
					$this->mo_auth_show_error_message();
					return;
				} else{
					$username = sanitize_text_field( $_POST['mo2fa_username'] );
				}
				
				if ( username_exists( $username ) ){ /*if username exists in wp site */
					$user = new WP_User( $username );
					if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
						session_start();
					}
					$_SESSION[ 'mo2f_current_user' ] = $user;
					$roles = $user->roles;
					$current_role = array_shift($roles);
					if(get_option('mo2fa_'.$current_role)){
						if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
							session_start();
						}
						if(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true) && get_user_meta($user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){
								//if(MO2f_Utility::check_if_request_is_from_mobile_device($_SERVER['HTTP_USER_AGENT'])){
									//$this->mo2f_login_kba_verification($currentuser);
								//}else{
								$mo2f_second_factor = mo2f_get_user_2ndfactor($user);
								if($mo2f_second_factor == 'MOBILE AUTHENTICATION'){
									$this->mo2f_login_mobile_verification($user);
								}else if($mo2f_second_factor == 'PUSH NOTIFICATIONS' || $mo2f_second_factor == 'OUT OF BAND EMAIL'){
									$this->mo2f_login_push_oobemail_verification($user,$mo2f_second_factor);
								}else if($mo2f_second_factor == 'SOFT TOKEN' || $mo2f_second_factor == 'SMS' || $mo2f_second_factor == 'PHONE VERIFICATION' || $mo2f_second_factor == 'GOOGLE AUTHENTICATOR'){
									$this->mo2f_login_otp_verification($user,$mo2f_second_factor);
								}else if($mo2f_second_factor == 'KBA'){
									$this->mo2f_login_kba_verification($user);
								}else{
									$this->remove_current_activity();
									$_SESSION['mo2f-login-message'] = 'Please try again or contact your admin.';
									$this->mo_auth_show_success_message();
								}
							//}
						}else{
							$_SESSION['mo2f-login-message'] = 'Please login into your account using password.';
							$this->mo_auth_show_success_message();
							$this->mo2f_redirectto_wp_login();
						}
					}else{
						$_SESSION['mo2f-login-message'] = 'Please login into your account using password.';
						$this->mo_auth_show_success_message();
						$this->mo2f_redirectto_wp_login();
					}
				}else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid Username.';
					$this->mo_auth_show_error_message();
				}
			}	
		}
		
		if(isset($_POST['miniorange_kba_nonce'])){ /*check kba validation*/
			$nonce = $_POST['miniorange_kba_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-kba-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$currentuser = isset($_SESSION[ 'mo2f_current_user' ]) ? $_SESSION[ 'mo2f_current_user' ] : null;
				if(isset($_SESSION[ 'mo2f_current_user' ])){
					if(MO2f_Utility::mo2f_check_empty_or_null($_POST[ 'mo2f_answer_1' ]) || MO2f_Utility::mo2f_check_empty_or_null($_POST[ 'mo2f_answer_2' ])){
						return;
					}
					$otpToken = array();
					$otpToken[0] = $_SESSION['mo_2_factor_kba_questions'][0];
					$otpToken[1] = sanitize_text_field( $_POST[ 'mo2f_answer_1' ] );
					$otpToken[2] = $_SESSION['mo_2_factor_kba_questions'][1];
					$otpToken[3] = sanitize_text_field( $_POST[ 'mo2f_answer_2' ] );
					$check_trust_device = sanitize_text_field( $_POST[ 'mo2f_trust_device' ] );
					
					$kba_validate = new Customer_Setup();
					$kba_validate_response = json_decode($kba_validate->validate_otp_token( 'KBA', null, $_SESSION[ 'mo2f-login-transactionId' ], $otpToken, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
					
					
					if( username_exists( $currentuser->user_login )) { // user is a member 
						if(strcasecmp($kba_validate_response['status'], 'SUCCESS') == 0) {
							remove_filter('authenticate', 'wp_authenticate_username_password', 10, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 10, 3);
						}else{
							$_SESSION[ 'mo2f-login-message' ] = 'The answers you have provided are incorrect.';
						}
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
				}else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid request.';
					$this->mo_auth_show_error_message();
				}	
			}
		}
		
		if(isset($_POST['miniorange_mobile_validation_nonce'])){ /*check mobile validation */
			$nonce = $_POST['miniorange_mobile_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$currentuser = $_SESSION[ 'mo2f_current_user' ];
				$username = $currentuser->user_login;
				if( username_exists( $username )) { // user is a member 
					$checkMobileStatus = new Two_Factor_Setup();
					$content = $checkMobileStatus->check_mobile_status($_SESSION[ 'mo2f-login-transactionId' ]);
					$response = json_decode($content, true);
					if(json_last_error() == JSON_ERROR_NONE) {
						if($response['status'] == 'SUCCESS'){				
							remove_filter('authenticate', 'wp_authenticate_username_password', 10, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 10, 3);
						}else{
							$this->remove_current_activity();
							$_SESSION['mo2f-login-message'] = 'Invalid request.';
							$this->mo_auth_show_error_message();
						}
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
				} else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid request.';
					$this->mo_auth_show_error_message();
				}
			}
		}
		
		if (isset($_POST['miniorange_mobile_validation_failed_nonce'])){ /*Back to miniOrange Login Page if mobile validation failed and from back button of mobile challenge, soft token and default login*/
			$nonce = $_POST['miniorange_mobile_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$this->remove_current_activity();
			}
		}
		
		if(isset($_POST['miniorange_forgotphone'])){ /*Click on the link of forgotphone */
			$nonce = $_POST['miniorange_forgotphone'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else{
				$customer = new Customer_Setup();
				$id = $_SESSION[ 'mo2f_current_user' ]->ID;
				$content = json_decode($customer->send_otp_token(get_user_meta($id,'mo_2factor_map_id_with_email',true),'EMAIL',get_option('mo2f_customerKey'),get_option('mo2f_api_key')), true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					unset($_SESSION[ 'mo2f-login-qrCode' ]);
					unset($_SESSION[ 'mo2f-login-transactionId' ]);
					$_SESSION['mo2f-login-message'] =  'A one time passcode has been sent to <b>' . MO2f_Utility::mo2f_get_hiden_email(get_user_meta($id,'mo_2factor_map_id_with_email',true) ) . '</b>. Please enter the OTP to verify your identity.';
					$_SESSION[ 'mo2f-login-transactionId' ] = $content['txId'];
					$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					$this->mo_auth_show_success_message();
				}else{
					$_SESSION['mo2f-login-message'] = 'Error:OTP over Email';
					$this->mo_auth_show_success_message();
				}
			}
		}
		
		if(isset($_POST['miniorange_softtoken'])){ /*Click on the link of phone is offline */
			$nonce = $_POST['miniorange_softtoken'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else{
				unset($_SESSION[ 'mo2f-login-qrCode' ]);
				unset($_SESSION[ 'mo2f-login-transactionId' ]);
				$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the miniOrange authenticator app.';
				$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			}
		}
		if (isset($_POST['miniorange_soft_token_nonce'])){ /*Validate Soft Token,OTP over SMS,OTP over EMAIL,Phone verification */
			$nonce = $_POST['miniorange_soft_token_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-soft-token-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$softtoken = '';
				if( MO2f_utility::mo2f_check_empty_or_null( $_POST[ 'mo2fa_softtoken' ] ) ) {
					$_SESSION['mo2f-login-message'] = 'Please enter OTP to proceed';
					$this->mo_auth_show_error_message();
					return;
				} else{
					$softtoken = sanitize_text_field( $_POST[ 'mo2fa_softtoken' ] );
					if(!MO2f_utility::mo2f_check_number_length($softtoken)){
						$_SESSION['mo2f-login-message'] = 'Invalid OTP. Only digits within range 4-8 are allowed. Please try again.';
						return;
					}
				}
				$currentuser = isset($_SESSION[ 'mo2f_current_user' ]) ? $_SESSION[ 'mo2f_current_user' ] : null;
				if(isset($_SESSION[ 'mo2f_current_user' ])){
					$customer = new Customer_Setup();
					$content ='';
					if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL'){
						$content = json_decode($customer->validate_otp_token( 'EMAIL', null, $_SESSION[ 'mo2f-login-transactionId' ], $softtoken, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
					}else if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS'){
						$content = json_decode($customer->validate_otp_token( 'SMS', null, $_SESSION[ 'mo2f-login-transactionId' ], $softtoken, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
					}else if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION'){
						$content = json_decode($customer->validate_otp_token( 'PHONE VERIFICATION', null, $_SESSION[ 'mo2f-login-transactionId' ], $softtoken, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
					}else if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){
						$content = json_decode($customer->validate_otp_token( 'SOFT TOKEN', get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true), null, $softtoken, get_option('mo2f_customerKey'), get_option('mo2f_api_key')),true);
					}else if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION'){
						$content = json_decode($customer->validate_otp_token( 'GOOGLE AUTHENTICATOR', get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true), null, $softtoken, get_option('mo2f_customerKey'), get_option('mo2f_api_key')),true);
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request. Please try again.';
						$this->mo_auth_show_error_message();
					}
					
					if( username_exists( $currentuser->user_login )) { // user is a member 
						if(strcasecmp($content['status'], 'SUCCESS') == 0) {
							remove_filter('authenticate', 'wp_authenticate_username_password', 10, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 10, 3);
						}else{
							$message = $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ? 'Invalid OTP ...Possible causes <br />1. You mis-typed the OTP, find the OTP again and type it. <br /> 2. Your phone time is not in sync with miniOrange servers. <br /><b>How to sync?</b> In the app,tap on Settings icon and then press Sync button.' : 'Invalid OTP. Please try again';
							$_SESSION['mo2f-login-message'] = $message;
							$this->mo_auth_show_error_message();
						}
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
				}else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid request.';
					$this->mo_auth_show_error_message();
				}
			}
		}
	}
	
	function remove_current_activity(){
		unset($_SESSION[ 'mo2f_current_user' ]);
		unset($_SESSION[ 'mo_2factor_login_status' ]);
		unset($_SESSION[ 'mo2f-login-qrCode' ]);
		unset($_SESSION[ 'mo2f-login-transactionId' ]);
		unset($_SESSION[ 'mo2f-login-message' ]);
		unset($_SESSION[ 'mo_2_factor_kba_questions' ]);
	}
	
	function mo2fa_login(){
		if(isset($_SESSION[ 'mo2f_current_user' ])){
			$currentuser = $_SESSION[ 'mo2f_current_user' ];
			$user_id = $currentuser->ID;
			wp_set_current_user($user_id, $currentuser->user_login);
			$this->remove_current_activity();
			do_action( 'wp_login', $currentuser->user_login, $currentuser );
			wp_set_auth_cookie( $user_id, true );
			redirect_user_to($currentuser);
			exit;
		}else{
			$this->remove_current_activity();
		}
	}
	
	
	
	function mo2fa_default_login($user,$username,$password){
		$currentuser = mo2f_wp_authenticate_username_password($user, $username, $password);
		if (is_wp_error($currentuser)) {
			return $currentuser;
		}else{
			$current_role = $currentuser->roles[0];
			if(get_option('mo2fa_'.$current_role)){
				if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
					session_start();
				}
				if(get_user_meta($currentuser->ID,'mo_2factor_mobile_registration_status',true) == 'MO_2_FACTOR_SUCCESS'){ // for existing users
					$error = new WP_Error();
					$error->add('empty_username', __('<strong>ERROR</strong>: Login with password is disabled for you.Please Login using your phone'));
					return $error;
				}else if(get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true) && get_user_meta($currentuser->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){ //checking if user has configured any 2nd factor method
					$error = new WP_Error();
					$error->add('empty_username', __('<strong>ERROR</strong>: Login with password is disabled for you.Please Login using your phone'));
					return $error;
				}else{ //if user has not configured any 2nd factor method then logged him in without asking 2nd factor
					$this->mo2f_verify_and_authenticate_userlogin($currentuser);
				}
			}else{ //plugin is not activated for non-admin then logged him in
				$this->mo2f_verify_and_authenticate_userlogin($currentuser);
			}
		}
	}
	
	function mo2f_verify_and_authenticate_userlogin($user){
		
		$user_id = $user->ID;
		$this->remove_current_activity();
		do_action( 'wp_login', $user->user_login, $user );
		wp_set_auth_cookie( $user_id, true );
		redirect_user_to($user);
		exit;
			
	}
	
	function mo2f_login_push_oobemail_verification($user,$mo2f_second_factor){
		$challengeMobile = new Customer_Setup();
		$content = $challengeMobile->send_otp_token(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true),$mo2f_second_factor ,get_option('mo2f_customerKey'),get_option('mo2f_api_key'));
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
			if($response['status'] == 'SUCCESS'){
				$_SESSION[ 'mo2f-login-transactionId' ] = $response['txId'];
				$_SESSION['mo2f-login-message'] = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'A Push Notification has been sent to your phone. We are waiting for your approval.' : 'An email has been sent to ' . MO2f_Utility::mo2f_get_hiden_email(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true)) . '. We are waiting for your approval.';
				$_SESSION[ 'mo_2factor_login_status' ] = $mo2f_second_factor == 'PUSH NOTIFICATIONS' ? 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' : 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL';
			}else if($response['status'] == 'ERROR' || $response['status'] == 'FAILED' ){
				$this->remove_current_activity();
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: An error occured while processing your request. Please Try again.'));
				return $error;
			}
		}else{
			$this->remove_current_activity();
			$error = new WP_Error();
			$error->add('empty_username', __('<strong>ERROR</strong>: An error occured while processing your request. Please Try again.'));
			return $error;
		}
	}

	function mo2f_login_otp_verification($user,$mo2f_second_factor){
		if($mo2f_second_factor == 'SOFT TOKEN'){
			$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the <b>miniOrange Authenticator</b> app.';
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
		}else if($mo2f_second_factor == 'GOOGLE AUTHENTICATOR'){
			$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the <b>Google Authenticator</b> app.';
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION';
		}else{
			$challengeMobile = new Customer_Setup();
			$content = $challengeMobile->send_otp_token(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true), $mo2f_second_factor,get_option('mo2f_customerKey'),get_option('mo2f_api_key'));
			$response = json_decode($content, true);
			if(json_last_error() == JSON_ERROR_NONE) {
				if($response['status'] == 'SUCCESS'){
					$message = $mo2f_second_factor == 'SMS' ? 'The OTP has been sent to '. MO2f_Utility::get_hidden_phone($response['phoneDelivery']['contact']) . '. Please enter the OTP you received to Validate.' : 'You will receive phone call on ' . MO2f_Utility::get_hidden_phone($response['phoneDelivery']['contact']) . ' with OTP. Please enter the OTP to Validate.';
					$_SESSION['mo2f-login-message'] = $message;
					$_SESSION[ 'mo2f-login-transactionId' ] = $response[ 'txId' ];
					$_SESSION[ 'mo_2factor_login_status' ] = $mo2f_second_factor == 'SMS' ? 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' : 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION';
				}else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
					$this->mo_auth_show_error_message();
				}
			}else{
				$this->remove_current_activity();
				$error = new WP_Error();
				$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
				$this->mo_auth_show_error_message();
			}
		}
	}
	
	function mo2f_login_kba_verification($user){
		$challengeKba = new Customer_Setup();
			$content = $challengeKba->send_otp_token(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true), 'KBA',get_option('mo2f_customerKey'),get_option('mo2f_api_key'));
			$response = json_decode($content, true);
			if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
				if($response['status'] == 'SUCCESS'){
					$_SESSION[ 'mo2f-login-transactionId' ] = $response['txId'];
					$questions = array();
					$questions[0] = $response['questions'][0]['question'];
					$questions[1] = $response['questions'][1]['question'];
					$_SESSION[ 'mo_2_factor_kba_questions' ] = $questions;
					$_SESSION['mo2f-login-message'] = 'Please answer the following questions:';
					$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION';
				}else if($response['status'] == 'ERROR'){
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
					$this->mo_auth_show_error_message();
				}
			}else{
				$this->remove_current_activity();
				$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
				$this->mo_auth_show_error_message();
			}
	}
	
	function mo2f_login_mobile_verification($user){
		
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		if(strpos($useragent,'Mobi') !== false){
			unset($_SESSION[ 'mo2f-login-qrCode' ]);
			unset($_SESSION[ 'mo2f-login-transactionId' ]);
			$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the miniOrange Authenticator app.';
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
		}else{
			$challengeMobile = new Customer_Setup();
			$content = $challengeMobile->send_otp_token(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true), 'MOBILE AUTHENTICATION',get_option('mo2f_customerKey'),get_option('mo2f_api_key'));
			$response = json_decode($content, true);
			if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
				if($response['status'] == 'SUCCESS'){
					
					$_SESSION[ 'mo2f-login-qrCode' ] = $response['qrCode'];
					$_SESSION[ 'mo2f-login-transactionId' ] = $response['txId'];
					$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION';
				}else if($response['status'] == 'ERROR'){
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
					$this->mo_auth_show_error_message();
				}
			}else{
				$this->remove_current_activity();
				$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';	
				$this->mo_auth_show_error_message();
			}
		}
		
	}
	
	function mo2f_redirectto_wp_login(){
		remove_action('login_enqueue_scripts', array( $this, 'mo_2_factor_hide_login'));
		add_action('login_dequeue_scripts', array( $this, 'mo_2_factor_show_login'));
		if(get_option('mo2f_show_loginwith_phone')){
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_LOGIN_WHEN_PHONELOGIN_ENABLED';
		}else{
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM';
		}
	}
	
	public function custom_login_enqueue_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap_script', plugins_url('includes/js/bootstrap.min.js', __FILE__ ));
	}
	
	public function mo_2_factor_hide_login() {
		wp_register_style( 'hide-login', plugins_url( 'includes/css/hide-login.css?version=3.4', __FILE__ ) );
		wp_register_style( 'bootstrap', plugins_url( 'includes/css/bootstrap.min.css?version=3.4', __FILE__ ) );
		
		wp_enqueue_style( 'hide-login' );
		wp_enqueue_style( 'bootstrap' );
		
	}
	
	function mo_2_factor_show_login() {
		if(get_option('mo2f_show_loginwith_phone')){
			wp_register_style( 'show-login', plugins_url( 'includes/css/hide-login-form.css?version=3.4', __FILE__ ) );
		}else{
			wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css?version=3.4', __FILE__ ) );
		}
		wp_enqueue_style( 'show-login' );
	}
	
	function mo_2_factor_show_login_with_password_when_phonelogin_enabled(){
		wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css?version=3.4', __FILE__ ) );
		wp_enqueue_style( 'show-login' );
	}
	
	function mo_auth_success_message() {
		$message = $_SESSION['mo2f-login-message'];
		return "<div> <p class='message'>" . $message . "</p></div>";
	}

	function mo_auth_error_message() {
		$id = "login_error1";
		$message = $_SESSION['mo2f-login-message'];
		return "<div id='" . $id . "'> <p>" . $message . "</p></div>";
	}
	
	private function mo_auth_show_error_message() {
		remove_filter( 'login_message', array( $this, 'mo_auth_success_message') );
		add_filter( 'login_message', array( $this, 'mo_auth_error_message') );
	}
	
	private function mo_auth_show_success_message() {
		remove_filter( 'login_message', array( $this, 'mo_auth_error_message') );
		add_filter( 'login_message', array( $this, 'mo_auth_success_message') );
	}
	


	
	// login form fields
	public function miniorange_login_form_fields() {
		if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
			session_start();
		}
		if(!get_option('mo2f_show_loginwith_phone')){ //Login with phone is alogin with default login form
			$login_status = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : null;
			if($this->miniorange_check_mobile_status($login_status)){			
				$this->mo_2_factor_show_qr_code();
			}else if($this->miniorange_check_otp_status($login_status)){
				$this->mo_2_factor_show_otp_token();
			}else if($this->miniorange_check_push_oobemail_status($login_status)){ //for push and out of band email.
				$this->mo_2_factor_show_push_oobemail();
			}else if($this->miniorange_login_check_kba_status($login_status)){ // for Kba 
				$this->mo_2_factor_login_show_kba();
			}else if($login_status == 'MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM'){
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}else{
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}
		}else{ //login with phone overwrite default login form
			
			$login_status_phone_enable = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : '';
			if($this->miniorange_check_mobile_status($login_status_phone_enable)){			
				$this->mo_2_factor_show_qr_code();
			}else if($this->miniorange_check_otp_status($login_status_phone_enable)){
				$this->mo_2_factor_show_otp_token();
			}else if($this->miniorange_login_check_kba_status($login_status_phone_enable)){ // for Kba 
				$this->mo_2_factor_login_show_kba();
			}else if($this->miniorange_check_push_oobemail_status($login_status_phone_enable)){ //for push and out of band email.
				$this->mo_2_factor_show_push_oobemail();
			}else if($login_status_phone_enable == 'MO_2_FACTOR_LOGIN_WHEN_PHONELOGIN_ENABLED' && isset($_POST['miniorange_login_nonce']) && wp_verify_nonce( $_POST['miniorange_login_nonce'], 'miniorange-2-factor-login-nonce' )){
				$this->mo_2_factor_show_login_with_password_when_phonelogin_enabled();
				$this->mo_2_factor_show_wp_login_form_when_phonelogin_enabled();
				?><script>
					jQuery('#user_login').val(<?php echo "'" . $_SESSION[ 'mo2f_current_user' ]->user_login . "'"; ?>);
				</script><?php
			}else{
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}
		}
	}
	
	function miniorange_check_push_oobemail_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' || $login_status == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL'){
			$nonce = '';
			
			if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_forgotphone'])){
				$nonce = $_POST['miniorange_forgotphone'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-forgotphone')){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_check_mobile_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION'){
			$nonce = '';
			if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_forgotphone'])){
				$nonce = $_POST['miniorange_forgotphone'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-forgotphone')){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_check_otp_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' || $login_status == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' || $login_status == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION'){
			$nonce = '';
			
			if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}
			if(isset($_POST['miniorange_softtoken'])){
				$nonce = $_POST['miniorange_softtoken'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-softtoken')){
					return true;
				}		
			}else if(isset($_POST['miniorange_forgotphone'])){
				$nonce = $_POST['miniorange_forgotphone'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-forgotphone')){
					return true;
				}
			}else if(isset($_POST['miniorange_soft_token_nonce'])){
				$nonce = $_POST['miniorange_soft_token_nonce'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-soft-token-nonce')){
					return true;
				}
			}
			return false;
		}
	}
	
	function miniorange_login_check_kba_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION'){
			$nonce = '';
			if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_kba_nonce']) ){
				$nonce = $_POST['miniorange_kba_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-kba-nonce' )){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_login_footer_form(){
		
		?>
			<form name="f" id="mo2f_show_softtoken_loginform" method="post" action="" hidden>
				<input type="hidden" name="miniorange_softtoken" value="<?php echo wp_create_nonce('miniorange-2-factor-softtoken'); ?>" />
			</form>
			<form name="f" id="mo2f_show_forgotphone_loginform" method="post" action="" hidden>
				<input type="hidden" name="miniorange_forgotphone" value="<?php echo wp_create_nonce('miniorange-2-factor-forgotphone'); ?>" />
			</form>
			<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo wp_login_url(); ?>" hidden>
				<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_mobile_validation_form" method="post" action="" hidden>
				<input type="hidden" name="miniorange_mobile_validation_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_show_qrcode_loginform" method="post" action="" hidden>
				<input type="text" name="mo2fa_username" id="mo2fa_username" hidden/>
				<input type="hidden" name="miniorange_login_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-login-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_submitotp_loginform" method="post" action="" hidden>
				<input type="text" name="mo2fa_softtoken" id="mo2fa_softtoken" hidden/>
				<input type="hidden" name="miniorange_soft_token_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-soft-token-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_submitkba_loginform" method="post" action="" style="display:none;"> 
				<input type="text" name="mo2f_answer_1" id="mo2f_answer_1" hidden />
				<input type="text" name="mo2f_answer_2" id="mo2f_answer_1" hidden />
				<input type="text" name="mo2f_trust_device" id="mo2f_trust_device" hidden />
				<input type="hidden" name="miniorange_kba_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-kba-nonce'); ?>" />
			</form>

		<?php
	}
	
	function mo_2_factor_show_wp_login_form_when_phonelogin_enabled(){
	?>
		<script>
			var content = '<a href="javascript:void(0)" id="backto_mo" onClick="mo2fa_backtomologin()" style="float:right">← Back</a>';
			jQuery('#login').append(content);
			function mo2fa_backtomologin(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
		</script>
	<?php
	}
	
	function mo_2_factor_show_wp_login_form(){
	?>
		<div class="mo2f-login-container">
			<?php if(!get_option('mo2f_show_loginwith_phone')){ ?>
			<div style="position: relative" class="or-container">
				<div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
				<h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
			</div>
			<?php } ?>
			<div class="mo2f-button-container" id="mo2f_button_container">
				<input type="text" name="mo2fa_usernamekey" id="mo2fa_usernamekey" autofocus="true" placeholder="Username"/>
					<p>
						<input type="button" name="miniorange_login_submit"  style="width:100% !important;" onclick="mouserloginsubmit();" id="miniorange_login_submit" class="miniorange-button button-add" value="Login with your phone" />
					</p>
					<?php if(!get_option('mo2f_show_loginwith_phone')){ ?><br /><br /><?php } ?>
			</div>
		</div>
		
		<script>
			jQuery(window).scrollTop(jQuery('#mo2f_button_container').offset().top);
			function mouserloginsubmit(){
				var username = jQuery('#mo2fa_usernamekey').val();
				document.getElementById("mo2f_show_qrcode_loginform").elements[0].value = username;
				jQuery('#mo2f_show_qrcode_loginform').submit();
				
			 }
			 
			 jQuery('#mo2fa_usernamekey').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var username = jQuery('#mo2fa_usernamekey').val();
					document.getElementById("mo2f_show_qrcode_loginform").elements[0].value = username;
					jQuery('#mo2f_show_qrcode_loginform').submit();
				  }
				 
			});
		</script>
	<?php
	}
	public function mo_2_factor_show_push_oobemail(){
		mo2f_getpush_oobemail_response();
	}
	
	public function mo_2_factor_show_otp_token(){
		mo2f_getotp_form();
	}
	
	public function mo_2_factor_show_qr_code(){
		mo2f_getqrcode();
	}
	
	function mo_2_factor_login_show_kba(){
		mo2f_getkba_form();
	}
}	
?>