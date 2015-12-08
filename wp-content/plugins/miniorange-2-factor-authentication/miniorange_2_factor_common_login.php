<?php 
	
	function mo2f_wp_authenticate_username_password($user, $username, $password) {
		if ( is_a($user, 'WP_User') ) { return $user; }

		if ( empty($username) || empty($password) ) {
			$error = new WP_Error();

			if ( empty($username) )
				$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

			if ( empty($password) )
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

			return $error;
		}

		$userdata = get_user_by('login', $username);

		if ( !$userdata )
			return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()));

		if ( is_multisite() ) {
			// Is user marked as spam?
			if ( 1 == $userdata->spam)
				return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));

			// Is a user's blog marked as spam?
			if ( !is_super_admin( $userdata->ID ) && isset($userdata->primary_blog) ) {
				$details = get_blog_details( $userdata->primary_blog );
				if ( is_object( $details ) && $details->spam == 1 )
					return new WP_Error('blog_suspended', __('Site Suspended.'));
			}
		}

		$userdata = apply_filters('wp_authenticate_user', $userdata, $password);
		if ( is_wp_error($userdata) )
			return $userdata;

		if ( !wp_check_password($password, $userdata->user_pass, $userdata->ID) )
			return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
			$username, wp_lostpassword_url() ) );

		$user =  new WP_User($userdata->ID);
		return $user;
	}
	
	function redirect_user_to($user){
		if(!strcasecmp(wp_sprintf_l( '%l', $user->roles ),'administrator')){
			if(get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_user_meta($user->ID,'mo_2factor_user_registration_status',true) != 'MO_2_FACTOR_PLUGIN_SETTINGS'){
				wp_redirect( admin_url().'admin.php?page=miniOrange_2_factor_settings');
			}else{
				wp_redirect( admin_url() );
			}
		}else{
			wp_redirect( home_url());
		}
	}
	
	function mo2f_register_profile($email,$deviceKey,$mo2f_rba_status){
		
		if(isset($deviceKey) && $deviceKey == 'true'){
			if($mo2f_rba_status['status'] == 'WAIT_FOR_INPUT' && $mo2f_rba_status['decision_flag']){
				$rba_profile = new Miniorange_Rba_Attributes();
				$rba_response = json_decode($rba_profile->mo2f_register_rba_profile($email,$mo2f_rba_status['sessionUuid']),true); //register profile
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
	
	function mo2f_collect_attributes($email,$attributes){
		if(get_option('mo2f_deviceid_enabled')){
			$rba_attributes = new Miniorange_Rba_Attributes();
			$rba_response = json_decode($rba_attributes->mo2f_collect_attributes($email,$attributes),true); //collect rba attributes
			if(json_last_error() == JSON_ERROR_NONE){
				if($rba_response['status'] == 'SUCCESS'){ //attribute are collected successfully
					$sessionUuid = $rba_response['sessionUuid'];
					$rba_risk_response = json_decode($rba_attributes->mo2f_evaluate_risk($email,$sessionUuid),true); // evaluate the rba risk
					if(json_last_error() == JSON_ERROR_NONE){
						if($rba_risk_response['status'] == 'SUCCESS' || $rba_risk_response['status'] == 'WAIT_FOR_INPUT'){ 
							$mo2f_rba_status = array();
							$mo2f_rba_status['status'] = $rba_risk_response['status'];
							$mo2f_rba_status['sessionUuid'] = $sessionUuid;
							$mo2f_rba_status['decision_flag'] = true;
							return $mo2f_rba_status;
						}else{
							$mo2f_rba_status = array();
							$mo2f_rba_status['status'] = $rba_risk_response['status'];
							$mo2f_rba_status['sessionUuid'] = $sessionUuid;
							$mo2f_rba_status['decision_flag'] = false;
							return $mo2f_rba_status;
						}
					}else{
						$mo2f_rba_status = array();
						$mo2f_rba_status['status'] = 'JSON_EVALUATE_ERROR';
						$mo2f_rba_status['sessionUuid'] = $sessionUuid;
						$mo2f_rba_status['decision_flag'] = false;
						return $mo2f_rba_status;
					}
				}else{
					$mo2f_rba_status = array();
					$mo2f_rba_status['status'] = 'ATTR_NOT_COLLECTED';
					$mo2f_rba_status['sessionUuid'] = '';
					$mo2f_rba_status['decision_flag'] = false;
					return $mo2f_rba_status;
				}
			}else{
				$mo2f_rba_status = array();
				$mo2f_rba_status['status'] = 'JSON_ATTR_NOT_COLLECTED';
				$mo2f_rba_status['sessionUuid'] = '';
				$mo2f_rba_status['decision_flag'] = false;
				return $mo2f_rba_status;
			}
		}else{
			$mo2f_rba_status = array();
			$mo2f_rba_status['status'] = 'RBA_NOT_ENABLED';
			$mo2f_rba_status['sessionUuid'] = '';
			$mo2f_rba_status['decision_flag'] = false;
			return $mo2f_rba_status;
		}
	}
	
	function mo2f_get_user_2ndfactor($current_user){
		if(get_user_meta($current_user->ID,'mo_2factor_mobile_registration_status',true) == 'MO_2_FACTOR_SUCCESS'){
			$mo2f_second_factor = 'MOBILE AUTHENTICATION';
		}else{
			$enduser = new Two_Factor_Setup();
			$userinfo = json_decode($enduser->mo2f_get_userinfo(get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true)),true);
			if(json_last_error() == JSON_ERROR_NONE){
				if($userinfo['status'] == 'ERROR'){
					$mo2f_second_factor = 'NONE';
				}else if($userinfo['status'] == 'SUCCESS'){
					$mo2f_second_factor = $userinfo['authType'];
				}else if($userinfo['status'] == 'FAILED'){
					$mo2f_second_factor = 'USER_NOT_FOUND';
				}else{
					$mo2f_second_factor = 'NONE';
				}
			}else{
				$mo2f_second_factor = 'NONE';
			}
		}
		return $mo2f_second_factor;
	}
	
	function mo2f_getkba_form(){
	?>
		<div class="miniorange_kba_page">
			<center>
			<div id="mo_2_factor_kba_page" class="miniorange-inner-kba-login-container">
				<span><h2 class="mo_header_background">Validate Security Questions</h2></span>
				<div id="kbaSection" style="padding:30px;">
					
					<div id="mo_kba_title" style="padding-bottom:20px;">
						<h3><?php echo isset($_SESSION['mo2f-login-message']) ? $_SESSION['mo2f-login-message'] : 'Please answer the following questions:'; ?></h3>
					</div>
					<div id="mo2f_kba_content" style="text-align:left">
						<h4><?php if(isset($_SESSION['mo_2_factor_kba_questions'])){
							echo $_SESSION['mo_2_factor_kba_questions'][0];
						?></h4>
						<input  type="text" name="mo2f_answer_1" id="mo2f_answer_1" required="true" autofocus="true" pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+-\s]{1,100}" title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed." class="mo2f_kba_textbox">
						<h4><?php
							echo $_SESSION['mo_2_factor_kba_questions'][1];
						?></h4>
						<input class="mo2f_kba_textbox" type="text" name="mo2f_answer_2" id="mo2f_answer_2" required="true" pattern="(?=\S)[A-Za-z0-9_@.$#&amp;+-\s]{1,100}" title="Only alphanumeric letters with special characters(_@.$#&amp;+-) are allowed.">
						<?php 
							}
						?>
					</div>
					<div>
						<?php if(get_option('mo2f_login_policy')){
								if(get_option('mo2f_deviceid_enabled')){
						?>
							<span style="padding-right:80px;"><input type="checkbox" name="miniorange_remember_device" id="miniorange_remember_device" />Remember this device.</span>
						<?php 
								}else{
						?>
							<input type="checkbox" name="miniorange_remember_device" id="miniorange_remember_device" style="display:none;" />
						<?php
								}
							}else{
						?>
							<input type="checkbox" name="miniorange_remember_device" id="miniorange_remember_device" style="display:none;" />
						<?php
							}
						?>
						<input type="button" name="miniorange_kba_validate" onclick="mo2f_validate_kba();" id="miniorange_kba_validate" class="miniorange-button"  style="float:right;" value="Validate" />
						
						<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
					</div>
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange" style="background-image: url('<?php if(get_option('mo2f_enable_custom_poweredby')==1) echo site_url().'/wp-content/uploads/custom.png'; else echo plugins_url('/includes/images/miniOrange2.png',__FILE__); ?>');"></div></a></div>
				<?php }?>
			</div>
			</center>
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_kba_page'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			function mo2f_validate_kba(){
				var ans1 = jQuery('#mo2f_answer_1').val();
				var ans2 = jQuery('#mo2f_answer_2').val();
				var check = jQuery('#miniorange_remember_device').prop('checked');
				document.getElementById("mo2f_submitkba_loginform").elements[0].value = ans1;
				document.getElementById("mo2f_submitkba_loginform").elements[1].value = ans2;
				document.getElementById("mo2f_submitkba_loginform").elements[2].value = check;
				jQuery('#mo2f_submitkba_loginform').submit();
			}
			
			jQuery('#mo2f_answer_2').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					mo2f_validate_kba();
				  } 
			});
		</script>
	<?php
	}
	
	function mo2f_getpush_oobemail_response(){
	?>
		<div class="miniorange_push_oobemail_auth">
			<center>
			<div class="mo2fa_push_messages_container" id="otpMessage" > 
				<p class='mo2fa_display_message'><?php echo $_SESSION['mo2f-login-message']; ?></p>
			</div>
			</center><br/>
			
			<div id="mo_2_factor_push_page" class="miniorange-inner-push-login-container">
				<div id="pushSection">
					<br>
					<center><a href="#showPushHelp" id="pushHelpLink"><h3>See How It Works ?</h3></a></center>
					<div style="margin-bottom:10%;padding-top:6%;">
					<center>
					<h3>Waiting for your approval...</h3>
					</center>
					</div>
						
					<div id="showPushImage" style="margin-bottom:10%;">
					<center> 
						<img src="<?php echo plugins_url( 'includes/images/ajax-loader-login.gif' , __FILE__ );?>" />
					</center>
					</div>
					
					<span style="padding-right:2%;">
					<?php if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS'){ ?>
					<center>
						<?php if(get_option('mo2f_enable_forgotphone')){ ?>
						<input type="button" name="miniorange_login_forgotphone" onclick="mologinforgotphone();" id="miniorange_login_forgotphone" class="miniorange-button" value="Forgot Phone?" />
						<?php } ?>
						
						<input type="button" name="miniorange_login_offline" onclick="mologinoffline();" id="miniorange_login_offline" class="miniorange-button" value="Phone is Offline?" /></center>
					
					<?php } ?>
					</span>
						<div><center><input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" /></center></div>
					<br />
				
				</div>
				<div id="showPushHelp" class="showPushHelp" hidden>
					<br>
					<center><a href="#showPushHelp" id="pushLink"><h3>←Go Back.</h3></a>
					<br>
						<div id="myCarousel" class="carousel slide" data-ride="carousel">
						  <ol class="carousel-indicators">
							<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
							<li data-target="#myCarousel" data-slide-to="1"></li>
							<li data-target="#myCarousel" data-slide-to="2"></li>
						</ol>
						<div class="carousel-inner" role="listbox">
							<?php  if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL') { ?>
									<div class="item active">
								
							  <img class="first-slide" src="http://miniorange.com/images/help/email-with-link-login-flow-1.png" alt="First slide">
							</div>
						   <div class="item">
							<p>Click on Accept Transaction link to verify your email .</p><br>
							<img class="first-slide" src="http://miniorange.com/images/help/email-with-link-login-flow-2.png" alt="First slide">
						 
						  </div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/email-with-link-login-flow-3.png" alt="First slide">
						  </div>
							<?php } else {	?>
						  <!-- Indicators -->
						 
						
							<div class="item active">
								<p>You will receive a notification on your phone.</p><br>
							  <img class="first-slide" src="http://miniorange.com/images/help/push-login-flow.png" alt="First slide">
							</div>
						   <div class="item">
							<p>Open the notification and click on accept button.</p><br>
							<img class="first-slide" src="http://miniorange.com/images/help/push-login-flow-1.png" alt="First slide">
						 
						  </div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/push-login-flow-2.png" alt="First slide">
						  </div>
						 	<?php } ?>
						</div>
						</div>
					</center>
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange" style="background-image: url('<?php if(get_option('mo2f_enable_custom_poweredby')==1) echo site_url().'/wp-content/uploads/custom.png'; else echo plugins_url('/includes/images/miniOrange2.png',__FILE__); ?>');"></div></a></div>
				<?php }?>
			</div>
		</div>
		
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_push_oobemail_auth'));
			var timeout;
			pollPushValidation();
			function pollPushValidation()
			{	
				var transId = "<?php echo $_SESSION[ 'mo2f-login-transactionId' ];  ?>";
				var jsonString = "{\"txId\":\""+ transId + "\"}";
				var postUrl = "<?php echo get_option('mo2f_host_name');  ?>" + "/moas/api/auth/auth-status";
				
				jQuery.ajax({
					url: postUrl,
					type : "POST",
					dataType : "json",
					data : jsonString,
					contentType : "application/json; charset=utf-8",
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							jQuery('#mo2f_mobile_validation_form').submit();
						} else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED') {
							jQuery('#mo2f_backto_mo_loginform').submit();
						} else {
							timeout = setTimeout(pollPushValidation, 3000);
						}
					}
				});
			}
			jQuery('#myCarousel').carousel('pause');
			jQuery('#pushHelpLink').click(function() {
				jQuery('#showPushHelp').show();
				jQuery('#pushSection').hide();
				
				jQuery('#myCarousel').carousel(0); 
			});
			jQuery('#pushLink').click(function() {
				jQuery('#showPushHelp').hide();
				jQuery('#pushSection').show();
				jQuery('#myCarousel').carousel('pause');
			});
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			 function mologinoffline(){
				jQuery('#mo2f_show_softtoken_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			 </script>
	<?php 
	}
	
	function mo2f_getqrcode(){
	?>
		<div class="miniorange_mobile_auth">
			<?php if(isset($_SESSION['mo2f-login-message']) && $_SESSION['mo2f-login-message'] == 'Error:OTP over Email'){ ?>
			<center>
			<div class="mo2fa_messages_container" id="otpMessage"> 
				<p class='mo2fa_display_message'><?php echo 'Error occurred while sending OTP over email. Please try again.'; ?></p>
			</div></center><br /> 
			<?php } ?>
			
			<div id="mo_2_factor_qr_code_page" class="miniorange-inner-login-container">
				<div id="scanQRSection">
					<br>
						<center><a href="#showQRHelp" id="helpLink"><h3>See How It Works ?</h3></a></center>
					<div style="margin-bottom:10%;padding-top:6%;">
					<center>
					<h3>Identify yourself by scanning the QR code with miniOrange Authenticator app.</h3>
					</center></div>
						
					<div id="showQrCode" style="margin-bottom:10%;"><center> <?php echo '<img src="data:image/jpg;base64,' . $_SESSION[ 'mo2f-login-qrCode' ] . '" />'; ?>
					</center>
					</div>
							
					
					<span style="padding-right:2%;">
					
					<center>
						<?php if(get_option('mo2f_enable_forgotphone')){ ?>
						<input type="button" name="miniorange_login_forgotphone" onclick="mologinforgotphone();" id="miniorange_login_forgotphone" class="miniorange-button" style="margin-right:5%;" value="Forgot Phone?" />
						<?php } ?>
						
						<input type="button" name="miniorange_login_offline" onclick="mologinoffline();" id="miniorange_login_offline" class="miniorange-button" value="Phone is Offline?" /></center></span>
						
						<div><center><input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" /></center></div>
					<br />
				
				</div>
				<div id="showQRHelp" class="showQRHelp" hidden>
					<br>
					<center><a href="#showQRHelp" id="qrLink"><h3>←Back to Scan QR Code.</h3></a>
					<br>
						<div id="myCarousel" class="carousel slide" data-ride="carousel">
						  <!-- Indicators -->
						  <ol class="carousel-indicators">
							<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
							<li data-target="#myCarousel" data-slide-to="1"></li>
							<li data-target="#myCarousel" data-slide-to="2"></li>
							<li data-target="#myCarousel" data-slide-to="3"></li>
							<li data-target="#myCarousel" data-slide-to="4"></li>
						  </ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
							  <img class="first-slide" src="http://miniorange.com/images/help/qr-help-1.png" alt="First slide">
							</div>
						   <div class="item">
							<p>Open miniOrange Authenticator app and click on Authenticate.</p><br>
							<img class="first-slide" src="http://miniorange.com/images/help/qr-help-2.png" alt="First slide">
						 
						  </div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/qr-help-3.png" alt="First slide">
						  </div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com//images/help/qr-help-4.png" alt="First slide">
						  </div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/qr-help-5.png" alt="First slide">
						  </div>
						</div>
						</div>
					</center>
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange" style="background-image: url('<?php if(get_option('mo2f_enable_custom_poweredby')==1) echo site_url().'/wp-content/uploads/custom.png'; else echo plugins_url('/includes/images/miniOrange2.png',__FILE__); ?>');"></div></a></div>
				<?php }?>
			</div>
		</div>
		
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_mobile_auth'));
			var timeout;
			pollMobileValidation();
			function pollMobileValidation()
			{
				var transId = "<?php echo $_SESSION[ 'mo2f-login-transactionId' ];  ?>";
				var jsonString = "{\"txId\":\""+ transId + "\"}";
				var postUrl = "<?php echo get_option('mo2f_host_name');  ?>" + "/moas/api/auth/auth-status";
				jQuery.ajax({
					url: postUrl,
					type : "POST",
					dataType : "json",
					data : jsonString,
					contentType : "application/json; charset=utf-8",
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							var content = "<div id='success'><center><img src='" + "<?php echo plugins_url( 'includes/images/right.png' , __FILE__ );?>" + "' /></center></div>";
							jQuery("#showQrCode").empty();
							jQuery("#showQrCode").append(content);
							setTimeout(function(){jQuery("#mo2f_mobile_validation_form").submit();}, 100);
						} else if (status == 'ERROR' || status == 'FAILED') {
							var content = "<div id='error'><center><img src='" + "<?php echo plugins_url( 'includes/images/wrong.png' , __FILE__ );?>" + "' /></center></div>";
							jQuery("#showQrCode").empty();
							jQuery("#showQrCode").append(content);
							setTimeout(function(){jQuery('#mo2f_backto_mo_loginform').submit();}, 1000);
						} else {
							timeout = setTimeout(pollMobileValidation, 3000);
						}
					}
				});
			}
			jQuery('#myCarousel').carousel('pause');
			jQuery('#helpLink').click(function() {
				jQuery('#showQRHelp').show();
				jQuery('#scanQRSection').hide();
				
				jQuery('#myCarousel').carousel(0); 
			});
			jQuery('#qrLink').click(function() {
				jQuery('#showQRHelp').hide();
				jQuery('#scanQRSection').show();
				jQuery('#myCarousel').carousel('pause');
			});
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			 function mologinoffline(){
				jQuery('#mo2f_show_softtoken_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			</script>
	<?php 
	}

	function mo2f_getotp_form(){
	?>	<div class="miniorange_soft_auth">
			<center>
			<div  id="otpMessage" class="mo2fa_otp_messages_container">
				<p class='mo2fa_display_message' ><?php echo $_SESSION['mo2f-login-message']; ?></p>
			</div> 
			</center>
			<br>
			<div id="mo_2_factor_soft_token_page" class="miniorange-inner-login-container" >
				<div id="showOTP">
					<br />
					<?php if($_SESSION[ 'mo_2factor_login_status' ] != 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION'){	?>
					<center><a href="#showOTPHelp" id="otpHelpLink"><h3>See How It Works ?</h3></a></center>
					<?php } ?>
					<br />
				
					<div id="displaySoftToken"><center><input type="text" name="mo2fa_softtokenkey" style="width:75%;" placeholder="Enter one time passcode" id="mo2fa_softtokenkey" required="true" autofocus="true" pattern="[0-9]{4,8}" title="Only digits within range 4-8 are allowed."/></center></div>
							
					<span><input type="button" name="miniorange_soft_token_submit" onclick="mootploginsubmit();" id="miniorange_soft_token_submit" class="miniorange-button" style="margin-left:12%;width:284px;" value="Validate" />
					<br /><br />
					
					<?php if(get_option('mo2f_enable_forgotphone') && isset($_SESSION[ 'mo_2factor_login_status' ] ) && $_SESSION[ 'mo_2factor_login_status' ] != 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL'){ ?>
					<input type="button" name="miniorange_login_forgotphone" style="width:139px;margin-left:21px;" onclick="mologinforgotphone();" id="miniorange_login_forgotphone" class="button-green" value="Forgot Phone ?" />
					<?php } ?>
					<input type="button" name="miniorange_login_back" onclick="mologinback();" style="float:right;margin-right:50px;" id="miniorange_login_back" class="button-green" value="←Back To Login"/>
						
					</span><br><br>
				</div>
				<div id="showOTPHelp" class="showOTPHelp" hidden>
				<br>
					<center><a href="#showOTP" id="otpLink"><h3>←Go Back</h3></a>
				<br>
				<div id="myCarousel" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
     
					<?php if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){ ?>
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
						<li data-target="#myCarousel" data-slide-to="3"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
      
						
						   <div class="item active">
						   <p>Open miniOrange Authenticator app and click on settings icon on top right corner.</p><br>
						  <img class="first-slide" src="http://miniorange.com/images/help/qr-help-2.png" alt="First slide">
						  </div>
						   <div class="item">
						   <p>Click on Sync button below to sync your time with miniOrange Servers. This is a one time sync to avoid otp validation failure.</p><br>
						  <img class="first-slide" src="http://miniorange.com/images/help/token-help-3.png" alt="First slide">
						  </div>
						  <div class="item">
						   <p>Go to Soft Token tab.</p><br>
						  <img class="first-slide" src="http://miniorange.com/images/help/token-help-2.png" alt="First slide">
						  </div>
						  <div class="item">
						   <p>Enter the one time passcode shown in miniOrange Authenticator app here.</p><br>
						  <img class="first-slide" src="http://miniorange.com/images/help/token-help-4.png" alt="First slide">
						  </div>
						</div>
					<?php } else  if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL') { ?>
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
							  <img class="first-slide" src="http://miniorange.com/images/help/otp-help-1.png" alt="First slide">
							</div>
						   <div class="item">
						   <p>Check your email with which you registered and copy the one time passcode.</p><br>
							<img class="first-slide" src="http://miniorange.com/images/help/otp-help-2.png" alt="First slide">
							</div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/otp-help-3.png" alt="First slide">
						  </div>
						 </div>
					<?php } else if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS') { ?>
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
							  <img class="first-slide" src="http://miniorange.com/images/help/otp-over-sms-login-flow-1.png" alt="First slide">
							</div>
						   <div class="item">
							<img class="first-slide" src="http://miniorange.com/images/help/otp-over-sms-login-flow-2.png" alt="First slide">
							</div>
						  <div class="item">
						  <img class="first-slide" src="http://miniorange.com/images/help/otp-over-sms-login-flow-3.png" alt="First slide">
						  </div>
						 </div>
					<?php } else { ?> 
					<!-- phone call verification  -->
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						
						
						</ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
								<p>You will receive a phone call. Pick up the call and listen to the one time passcode carefully. </p>
							  <img class="first-slide" src="http://miniorange.com/images/help/phone-call-login-flow-2.png" alt="First slide">
							</div>
						   <div class="item">
						   <p>Enter the one time passcode here and click on validate button to login.</p><br>
							<img class="first-slide" src="http://miniorange.com/images/help/phone-call-login-flow.png" alt="First slide">
							</div>
						  
						 </div>
					<?php } ?>
					
				</div>
				</div>	
				
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange" style="background-image: url('<?php if(get_option('mo2f_enable_custom_poweredby')==1) echo site_url().'/wp-content/uploads/custom.png'; else echo plugins_url('/includes/images/miniOrange2.png',__FILE__); ?>');"></div></a></div>
				<?php }?>
			</div>
			
		</div>
		<script>
			
			jQuery("div#login").hide();
		    jQuery('#otpHelpLink').click(function() {
				jQuery('#showOTPHelp').show();
				jQuery('#showOTP').hide();
				jQuery('#otpMessage').hide();
			});
			jQuery('#otpLink').click(function() {
				jQuery('#showOTPHelp').hide();
				jQuery('#showOTP').show();
				jQuery('#otpMessage').show();
			});
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			  function mootploginsubmit(){
				var otpkey = jQuery('#mo2fa_softtokenkey').val();
				document.getElementById("mo2f_submitotp_loginform").elements[0].value = otpkey;
				jQuery('#mo2f_submitotp_loginform').submit();
				
			 }
			 
			 jQuery('#mo2fa_softtokenkey').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var otpkey = jQuery('#mo2fa_softtokenkey').val();
					document.getElementById("mo2f_submitotp_loginform").elements[0].value = otpkey;
					jQuery('#mo2f_submitotp_loginform').submit();
				  }
				 
			});
			
			

		</script>
	<?php
	}
	
	function mo2f_get_device_form(){
	?>
		<div class="miniorange_trust_device">
			
			<div id="mo_2_factor_push_page" class="miniorange-inner-push-login-container">
				<div id="pushSection">
					<span><h2 style="padding:5px;background-color:beige;">Remember Device</h2></span>
					<br>
					<div id="mo_device_title" style="margin-bottom:10%;padding-top:6%;">
					<center>
					<h3>Do you want to remember this device?</h3>
					</center>
					</div>
					<br />
					<div id="mo2f_device_content">
					<center>
						<input type="button" name="miniorange_trust_device_yes" onclick="mo_check_device_confirm();" id="miniorange_trust_device_yes" class="miniorange-button mo_green" style="margin-right:5%;" value="Yes" />
						
						<input type="button" name="miniorange_trust_device_no" onclick="mo_check_device_cancel();" id="miniorange_trust_device_no" class="miniorange-button mo_red" value="No" />
					</center>
					</div>
					<div id="showLoadingBar"  hidden>
						<center>
						<h3>Please wait...We are taking you into your account.</h3>
						 
							<img src="<?php echo plugins_url( 'includes/images/ajax-loader-login.gif' , __FILE__ );?>" />
						</center>
					</div>
					<br /><br />
					<center>
						<span>
							Click on <i><b>Yes</b></i> if this is your personal device.<br />
							Click on <i><b>No</b></i> if this is a public device.
						</span>
					</center>
					
					<br /><br />
					
						<div><center><input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" /></center></div>
					<br />
				
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange" style="background-image: url('<?php if(get_option('mo2f_enable_custom_poweredby')==1) echo site_url().'/wp-content/uploads/custom.png'; else echo plugins_url('/includes/images/miniOrange2.png',__FILE__); ?>');"></div></a></div>
				<?php }?>
			</div>
			
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_trust_device'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			function mo_check_device_confirm(){
				jQuery('#mo2f_device_content').hide();
				jQuery('#mo_device_title').hide();
				jQuery('#showLoadingBar').show();
				jQuery('#mo2f_trust_device_confirm_form').submit();
			}
			function mo_check_device_cancel(){
				jQuery('#mo2f_device_content').hide();
				jQuery('#mo_device_title').hide();
				jQuery('#showLoadingBar').show();
				jQuery('#mo2f_trust_device_cancel_form').submit();
			}
		</script>
	<?php
	}
?>