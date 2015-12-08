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
include_once dirname( __FILE__ ) . '/miniorange_2_factor_user_inline_registration.php';
include_once dirname( __FILE__ ) . '/class-rba-attributes.php';

class Miniorange_Password_2Factor_Login{
	
	function remove_current_activity(){
		unset($_SESSION[ 'mo2f_current_user' ]);
		unset($_SESSION[ 'mo2f_1stfactor_status' ]);
		unset($_SESSION[ 'mo_2factor_login_status' ]);
		unset($_SESSION[ 'mo2f-login-qrCode' ]);
		unset($_SESSION[ 'mo2f-login-transactionId' ]);
		unset($_SESSION[ 'mo2f-login-message' ]);
		unset($_SESSION[ 'mo2f_rba_status' ]);
		unset($_SESSION[ 'mo_2_factor_kba_questions' ]);
		unset($_SESSION[ 'mo2f_show_qr_code']);
		unset($_SESSION['mo2f_google_auth']);
		
	}
	
	function mo2fa_pass2login(){
		if(isset($_SESSION[ 'mo2f_current_user' ]) && isset($_SESSION[ 'mo2f_1stfactor_status' ]) && $_SESSION[ 'mo2f_1stfactor_status' ] = 'VALIDATE_SUCCESS'){
			$currentuser = $_SESSION[ 'mo2f_current_user' ];
			$user_id = $currentuser->ID;
			wp_set_current_user($user_id, $currentuser->user_login);
			$this->remove_current_activity();
			wp_set_auth_cookie( $user_id, true );
			do_action( 'wp_login', $currentuser->user_login, $currentuser );
			redirect_user_to($currentuser);
			exit;
		}else{
			$this->remove_current_activity();
		}
	}
	
	public function miniorange_pass2login_redirect() {
		if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
			session_start();
		}
		
		if(isset($_POST['mo2f_trust_device_confirm_nonce'])){ /*register device as rba profile */
			$nonce = $_POST['mo2f_trust_device_confirm_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-trust-device-confirm-nonce' ) ) {
				$this->remove_current_activity();
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				try{
					$currentuser = $_SESSION[ 'mo2f_current_user' ];
					mo2f_register_profile(get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true),'true',$_SESSION[ 'mo2f_rba_status' ]);
				}catch(Exception $e){
					echo $e->getMessage();
				}
				$this->mo2fa_pass2login();
			}
		}
		
		if(isset($_POST['mo2f_trust_device_cancel_nonce'])){ /*do not register device as rba profile */
			$nonce = $_POST['mo2f_trust_device_cancel_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-trust-device-cancel-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$this->mo2fa_pass2login();
			}
		}
		
		if(isset($_POST['miniorange_kba_nonce'])){ /*check kba validation*/
			$nonce = $_POST['miniorange_kba_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-kba-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
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
		
					if(strcasecmp($kba_validate_response['status'], 'SUCCESS') == 0) {
						if(get_option('mo2f_deviceid_enabled') && $check_trust_device == 'true'){
							try{
								mo2f_register_profile(get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true),'true',$_SESSION[ 'mo2f_rba_status' ]);
							}catch(Exception $e){
								echo $e->getMessage();
							}
							$this->mo2fa_pass2login();
						}else{
							$this->mo2fa_pass2login();
						}
					}else{
						
						$_SESSION[ 'mo2f-login-message' ] = 'The answers you have provided are incorrect.';
					}
				}else{
					$this->remove_current_activity();
					return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Please try again..'));
				}
			}
		}
		
		if(isset($_POST['miniorange_mobile_validation_nonce'])){ /*check mobile validation */
			
			$nonce = $_POST['miniorange_mobile_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {	
				
				$currentuser = $_SESSION[ 'mo2f_current_user' ];
				$checkMobileStatus = new Two_Factor_Setup();
				$content = $checkMobileStatus->check_mobile_status($_SESSION[ 'mo2f-login-transactionId' ]);
				$response = json_decode($content, true);
				if(json_last_error() == JSON_ERROR_NONE) {
					if($response['status'] == 'SUCCESS'){	
						if(get_option('mo2f_deviceid_enabled')){
							$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE';
						}else{
							$this->mo2fa_pass2login();
						}
					}else{
						$this->remove_current_activity();
						return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Please try again.'));
					}
				}else{
					$this->remove_current_activity();
					return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Please try again.'));
				}
			}
		}
		
		if (isset($_POST['miniorange_mobile_validation_failed_nonce'])){ /*Back to miniOrange Login Page if mobile validation failed and from back button of mobile challenge, soft token and default login*/
			$nonce = $_POST['miniorange_mobile_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$this->remove_current_activity();
			}
		}
		
		if(isset($_POST['miniorange_forgotphone'])){ /*Click on the link of forgotphone */
			$nonce = $_POST['miniorange_forgotphone'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
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
				}else{
					$_SESSION['mo2f-login-message'] = 'Error:OTP over Email';
				}
			}
		} 
		
		if ( isset($_POST['miniorange_inline_user_reg_nonce'])){	
			
			$nonce = $_POST['miniorange_inline_user_reg_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-user-reg-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$email = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo_useremail'] )){
					$_SESSION['mo2f-login-message'] = 'Please enter email-id to register.';
					return;
				}else{
					$email = sanitize_email( $_POST['mo_useremail'] );
				}
				
				if(!MO2f_Utility::check_if_email_is_already_registered($email)){
					$currentUserId = $_SESSION[ 'mo2f_current_user' ]->ID;
					update_user_meta($currentUserId,'mo_2factor_user_email',$email);
					
					$enduser = new Two_Factor_Setup();
					$check_user = json_decode($enduser->mo_check_user_already_exist($email),true);
					if(json_last_error() == JSON_ERROR_NONE){
						if($check_user['status'] == 'ERROR'){
							$_SESSION['mo2f-login-message'] = $check_user['message'];
							
							return;
						}else if(strcasecmp($check_user['status'], 'USER_FOUND_UNDER_DIFFERENT_CUSTOMER') == 0){
							$_SESSION['mo2f-login-message'] =  'The email you entered is already registered. Please register with another email to set up Two-Factor.';
							
							return;
						}
						else if(strcasecmp($check_user['status'], 'USER_FOUND') == 0 || strcasecmp($check_user['status'], 'USER_NOT_FOUND') == 0){
					
							$enduser = new Customer_Setup();
							$content = json_decode($enduser->send_otp_token($email,'EMAIL',get_option('mo2f_customerKey'),get_option('mo2f_api_key')), true);
							if(strcasecmp($content['status'], 'SUCCESS') == 0) {
								$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_PROMPT_FOR_USER_REG_OTP';
								$_SESSION['mo2f-login-message'] = 'An OTP has been sent to <b>' . ( $email ) . '</b>. Please enter the OTP below to verify your email. If you didn\'t get the email, please check your <b>SPAM</b> folder.';
								update_user_meta($currentUserId,'mo_2fa_verify_otp_create_account',$content['txId']);
								update_user_meta($currentUserId, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
									
							}else{
								$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_PROMPT_FOR_USER_REG_OTP';
								$_SESSION['mo2f-login-message'] = 'There was an error in sending OTP over email. Please click on Resend OTP to try again.';
								update_user_meta($currentUserId, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');	
							}
						}
					}
				}else{
					$_SESSION['mo2f-login-message'] = 'The email is already used by other user. Please register with other email.';	
					
				}
			}
		}
		
		if( isset($_POST['miniorange_inline_two_factor_setup'])){
			$nonce = $_POST['miniorange_inline_two_factor_setup'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-setup-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
				unset($_SESSION['mo2f_google_auth']);
				$_SESSION['mo2f-login-message'] = '';
				delete_user_meta($_SESSION[ 'mo2f_current_user' ]->ID,'mo2f_selected_2factor_method');
			}
		}
		
		if ( isset($_POST['miniorange_inline_resend_otp_nonce'])){	//resend otp during user inline registration
			
			$nonce = $_POST['miniorange_inline_resend_otp_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-resend-otp-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$currentUserId = $_SESSION[ 'mo2f_current_user' ]->ID;
				
				$userkey = '';
				if(get_user_meta( $currentUserId,'mo2f_selected_2factor_method',true) == 'SMS'){
					$currentMethod = "OTP_OVER_SMS";
					$userkey = $_SESSION['mo2f_phone'];
					$_SESSION['mo2f-login-message'] = 'The One Time Passcode has been sent to ' . $userkey . '. Please enter the one time passcode below to verify your number.';
				}else if(get_user_meta( $currentUserId,'mo2f_selected_2factor_method',true) == 'PHONE VERIFICATION'){
					$currentMethod = "PHONE_VERIFICATION";
					$userkey = $_SESSION['mo2f_phone'];
					$_SESSION['mo2f-login-message'] = 'You will receive a phone call on this number ' . $userkey . '. Please enter the one time passcode below to verify your number.';
				}else{
					$currentMethod = 'EMAIL';
					$userkey = get_user_meta($currentUserId,'mo_2factor_user_email',true);
					$_SESSION['mo2f-login-message'] = 'An OTP has been sent to <b>' . ( $userkey ) . '</b>. Please enter the OTP below to verify your email.';
				}
				
				$customer = new Customer_Setup();
				$content = json_decode($customer->send_otp_token($userkey,$currentMethod,get_option( 'mo2f_customerKey'),get_option( 'mo2f_api_key')), true);
				
				
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_user_meta($currentUserId,'mo_2fa_verify_otp_create_account',$content['txId']);
					if($currentMethod == 'EMAIL'){
						update_user_meta($currentUserId, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
						$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_PROMPT_FOR_USER_REG_OTP';
					}
										
				}else{
					$_SESSION['mo2f-login-message'] = 'There was an error in sending one time passcode. Please click on Resend OTP to try again.';
					if($currentMethod == 'EMAIL'){
						update_user_meta($currentUserId, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
						$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_PROMPT_FOR_USER_REG_OTP';
					}
				}
				
			}
		}
		
		if ( isset($_POST['mo2f_inline_ga_phone_type_nonce'])){	//select google phone type during user inline registration when google authenticator is selected
		
			$nonce = $_POST['mo2f_inline_ga_phone_type_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-ga-phone-type-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$phone_type = $_POST['google_phone_type'];
				
				$current_user = $_SESSION[ 'mo2f_current_user' ];
				$google_auth = new Miniorange_Rba_Attributes();
				$google_response = json_decode($google_auth->mo2f_google_auth_service(get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true)),true);
				if(json_last_error() == JSON_ERROR_NONE) {
					if($google_response['status'] == 'SUCCESS'){
						$mo2f_google_auth = array();
						$mo2f_google_auth['ga_qrCode'] = $google_response['qrCodeData'];
						$mo2f_google_auth['ga_secret'] = $google_response['secret'];
						$mo2f_google_auth['ga_phone'] = $phone_type;
						$_SESSION['mo2f_google_auth'] = $mo2f_google_auth;
						$_SESSION['mo2f-login-message'] = '';
						
					}else{
						$_SESSION['mo2f-login-message'] = 'Error occurred while registering the user for google authenticator. Please try again.';
					}
				}else{
					$_SESSION['mo2f-login-message'] = 'Invalid request. Please try again.';
				}
			}
		}
		
		if(isset($_POST['mo2f_inline_validate_ga_nonce'])){
			$nonce = $_POST['mo2f_inline_validate_ga_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-google-auth-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
			$otpToken = $_POST['google_auth_code'];
			$current_user = $_SESSION[ 'mo2f_current_user' ];
			$mo2f_google_auth = isset($_SESSION['mo2f_google_auth']) ? $_SESSION['mo2f_google_auth'] : null;
			$ga_secret = $mo2f_google_auth != null ? $mo2f_google_auth['ga_secret'] : null;
			if(MO2f_Utility::mo2f_check_number_length($otpToken)){
				$email = get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true);
				$google_auth = new Miniorange_Rba_Attributes();
				$google_response = json_decode($google_auth->mo2f_validate_google_auth($email,$otpToken,$ga_secret),true);
				if(json_last_error() == JSON_ERROR_NONE) {
					if($google_response['status'] == 'SUCCESS'){
						$enduser = new Two_Factor_Setup();
						$response = json_decode($enduser->mo2f_update_userinfo($email,get_user_meta( $current_user->ID,'mo2f_selected_2factor_method',true),null,null,null),true);
						if(json_last_error() == JSON_ERROR_NONE) { 
							
							if($response['status'] == 'SUCCESS'){
							
								update_user_meta($current_user->ID,'mo2f_google_authentication_status',true);
								update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
								$this->mo2fa_pass2login();
								
							}else{
								$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
							}
						}else{
							$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
						}
					}else{
						$_SESSION['mo2f-login-message'] = 'Error occurred while validating the OTP. Please try again. Possible causes: <br />1. You have enter invalid OTP.<br />2. You App Time is not sync.Go to seetings and tap on Time correction for codes and tap on Sync now .';
					}
				}else{
					$_SESSION['mo2f-login-message'] = 'Error occurred while validating the user. Please try again.';
					
				}
			}else{
				$_SESSION['mo2f-login-message'] = 'Only digits are allowed. Please enter again.';
				
			}
		}
		}
		
		if(isset($_POST['miniorange_inline_validate_user_otp_nonce'])){
			$nonce = $_POST['miniorange_inline_validate_user_otp_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-user-otp-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
		
				$otp_token = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['otp_token'] ) ) {
					$_SESSION['mo2f-login-message'] = 'All the fields are required. Please enter valid entries.';
					return;
				} else{
					$otp_token = sanitize_text_field( $_POST['otp_token'] );
				}
				
				$id = $_SESSION[ 'mo2f_current_user' ]->ID;
				if(!MO2f_Utility::check_if_email_is_already_registered(get_user_meta($id,'mo_2factor_user_email',true))){
					$customer = new Customer_Setup();
					$transactionId = get_user_meta($id,'mo_2fa_verify_otp_create_account',true);
					$content = json_decode($customer->validate_otp_token( 'EMAIL', null, $transactionId, $otp_token, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
					if($content['status'] == 'ERROR'){
						$_SESSION['mo2f-login-message'] = $content['message'];
						delete_user_meta($id,'mo_2fa_verify_otp_create_account');
					}else{
						if(strcasecmp($content['status'], 'SUCCESS') == 0) { //OTP validated and generate QRCode
							$this->mo2f_register_user_inline(get_user_meta($id,'mo_2factor_user_email',true));
							delete_user_meta($id,'mo_2fa_verify_otp_create_account');
						}else{  // OTP Validation failed.
							$_SESSION['mo2f-login-message'] = 'Invalid OTP. Please try again.';
							update_user_meta($id,'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
							
						}
						delete_user_meta($id,'mo_2fa_verify_otp_create_account');
					}

				}else{
					$_SESSION['mo2f-login-message'] = 'The email is already used by other user. Please register with other email by clicking on Back button.';	
					
				}
			}
		}
		
		if(isset($_POST['miniorange_inline_save_2factor_method_nonce'])){
			$nonce = $_POST['miniorange_inline_save_2factor_method_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-2factor-method-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				
				$currentUserId = $_SESSION[ 'mo2f_current_user' ]->ID;
				if(get_user_meta($currentUserId,'mo_2factor_user_registration_with_miniorange',true) == 'SUCCESS'){
					update_user_meta( $currentUserId,'mo2f_selected_2factor_method', $_POST['mo2f_selected_2factor_method']); //status for second factor selected by user
				}else{
					$_SESSION['mo2f-login-message'] = 'Invalid request. Please register with miniOrange to configure 2 Factor plugin.';
					
				}
			}
		}
		
		if(isset($_POST['miniorange_inline_verify_phone_nonce'])){
			$nonce = $_POST['miniorange_inline_verify_phone_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-verify-phone-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
		
				$phone = sanitize_text_field( $_POST['verify_phone'] );
							
				if( MO2f_Utility::mo2f_check_empty_or_null( $phone ) ){
					$_SESSION['mo2f-login-message'] = 'All the fields are required. Please enter valid entries.';
					return;
				}
				$phone = str_replace(' ', '', $phone);
				$_SESSION['mo2f_phone'] = $phone;
				$current_user = $_SESSION[ 'mo2f_current_user' ]->ID;
				$customer = new Customer_Setup();
					
					if(get_user_meta( $current_user,'mo2f_selected_2factor_method',true) == 'SMS'){
						$currentMethod = "OTP_OVER_SMS";
					}else if(get_user_meta( $current_user,'mo2f_selected_2factor_method',true) == 'PHONE VERIFICATION'){
						$currentMethod = "PHONE_VERIFICATION";
					}
					
					$content = json_decode($customer->send_otp_token($phone,$currentMethod,get_option( 'mo2f_customerKey'),get_option( 'mo2f_api_key')), true);
					
				if(json_last_error() == JSON_ERROR_NONE) { /* Generate otp token */
					if($content['status'] == 'ERROR'){
						$_SESSION['mo2f-login-message'] = $response['message'];
						
					}else if($content['status'] == 'SUCCESS'){
						$_SESSION[ 'mo2f_transactionId' ] = $content['txId'];
						
						if(get_user_meta( $current_user,'mo2f_selected_2factor_method',true) == 'SMS'){
								$_SESSION['mo2f-login-message'] = 'The One Time Passcode has been sent to ' . $phone . '. Please enter the one time passcode below to verify your number.';
						}else if(get_user_meta( $current_user,'mo2f_selected_2factor_method',true)== 'PHONE VERIFICATION'){
							$_SESSION['mo2f-login-message'] = 'You will receive a phone call on this number ' . $phone . '. Please enter the one time passcode below to verify your number.';
						}
						
					}else{
						$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
						
					}
					
				}else{
					$_SESSION['mo2f-login-message'] = 'Invalid request. Please try again';
					
				}
			}
		}
		
		if(isset($_POST['miniorange_inline_validate_otp_nonce'])){
			$nonce = $_POST['miniorange_inline_validate_otp_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-otp-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
		
				$otp_token = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['otp_token'] ) ) {
					$_SESSION['mo2f-login-message'] =  'All the fields are required. Please enter valid entries.';
					return;
				} else{
					$otp_token = sanitize_text_field( $_POST['otp_token'] );
				}
				
				$current_user = $_SESSION[ 'mo2f_current_user' ]->ID;
				$customer = new Customer_Setup();
				$content = json_decode($customer->validate_otp_token( get_user_meta( $current_user,'mo2f_selected_2factor_method',true), null, $_SESSION[ 'mo2f_transactionId' ], $otp_token, get_option('mo2f_customerKey'), get_option('mo2f_api_key') ),true);
				if($content['status'] == 'ERROR'){
					$_SESSION['mo2f-login-message'] = $content['message'];
				
				}else if(strcasecmp($content['status'], 'SUCCESS') == 0) { //OTP validated 
						if(get_user_meta($current_user,'mo2f_user_phone',true) && strlen(get_user_meta($current_user,'mo2f_user_phone',true)) >= 4){
							if($_SESSION['mo2f_phone'] != get_user_meta($current_user,'mo2f_user_phone',true) ){
								update_user_meta($current_user,'mo2f_mobile_registration_status',false);
							}
						}
						$email = get_user_meta($current_user,'mo_2factor_map_id_with_email',true);
						$phone = $_SESSION['mo2f_phone'];
						
						$enduser = new Two_Factor_Setup();
						$response = json_decode($enduser->mo2f_update_userinfo($email,get_user_meta( $current_user,'mo2f_selected_2factor_method',true),$phone,null,null),true);
						if(json_last_error() == JSON_ERROR_NONE) { 
								
								if($response['status'] == 'ERROR'){
									unset($_SESSION[ 'mo2f_phone']);
									$_SESSION['mo2f-login-message'] = $response['message'];
									$this->mo_auth_show_error_message();
								}else if($response['status'] == 'SUCCESS'){
									update_user_meta($current_user,'mo2f_otp_registration_status',true);
									update_user_meta($current_user,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
									update_user_meta($current_user,'mo2f_user_phone',$_SESSION[ 'mo2f_phone']);
									unset($_SESSION[ 'mo2f_phone']);
									$this->mo2fa_pass2login();
									
								}else{
										unset($_SESSION[ 'mo2f_phone']);
										$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
										
								}
						}else{
								unset($_SESSION[ 'mo2f_phone']);
								$_SESSION['mo2f-login-message'] = 'Invalid request. Please try again';
								
						}
						
				}else{  // OTP Validation failed.
						$_SESSION['mo2f-login-message'] =  'Invalid OTP. Please try again.';
						
				}
			} 
		}
		
		if(isset($_POST['miniorange_inline_show_qrcode_nonce'])){
			$nonce = $_POST['miniorange_inline_show_qrcode_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-show-qrcode-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
		
				$current_user = $_SESSION[ 'mo2f_current_user' ];
				if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_TWO_FACTOR') {
					$email = get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true);
					$this->mo2f_inline_get_qr_code_for_mobile($email,$current_user->ID);
				}else{
					$_SESSION['mo2f-login-message'] = 'Invalid request. Please register with miniOrange before configuring your mobile.';
					
				}
			}
		}
		
		
		if(isset($_POST['mo_auth_inline_mobile_registration_complete_nonce'])){
			$nonce = $_POST['mo_auth_inline_mobile_registration_complete_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-mobile-registration-complete-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
		
				unset($_SESSION[ 'mo2f-login-qrCode' ]);
				unset($_SESSION[ 'mo2f-login-transactionId' ]);
				unset($_SESSION[ 'mo2f_show_qr_code'] );
				$current_user = $_SESSION[ 'mo2f_current_user' ]->ID;
				$email = get_user_meta($current_user,'mo_2factor_map_id_with_email',true);
				$enduser = new Two_Factor_Setup();
				$response = json_decode($enduser->mo2f_update_userinfo($email,get_user_meta( $current_user,'mo2f_selected_2factor_method',true),null,null,null),true);
				if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
						if($response['status'] == 'ERROR'){
							$_SESSION['mo2f-login-message'] = $response['message'];
							
						}else if($response['status'] == 'SUCCESS'){
								
							update_user_meta($current_user,'mo2f_mobile_registration_status',true);
							update_user_meta($current_user,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
							$this->mo2fa_pass2login();
						}else{
							$_SESSION['mo2f-login-message'] = 'An error occured while processing your request. Please Try again.';
								
						}
						
				}else{
						$_SESSION['mo2f-login-message'] = 'Invalid request. Please try again';
						
				}
			
			}
		}
		
		if(isset($_POST['mo2f_inline_save_kba_nonce'])){
			$nonce = $_POST['mo2f_inline_save_kba_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-kba-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				if(MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kbaquestion_1'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kba_ans1'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kbaquestion_2'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kba_ans2'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kbaquestion_3'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2f_kba_ans3'] ) ){
					$_SESSION['mo2f-login-message'] =  'All the fields are required. Please enter valid entries.';
					return;
				}
				$kba_q1 = $_POST[ 'mo2f_kbaquestion_1' ];
				$kba_a1 = sanitize_text_field( $_POST[ 'mo2f_kba_ans1' ] );
				$kba_q2 = $_POST[ 'mo2f_kbaquestion_2' ];
				$kba_a2 = sanitize_text_field( $_POST[ 'mo2f_kba_ans2' ] );
				$kba_q3 = sanitize_text_field( $_POST[ 'mo2f_kbaquestion_3' ] );
				$kba_a3 = sanitize_text_field( $_POST[ 'mo2f_kba_ans3' ] );
				if (strcasecmp($kba_q1, $kba_q2) == 0 || strcasecmp($kba_q2, $kba_q3) == 0 || strcasecmp($kba_q3, $kba_q1) == 0) {
					$_SESSION['mo2f-login-message'] = 'The questions you select must be unique.';
					return;
				}
				$current_user = $_SESSION[ 'mo2f_current_user' ];
				$email = get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true);
				$kba_registration = new Two_Factor_Setup();
				$kba_reg_reponse = json_decode($kba_registration->register_kba_details($email, $kba_q1,$kba_a1,$kba_q2,$kba_a2,$kba_q3,$kba_a3),true);
				if(json_last_error() == JSON_ERROR_NONE) { 
					if($kba_reg_reponse['status'] == 'SUCCESS'){
						$enduser = new Two_Factor_Setup();
						$response = json_decode($enduser->mo2f_update_userinfo($email,get_user_meta( $current_user->ID,'mo2f_selected_2factor_method',true),null,null,null),true);
						if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
							if($response['status'] == 'ERROR'){
								$_SESSION['mo2f-login-message'] = $response['message'];
							
							}else if($response['status'] == 'SUCCESS'){
								update_user_meta($current_user->ID,'mo2f_kba_registration_status',true);
								update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
								$this->mo2fa_pass2login();
							}
						}else{
							$_SESSION['mo2f-login-message'] = 'Error occured while saving your kba details. Please try again.';
						}
					}else{
						$_SESSION['mo2f-login-message'] = 'Error occured while saving your kba details. Please try again.';
					}
				}else{
					$_SESSION['mo2f-login-message'] = 'Error occured while saving your kba details. Please try again.';
				}
			
			}
		}
		if(isset($_POST['mo2f_inline_email_setup'])){
			
			$nonce = $_POST['mo2f_inline_email_setup'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2fa-inline-email-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				
				$current_user = $_SESSION[ 'mo2f_current_user' ];
				update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
				update_user_meta($current_user->ID,'mo2f_email_verification_status',true);
				
				$this->mo2fa_pass2login();
			}
		}
		
		if(isset($_POST['miniorange_softtoken'])){ /*Click on the link of phone is offline */
			$nonce = $_POST['miniorange_softtoken'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
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
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$softtoken = '';
				if( MO2f_utility::mo2f_check_empty_or_null( $_POST[ 'mo2fa_softtoken' ] ) ) {
					$_SESSION['mo2f-login-message'] = 'Please enter OTP to proceed.';
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
						return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Invalid Request. Please try again.'));
					}
					
					
					
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						if(get_option('mo2f_deviceid_enabled')){
							$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE';
						}else{
							$this->mo2fa_pass2login();
						}
					}else{
						
						$message = $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ? 'Invalid OTP ...Possible causes <br />1. You mis-typed the OTP, find the OTP again and type it. <br /> 2. Your phone time is not in sync with miniOrange servers. <br /><b>How to sync?</b> In the app,tap on Settings icon and then press Sync button.' : 'Invalid OTP. Please try again';
						$_SESSION['mo2f-login-message'] = $message;
					}
					
				}else{
					$this->remove_current_activity();
					return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Please try again..'));
				}
			}
		}
		
		if (isset($_POST['miniorange_inline_skip_registration_nonce'])){ /*Validate Soft Token,OTP over SMS,OTP over EMAIL,Phone verification */
			$nonce = $_POST['miniorange_inline_skip_registration_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-skip-registration-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$this->mo2fa_pass2login();
			}
		}
		
		if (isset($_POST['miniorange_inline_goto_user_registration_nonce'])){ /*Validate Soft Token,OTP over SMS,OTP over EMAIL,Phone verification */
			$nonce = $_POST['miniorange_inline_goto_user_registration_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-goto-user-registration-nonce' ) ) {
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			} else {
				$current_user = $_SESSION[ 'mo2f_current_user' ];
				delete_user_meta($current_user->ID,'mo_2factor_user_email');
				delete_user_meta($current_user->ID,'mo_2fa_verify_otp_create_account');
				delete_user_meta($current_user->ID, 'mo_2factor_user_registration_status');
				$_SESSION['mo2f-login-message'] = '';
				$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_FOR_USER_REGISTRATION';
			}
		}
		
	}
	
	
	
	function mo2f_check_username_password($user, $username, $password){
		
		if (isset($_POST['miniorange_login_nonce'])){	
			$nonce = $_POST['miniorange_login_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {	
				wp_logout();
				$error = new WP_Error();
				$error->add('empty_username', __('<strong>ERROR</strong>: Invalid Request.'));
				return $error;
			}
			else { 
				$currentuser = mo2f_wp_authenticate_username_password($user, $username, $password);
				if (is_wp_error($currentuser)) {
					return $currentuser;
				}else{
					if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
						session_start();
					}
					$_SESSION[ 'mo2f_current_user' ] = $currentuser;
					$_SESSION[ 'mo2f_1stfactor_status' ] = 'VALIDATE_SUCCESS';
					$roles = $currentuser->roles;
					$current_role = array_shift($roles);
					if(get_option('mo2fa_'.$current_role)){
						if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
							session_start();
						}
						$email = get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true);
						$attributes = isset($_POST[ 'miniorange_rba_attribures' ]) ? $_POST[ 'miniorange_rba_attribures' ] : null;
						if( $email && get_user_meta($currentuser->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){ 
							//checking if user has configured any 2nd factor method
							try{
								$mo2f_rba_status = mo2f_collect_attributes($email,stripslashes($attributes)); // Rba flow
							}catch(Exception $e){
								echo $e->getMessage();
							}
							
							if($mo2f_rba_status['status'] == 'SUCCESS' && $mo2f_rba_status['decision_flag']){
								$this->mo2fa_pass2login();
							}else{
								$_SESSION['mo2f_rba_status'] = $mo2f_rba_status;
								
								$mo2f_second_factor = mo2f_get_user_2ndfactor($currentuser);
								if($mo2f_second_factor == 'MOBILE AUTHENTICATION'){
									$this->mo2f_pass2login_mobile_verification($currentuser);
								}else if($mo2f_second_factor == 'PUSH NOTIFICATIONS' || $mo2f_second_factor == 'OUT OF BAND EMAIL'){
									$this->mo2f_pass2login_push_oobemail_verification($currentuser,$mo2f_second_factor);
								}else if($mo2f_second_factor == 'SOFT TOKEN' || $mo2f_second_factor == 'SMS' || $mo2f_second_factor == 'PHONE VERIFICATION' || $mo2f_second_factor == 'GOOGLE AUTHENTICATOR' ){
									$this->mo2f_pass2login_otp_verification($currentuser,$mo2f_second_factor);
								}else if($mo2f_second_factor == 'KBA'){
										$this->mo2f_pass2login_kba_verification($currentuser);
								}else{
									$this->remove_current_activity();
									$error = new WP_Error();
									$error->add('empty_username', __('<strong>ERROR</strong>: Please try again or contact your admin.'));
									return $error;
								}
							}
						}else{
							$_SESSION['mo2f-login-message'] = '';
							if( get_user_meta($currentuser->ID,'mo_2factor_user_registration_status',true) =='MO_2_FACTOR_INITIALIZE_TWO_FACTOR'){
								$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
							}else{
								$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_FOR_USER_REGISTRATION';
							}
						}	
					}else{ //plugin is not activated for current role then logged him in without asking 2 factor
						$this->mo2fa_pass2login();
					}
				}
			}
		}else{
			$error = new WP_Error();
			return $error;
		}
	}
	function mo_2_factor_enable_jquery() {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap_script', plugins_url('includes/js/bootstrap.min.js', __FILE__ ));
		wp_enqueue_script( 'mo_2_factor_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
	}
	
	
	function mo_2_factor_pass2login_hide_login() {
		wp_register_style( 'hide-login', plugins_url( 'includes/css/hide-login.css?version=3.4', __FILE__ ) );
		wp_enqueue_style( 'hide-login' );
		wp_register_style( 'bootstrap', plugins_url( 'includes/css/bootstrap.min.css?version=3.4', __FILE__ ) );
		wp_enqueue_style( 'bootstrap' );
		wp_register_style( 'mo-country-code', plugins_url('includes/css/phone.css', __FILE__));
		wp_enqueue_style( 'mo-country-code' );
	}
	
	function mo_2_factor_pass2login_show_login(){
		wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css?version=3.4', __FILE__ ) );
		wp_enqueue_style( 'show-login' );
	}
	
	function miniorange_pass2login_header_field(){
	?>
	<script>
		var relPath = '<?php echo plugins_url('includes/js/rba/js', __FILE__); ?>';
	</script>
	<?php
	}
	
	function miniorange_pass2login_form_fields(){
		
		$login_status = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : null;
		$current_user = isset($_SESSION[ 'mo2f_current_user' ]) ? $_SESSION[ 'mo2f_current_user' ]->ID : null;
		if($this->miniorange_pass2login_check_mobile_status($login_status)){ //for mobile
			$this->mo_2_factor_pass2login_hide_login();
			$this->mo_2_factor_pass2login_show_qr_code();
		}else if($this->miniorange_pass2login_check_otp_status($login_status)){ //for soft-token,otp over email,sms,phone verification
			$this->mo_2_factor_pass2login_hide_login();
			$this->mo_2_factor_pass2login_show_otp_token();
		}else if($this->miniorange_pass2login_check_push_oobemail_status($login_status)){ //for push and out of band email.
			$this->mo_2_factor_pass2login_hide_login();
			$this->mo_2_factor_pass2login_show_push_oobemail();
		}else if($this->miniorange_pass2login_check_kba_status($login_status)){ // for Kba 
			$this->mo_2_factor_pass2login_hide_login();
			$this->mo_2_factor_pass2login_show_kba();
		}else if($this->miniorange_pass2login_check_trusted_device_status($login_status)){ // trusted device
			$this->mo_2_factor_pass2login_hide_login();
			$this->mo_2_factor_pass2login_show_device_page();
		}else if($this->miniorange_pass2login_check_inline_user_registration($login_status)){ // inline registration started
			$this->mo_2_factor_pass2login_hide_login();
			prompt_user_to_register();
		}else if($this->miniorange_pass2login_check_inline_user_otp($login_status)){ //otp verification after user enter email during inline registration
			$this->mo_2_factor_pass2login_hide_login();
			prompt_user_for_validate_otp();
		}else if($this->miniorange_pass2login_check_inline_user_2fa_methods($login_status)){ // two-factor methods
			$this->mo_2_factor_pass2login_hide_login();
			$opt = (array) get_option('mo2f_auth_methods_for_users');
			if (sizeof($opt) > 1) {
				
				prompt_user_to_select_2factor_method($current_user);
				
			}else if( in_array("SMS", $opt) || in_array("PHONE VERIFICATION", $opt) ){
				
				prompt_user_for_phone_setup($current_user);
				
			}else if( in_array("SOFT_TOKEN", $opt) || in_array("PUSH NOTIFICATION", $opt) || in_array("MOBILE AUTHENTICATION", $opt)  ){
				
				prompt_user_for_miniorange_app_setup($current_user);
				
			}else if( in_array("GOOGLE AUTHENTICATOR", $opt) ){
				
				prompt_user_for_google_authenticator_setup($current_user);
				
			}else if( in_array("KBA", $opt) ){
				
				prompt_user_for_kba_setup($current_user);
				
			}else{
				prompt_user_for_email_setup($current_user);
			}
			
		}else{ //show login screen
			$this->mo_2_factor_pass2login_show_login();
			$this->mo_2_factor_pass2login_show_wp_login_form();
		}
	}
	
	//woocommerce front end login
	function miniorange_pass2login_form_fields_frontend(){
		$login_status = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : null;
		$current_user = isset($_SESSION[ 'mo2f_current_user' ]) ? $_SESSION[ 'mo2f_current_user' ]->ID : null;
		if($this->miniorange_pass2login_check_mobile_status($login_status)){ //for mobile
			mo2f_frontend_getqrcode();
		}else if($this->miniorange_pass2login_check_otp_status($login_status)){ //for soft-token,otp over email,sms,phone verification
			mo2f_frontend_getotp_form();
		}else if($this->miniorange_pass2login_check_push_oobemail_status($login_status)){ //for push and out of band email.
			mo2f_frontend_getpush_oobemail_response();
		}else if($this->miniorange_pass2login_check_kba_status($login_status)){ // for Kba 
			$this->mo2f_frontend_get_kba_form();
		}else if($this->miniorange_pass2login_check_trusted_device_status($login_status)){
			mo2f_frontend_get_trusted_device_form();
		}else if($this->miniorange_pass2login_check_inline_user_registration($login_status)){
			$this->mo_2_factor_pass2login_hide_login();
			prompt_user_to_register_frontend();
		}else if($this->miniorange_pass2login_check_inline_user_otp($login_status)){
			$this->mo_2_factor_pass2login_hide_login();
			prompt_user_for_validate_otp_frontend();
		}else if($this->miniorange_pass2login_check_inline_user_2fa_methods($login_status)){
			$this->mo_2_factor_pass2login_hide_login();
			$opt = (array) get_option('mo2f_auth_methods_for_users');
			if (sizeof($opt) > 1) {
				
				prompt_user_to_select_2factor_method_frontend($current_user);
				
			}else if( in_array("SMS", $opt) || in_array("PHONE VERIFICATION", $opt) ){
				
				prompt_user_for_phone_setup_frontend($current_user);
				
			}else if( in_array("SOFT_TOKEN", $opt) || in_array("PUSH NOTIFICATION", $opt) || in_array("MOBILE AUTHENTICATION", $opt)  ){
				
				prompt_user_for_miniorange_app_setup_frontend($current_user);
				
			}else if( in_array("GOOGLE AUTHENTICATOR", $opt) ){
				
				prompt_user_for_google_authenticator_setup_frontend($current_user);
				
			}else if( in_array("KBA", $opt) ){
				
				prompt_user_for_kba_setup_frontend($current_user);
				
			}else{
				prompt_user_for_email_setup_frontend($current_user);
			}
		}	
	}
	
	function miniorange_pass2login_check_inline_user_2fa_methods($login_status){
		       
		if($login_status == 'MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS'){
			$nonce = '';
			if(isset($_POST['miniorange_inline_validate_user_otp_nonce']) ){
				$nonce = $_POST['miniorange_inline_validate_user_otp_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-user-otp-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_two_factor_setup'])){
				$nonce = $_POST['miniorange_inline_two_factor_setup'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-inline-setup-nonce')){
					return true;
				}
			}else if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_save_2factor_method_nonce']) ){
				$nonce = $_POST['miniorange_inline_save_2factor_method_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-2factor-method-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_verify_phone_nonce'])){
				$nonce = $_POST['miniorange_inline_verify_phone_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-verify-phone-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_resend_otp_nonce'])){
				$nonce = $_POST['miniorange_inline_resend_otp_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-resend-otp-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_validate_otp_nonce'])){
				$nonce = $_POST['miniorange_inline_validate_otp_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-otp-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_show_qrcode_nonce'])){
				$nonce = $_POST['miniorange_inline_show_qrcode_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-show-qrcode-nonce' )){
					return true;
				}
			}else if(isset($_POST['mo2f_inline_ga_phone_type_nonce'])){
				$nonce = $_POST['mo2f_inline_ga_phone_type_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-ga-phone-type-nonce' )){
					return true;
				}
			}else if(isset($_POST['mo2f_inline_validate_ga_nonce'])){
				$nonce = $_POST['mo2f_inline_validate_ga_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-google-auth-nonce' )){
					return true;
				}
			}else if(isset($_POST['mo2f_inline_save_kba_nonce'])){
				$nonce = $_POST['mo2f_inline_save_kba_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-save-kba-nonce' )){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_pass2login_check_inline_user_otp($login_status){
		
		if($login_status == 'MO_2_FACTOR_PROMPT_FOR_USER_REG_OTP'){
			$nonce = '';
			if(isset($_POST['miniorange_inline_user_reg_nonce']) ){
				$nonce = $_POST['miniorange_inline_user_reg_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-user-reg-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_validate_user_otp_nonce']) ){
				$nonce = $_POST['miniorange_inline_validate_user_otp_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-validate-user-otp-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_resend_otp_nonce']) ){
				$nonce = $_POST['miniorange_inline_resend_otp_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-resend-otp-nonce' )){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_pass2login_check_inline_user_registration($login_status){
		if($login_status == 'MO_2_FACTOR_PROMPT_FOR_USER_REGISTRATION'){
			$nonce = '';
			
			if(isset($_POST['miniorange_login_nonce']) ){                          
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_goto_user_registration_nonce'])){
				$nonce = $_POST['miniorange_inline_goto_user_registration_nonce'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-inline-goto-user-registration-nonce')){
					return true;
				}
			}else if(isset($_POST['miniorange_inline_user_reg_nonce']) ){
				$nonce = $_POST['miniorange_inline_user_reg_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-inline-user-reg-nonce' )){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_pass2login_check_trusted_device_status($login_status){
		
		if($login_status == 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE'){
			$nonce = '';
			if(isset($_POST['miniorange_soft_token_nonce'])){
				$nonce = $_POST['miniorange_soft_token_nonce'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-soft-token-nonce')){
					return true;
				}
			}else if(isset($_POST['miniorange_mobile_validation_nonce'])){
				$nonce = $_POST['miniorange_mobile_validation_nonce'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-mobile-validation-nonce')){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_pass2login_check_push_oobemail_status($login_status){
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
	
	function miniorange_pass2login_check_otp_status($login_status){
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
		}
		return false;
	}
	
	function miniorange_pass2login_check_mobile_status($login_status){
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
	
	function miniorange_pass2login_check_kba_status($login_status){
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
	
	function miniorange_pass2login_footer_form(){
	
		if(isset($_SESSION[ 'mo_2factor_login_status' ])){  //show these forms after default login form
	?>
		<form name="f" id="mo2f_show_softtoken_loginform" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_softtoken" value="<?php echo wp_create_nonce('miniorange-2-factor-softtoken'); ?>" />
		</form>
		<form name="f" id="mo2f_show_forgotphone_loginform" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_forgotphone" value="<?php echo wp_create_nonce('miniorange-2-factor-forgotphone'); ?>" />
		</form>
		<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo wp_login_url(); ?>" style="display:none;">
			<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce'); ?>" />
		</form>
		<?php if(get_option('mo2f_enable_2fa_for_woocommerce') == 1) { ?>
		<form name="f" id="mo2f_2fa_form_close" method="post" style="display:none;">
			<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce'); ?>" />
		</form>
		<?php }
		}
		if(isset($_SESSION[ 'mo_2factor_login_status' ]) && ($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION' ||  $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL' || $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' )){ //show this form when 2nd factor is mobile,email verification,push 
		?>
		<form name="f" id="mo2f_mobile_validation_form" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_mobile_validation_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-nonce'); ?>" />
		</form>
		<?php
		}
		if(isset($_SESSION[ 'mo_2factor_login_status' ]) && ($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' ||  $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' || $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' || $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' || $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION')){ //show this form when 2nd factor is otp over email(forgot phone),otp over sms,phone verification,soft token,google authenticator
		?>
		<form name="f" id="mo2f_submitotp_loginform" method="post" action="" style="display:none;"> 
			<input type="text" name="mo2fa_softtoken" id="mo2fa_softtoken" hidden/>
			<input type="hidden" name="miniorange_soft_token_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-soft-token-nonce'); ?>" />
		</form>
		<?php 
		}
		if(isset($_SESSION[ 'mo_2factor_login_status' ]) && ($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_KBA_AUTHENTICATION')){ //show this form only when 2nd factor is KBA
		?>
			<form name="f" id="mo2f_submitkba_loginform" method="post" action="" style="display:none;"> 
				<input type="text" name="mo2f_answer_1" id="mo2f_answer_1" hidden />
				<input type="text" name="mo2f_answer_2" id="mo2f_answer_1" hidden />
				<input type="text" name="mo2f_trust_device" id="mo2f_trust_device" hidden />
				<input type="hidden" name="miniorange_kba_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-kba-nonce'); ?>" />
			</form>
		<?php
		}
			if(get_option('mo2f_deviceid_enabled') && get_option('mo2f_login_policy')){ //show this form and script only rba is on
				if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_REMEMBER_TRUSTED_DEVICE' ){ //show this form only when rba is on and device is not trusted.
		?>
			
			<form name="f" id="mo2f_trust_device_confirm_form" method="post" action="" style="display:none;">
				<input type="hidden" name="mo2f_trust_device_confirm_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-trust-device-confirm-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_trust_device_cancel_form" method="post" action="" style="display:none;">
				<input type="hidden" name="mo2f_trust_device_cancel_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-trust-device-cancel-nonce'); ?>" />
			</form>
			<?php
			}
			?>
		
			<script>
			jQuery(document).ready(function(){
				if(document.getElementById('loginform') != null){
					 jQuery('#loginform').on('submit', function(e){
						jQuery('#miniorange_rba_attribures').val(JSON.stringify(rbaAttributes.attributes));
					});
				}else{
					if(document.getElementsByClassName('login') != null){
						jQuery('.login').on('submit', function(e){
							jQuery('#miniorange_rba_attribures').val(JSON.stringify(rbaAttributes.attributes));
						});
					}
				}
			});
			</script>
	<?php  }
		?>
		<form name="f" id="mo2f_inline_register_user_form" method="post" action="" style="display:none;"> 
			<input type="text" name="mo_useremail" id="mo2fa_user_email" hidden/>
			<input type="hidden" name="miniorange_inline_user_reg_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-user-reg-nonce'); ?>" />
		</form>
		<form name="f" id="mo2f_inline_register_skip_form" method="post" style="display:none;">
			<input type="hidden" name="miniorange_inline_skip_registration_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-skip-registration-nonce'); ?>" />
		</form>
		<form name="f" id="mo2f_goto_user_registration_form" method="post" action="" style="display:none;">
			<input type="hidden" name="miniorange_inline_goto_user_registration_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-goto-user-registration-nonce'); ?>" />
		</form>
		<form name="f" id="mo2f_inline_user_validate_otp_form" method="post" action="" style="display:none;"> 
			<input type="hidden" name="otp_token" />
			<input type="hidden" name="miniorange_inline_validate_user_otp_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-validate-user-otp-nonce'); ?>" />
			
		</form>
		<form name="f" method="post" action="" id="mo2fa_inline_resend_otp_form" style="display:none;">
			<input type="hidden" name="miniorange_inline_resend_otp_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-resend-otp-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_select_2fa_methods_form" style="display:none;">
			<input type="hidden" name="mo2f_selected_2factor_method" />
			<input type="hidden" name="miniorange_inline_save_2factor_method_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-save-2factor-method-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_inline_verifyphone_form" style="display:none;">
			<input type="hidden" name="verify_phone"  />
			<input type="hidden" name="miniorange_inline_verify_phone_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-verify-phone-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_inline_validateotp_form" style="display:none;">
			<input type="hidden" name="otp_token" />
			<input type="hidden" name="miniorange_inline_validate_otp_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-validate-otp-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_inline_configureapp_form" style="display:none;">
			<input type="hidden" name="miniorange_inline_show_qrcode_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-show-qrcode-nonce'); ?>" />
		</form>
		<form name="f" method="post" id="mo2f_inline_mobile_register_form" action="" style="display:none;">
			<input type="hidden" name="mo_auth_inline_mobile_registration_complete_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-mobile-registration-complete-nonce'); ?>" />
		</form>
		<form name="f" method="post" id="mo2f_inline_save_kba_form" action="" style="display:none;">
			<input type="text" name="mo2f_kbaquestion_1" id="mo2f_kbaquestion_1" hidden />
			<input type="text" name="mo2f_kba_ans1" id="mo2f_kba_ans1" hidden />
			<input type="text" name="mo2f_kbaquestion_2" id="mo2f_kbaquestion_2" hidden />
			<input type="text" name="mo2f_kba_ans2" id="mo2f_kba_ans2" hidden />
			<input type="text" name="mo2f_kbaquestion_3" id="mo2f_kbaquestion_3" hidden />
			<input type="text" name="mo2f_kba_ans3" id="mo2f_kba_ans3" hidden />
			<input type="hidden" name="mo2f_inline_save_kba_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-save-kba-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_goto_two_factor_form" style="display:none;">
			<input type="hidden" name="miniorange_inline_two_factor_setup" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-setup-nonce'); ?>" />
		</form>
		<form name="f" method="post" id="mo2f_inline_app_type_ga_form" action="" style="display:none;">
			<input type="hidden" name="google_phone_type" />
			<input type="hidden" name="mo2f_inline_ga_phone_type_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-ga-phone-type-nonce'); ?>" />
		</form>
		<form name="" method="post" id="mo2f_inline_verify_ga_code_form" style="display:none;">
			<input type="hidden" name="google_auth_code" />
			<input type="hidden" name="mo2f_inline_validate_ga_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-inline-google-auth-nonce'); ?>" />
		</form>
		<form name="f" method="post" action="" id="mo2f_inline_email_form" style="display:none;">
			<input type="hidden" name="mo2f_inline_email_setup" value="<?php echo wp_create_nonce('miniorange-2fa-inline-email-nonce'); ?>" />
		</form>
	<?php		
	}
	
	function mo2f_pass2login_otp_verification($user,$mo2f_second_factor){
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
	}
	
	function mo2f_pass2login_push_oobemail_verification($user,$mo2f_second_factor){
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
	
	function mo2f_pass2login_kba_verification($user){
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
	
	function mo2f_pass2login_mobile_verification($user){
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
		
	}
	
	function mo_2_factor_pass2login_show_wp_login_form(){
	?>
		<p><input type="hidden" name="miniorange_login_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-login-nonce'); ?>" /></p>
		<?php 
			if(get_option('mo2f_deviceid_enabled')){
		?>
				<p><input type="hidden" id="miniorange_rba_attribures" name="miniorange_rba_attribures" value="" /></p>
		<?php
				wp_enqueue_script( 'jquery_script', plugins_url('includes/js/rba/js/jquery-1.9.1.js', __FILE__ ));
				wp_enqueue_script( 'flash_script', plugins_url('includes/js/rba/js/jquery.flash.js', __FILE__ ));
				wp_enqueue_script( 'uaparser_script', plugins_url('includes/js/rba/js/ua-parser.js', __FILE__ ));
				wp_enqueue_script( 'client_script', plugins_url('includes/js/rba/js/client.js', __FILE__ ));
				wp_enqueue_script( 'device_script', plugins_url('includes/js/rba/js/device_attributes.js', __FILE__ ));
				wp_enqueue_script( 'swf_script', plugins_url('includes/js/rba/js/swfobject.js', __FILE__ ));
				wp_enqueue_script( 'font_script', plugins_url('includes/js/rba/js/fontdetect.js', __FILE__ ));
				wp_enqueue_script( 'murmur_script', plugins_url('includes/js/rba/js/murmurhash3.js', __FILE__ ));
				wp_enqueue_script( 'miniorange_script', plugins_url('includes/js/rba/js/miniorange-fp.js', __FILE__ ));
			}
	}
	
	function mo2f_register_user_inline($email){
		
		$enduser = new Two_Factor_Setup();
		$check_user = json_decode($enduser->mo_check_user_already_exist($email),true);
		$currentUserId = $_SESSION[ 'mo2f_current_user' ]->ID;
		$current_user = $_SESSION[ 'mo2f_current_user' ];
		if(json_last_error() == JSON_ERROR_NONE){
			if($check_user['status'] == 'ERROR'){
				$_SESSION['mo2f-login-message'] = $check_user['message'];
				
			}else{
				if(strcasecmp($check_user['status'], 'USER_FOUND') == 0){
					
					delete_user_meta($currentUserId,'mo_2factor_user_email');
					update_user_meta($currentUserId,'mo_2factor_user_registration_with_miniorange','SUCCESS');
					update_user_meta($currentUserId,'mo_2factor_map_id_with_email',$email);
					update_user_meta($currentUserId,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_TWO_FACTOR');
					$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
					$message = '';
					$_SESSION['mo2f-login-message'] = $message;
					
					
				}else if(strcasecmp($check_user['status'], 'USER_NOT_FOUND') == 0){
					$content = json_decode($enduser->mo_create_user($current_user,$email), true);
						if(json_last_error() == JSON_ERROR_NONE) {
							if($content['status'] == 'ERROR'){
								$_SESSION['mo2f-login-message'] = $content['message'];
							}else{
								if(strcasecmp($content['status'], 'SUCCESS') == 0) {
									delete_user_meta($currentUserId,'mo_2factor_user_email');
									update_user_meta($currentUserId,'mo_2factor_user_registration_with_miniorange','SUCCESS');
									update_user_meta($currentUserId,'mo_2factor_map_id_with_email',$email);
									update_user_meta($currentUserId,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_TWO_FACTOR');
									$enduser->mo2f_update_userinfo(get_user_meta($currentUserId,'mo_2factor_map_id_with_email',true), 'OUT OF BAND EMAIL',null,null,null);
									$message = '';
									$_SESSION['mo2f-login-message'] = $message;
									
									$_SESSION[ 'mo_2factor_login_status' ] ='MO_2_FACTOR_PROMPT_USER_FOR_2FA_METHODS';
									
								}else{
									$_SESSION['mo2f-login-message'] = 'Error occurred while registering the user. Please try again.';
									
								}
							}
						}else{
								$_SESSION['mo2f-login-message'] = 'Error occurred while registering the user. Please try again or contact your admin.';
								
						}
				}else{
					$_SESSION['mo2f-login-message'] = 'Error occurred while registering the user. Please try again.';
					
				}
			}
		}else{
			$_SESSION['mo2f-login-message'] = 'Error occurred while registering the user. Please try again.';
			
		}
	}
	
	function mo2f_inline_get_qr_code_for_mobile($email,$id){
		$registerMobile = new Two_Factor_Setup();
		$content = $registerMobile->register_mobile($email);
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) {
			if($response['status'] == 'ERROR'){
				$_SESSION['mo2f-login-message'] =  $response['message'];
				unset($_SESSION[ 'mo2f-login-qrCode' ]);
				unset($_SESSION[ 'mo2f-login-transactionId' ]);
				unset($_SESSION[ 'mo2f_show_qr_code']);
			}else{
				if($response['status'] == 'IN_PROGRESS'){
					
					$_SESSION[ 'mo2f-login-qrCode' ] = $response['qrCode'];
					$_SESSION[ 'mo2f-login-transactionId' ] = $response['txId'];
					$_SESSION[ 'mo2f_show_qr_code'] = 'MO_2_FACTOR_SHOW_QR_CODE';
				}else{
					$_SESSION['mo2f-login-message'] =  "An error occured while processing your request. Please Try again.";
					unset($_SESSION[ 'mo2f-login-qrCode' ]);
					unset($_SESSION[ 'mo2f-login-transactionId' ]);
					unset($_SESSION[ 'mo2f_show_qr_code']);
				}
			}
		}
	}
	
	function mo_2_factor_pass2login_show_qr_code(){ //for mobile authentication
		mo2f_getqrcode();
	}
	
	function mo_2_factor_pass2login_show_otp_token(){ //for soft token,sms,email(forgot phone),phone verification
		mo2f_getotp_form();
	}
	
	function mo_2_factor_pass2login_show_push_oobemail(){ //for push notification and out of band email
		mo2f_getpush_oobemail_response();
	}
	
	function mo_2_factor_pass2login_show_device_page(){
		mo2f_get_device_form();
	}
	
	function mo_2_factor_pass2login_show_kba(){
		mo2f_getkba_form();
	}
}
?>