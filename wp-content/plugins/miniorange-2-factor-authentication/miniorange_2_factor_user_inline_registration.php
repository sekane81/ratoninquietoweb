<?php

include_once dirname( __FILE__ ) . '/miniorange_2_factor_mobile_configuration.php';

	
	function prompt_user_to_register(){ ?>
		<div class="miniorange_kba_page">
		<center>
		<div class="miniorange-inner-kba-login-container">
		
			<h2 class="mo_header_background" >Setup Two Factor</h2>
			<br>
			<p><?php echo $_SESSION['mo2f-login-message']; ?></p>
			<br>
			A new security system has been enabled to better protect your account. Please configure your Two-Factor Authentication method by setting up your account.
			<br><br>
			
			<input type="email" autofocus="true" name="mo_useremail" id="mo_useremail" class="mo_email_textbox" required placeholder="person@example.com" />
			
			<br><br>
			<input type="button" name="miniorange_get_started" onclick="mouserregistersubmit();" class="miniorange-button" value="Get Started" />
			<?php if( !get_option('mo2f_inline_registration')){ ?>
				<input type="button" name="mo2f_skip_btn" onclick="moskipregistersubmit();" class="miniorange-button" value="Skip" />
			<?php } ?>
			<br><br>
			<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
			<br><br>
			<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			
		</div>
			
		</center>
			
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_kba_page'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			function mouserregistersubmit(){
				var userEmail = jQuery('#mo_useremail').val();
				document.getElementById("mo2f_inline_register_user_form").elements[0].value = userEmail;
				jQuery('#mo2f_inline_register_user_form').submit();
				
			 }
			 
			 jQuery('#mo_useremail').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var userEmail = jQuery('#mo_useremail').val();
					document.getElementById("mo2f_inline_register_user_form").elements[0].value = userEmail;
					jQuery('#mo2f_inline_register_user_form').submit();
				  }
					
			});
			function moskipregistersubmit(){
				jQuery('#mo2f_inline_register_skip_form').submit();
			}
		</script>
	<?php }
	
	function prompt_user_for_validate_otp(){ ?>
		<div class="miniorange_soft_auth">
		<center>
		<div class="miniorange-inner-login-container">
		
			<h2 class="mo_header_background">Setup Two Factor</h2>
			<br>
			<div style="padding-left:10px;padding-right:10px;"><?php echo isset($_SESSION['mo2f-login-message']) ? $_SESSION['mo2f-login-message'] : '';?></div><br/>
			<div style="padding-left:40px;padding-right:40px;">
				<input  autofocus="true" type="text" name="otp_token" id="otp_token" required placeholder="Enter OTP" />
			
			<a href="#resendinlineotplink">Resend OTP ?</a>
			<input type="button" name="back" id="mo2f_inline_backto_regform" style="margin-left:20px;" class="miniorange-button" value="Back" />
			<input type="button" name="miniorange_validtae_otp" style="float:right;" value="Validate OTP" class="miniorange-button" onclick="movalidateotpsubmit();" />
			</div>
			
						
						
						<br><br>
			<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
			<br><br>
			<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			
		</div>
			
		</center>
			
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			function movalidateotpsubmit(){
				var otp = jQuery('#otp_token').val();
				document.getElementById("mo2f_inline_user_validate_otp_form").elements[0].value = otp;
				jQuery('#mo2f_inline_user_validate_otp_form').submit();
			 }
			 
			 jQuery('#otp_token').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var otp = jQuery('#otp_token').val();
					document.getElementById("mo2f_inline_user_validate_otp_form").elements[0].value = otp;
					jQuery('#mo2f_inline_user_validate_otp_form').submit();
				  }
					
			});
			jQuery('a[href="#resendinlineotplink"]').click(function(e) {
				jQuery('#mo2fa_inline_resend_otp_form').submit();
			});
			jQuery('#mo2f_inline_backto_regform').click(function() {	
					jQuery('#mo2f_goto_user_registration_form').submit();
			});
		</script>
	<?php }
	
	function prompt_user_to_select_2factor_method($current_user){
		
		if(get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'MOBILE AUTHENTICATION' 
		|| get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'SOFT TOKEN' 
		|| get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'PUSH NOTIFICATIONS'){
			
					prompt_user_for_miniorange_app_setup($current_user);
					
		}else if(get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'SMS' 
			|| get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'PHONE VERIFICATION'){
			
					prompt_user_for_phone_setup($current_user);
					
		}else if(get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'GOOGLE AUTHENTICATOR' ){
			
					prompt_user_for_google_authenticator_setup($current_user);
					
		}else if(get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'KBA' ){
			
				prompt_user_for_kba_setup($current_user);
			
		}else if(get_user_meta($current_user,'mo2f_selected_2factor_method',true) == 'OUT OF BAND EMAIL' ){
			
				prompt_user_for_email_setup($current_user);
			
		}else{
			$opt = (array) get_option('mo2f_auth_methods_for_users'); ?>
		<div class="miniorange_soft_auth">
		
		<div class="miniorange-inner-kba-login-container" >
		
			<h2 class="mo_header_background">Setup Two Factor</h2>
			<br>
			<div class="mo_margin_left">
			
			<b>Select any Two-Factor of your choice below and complete its setup.</b>
				<br><br>
				<span class="<?php if( !(in_array("OUT OF BAND EMAIL", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; } ?>" >
					<label title="You will receive an email with link. You have to click the ACCEPT or DENY link to verify your email. Supported in Desktops, Laptops, Smartphones.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="OUT OF BAND EMAIL"  />
								Email Verification
					</label>
					<br>
				</span>	
					
					<span class="<?php if( !(in_array("SMS", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; } ?>" >
						
							<label title="You will receive a one time passcode via SMS on your phone. You have to enter the otp on your screen to login. Supported in Smartphones, Feature Phones.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="SMS"  />
								OTP Over SMS
							</label>
						<br>
					</span>
					
					<span class="<?php if(  !(in_array("PHONE VERIFICATION", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; } ?>">
					
							<label title="You will receive a phone call telling a one time passcode. You have to enter the one time passcode to login. Supported in Landlines, Smartphones, Feature phones.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="PHONE VERIFICATION"  />
								Phone Call Verification
							</label>
						<br>
					</span>
					
					<span class="<?php if(  !(in_array("SOFT TOKEN", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; } ?>" >
							<label title="You have to enter 6 digits code generated by miniOrange Authenticator App like Google Authenticator code to login. Supported in Smartphones only." >
								<input type="radio"  name="mo2f_selected_2factor_method"  value="SOFT TOKEN"  />
								Soft Token
							</label>
							
						<br>
					</span>
					
					<span class="<?php if(  !(in_array("MOBILE AUTHENTICATION", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; }?>">
					
							<label title="You have to scan the QR Code from your phone using miniOrange Authenticator App to login. Supported in Smartphones only.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="MOBILE AUTHENTICATION"  />
								QR Code Authentication
							</label>
						<br>
					</span>
					
					<span class="<?php if(  !(in_array("PUSH NOTIFICATIONS", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; } ?>" >
						
							<label title="You will receive a push notification on your phone. You have to ACCEPT or DENY it to login. Supported in Smartphones only.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="PUSH NOTIFICATIONS"  />
								Push Notification
							</label>
							<br>	
					</span>
				<span class="<?php if( !(in_array("GOOGLE AUTHENTICATOR", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; }?>">
						
							<label title="You have to enter 6 digits code generated by Google Authenticaor App to login. Supported in Smartphones only.">
								<input type="radio"  name="mo2f_selected_2factor_method"  value="GOOGLE AUTHENTICATOR"  />
								Google Authenticator
							</label>
							<br>
				</span>
				
				<span class="<?php if( !(in_array("KBA", $opt))  ){ echo "mo2f_td_hide"; }else { echo "mo2f_td_show"; }?>">
						
					<label title="You have to answers some knowledge based security questions which are only known to you to authenticate yourself. Supported in Desktops,Laptops,Smartphones." >
					<input type="radio"  name="mo2f_selected_2factor_method"  value="KBA"  />
								Security Questions ( KBA )
							</label>
							
				</span>
										
						<br><br>
			<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
			<br><br>
			</div>
			<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			
		</div>
	
			
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			jQuery('input:radio[name=mo2f_selected_2factor_method]').click(function() {
				var selectedMethod = jQuery(this).val();
				document.getElementById("mo2f_select_2fa_methods_form").elements[0].value = selectedMethod;
				jQuery('#mo2f_select_2fa_methods_form').submit();
			});
	
		</script>
		<?php } 
		}
		
		function prompt_user_for_google_authenticator_setup($current_user){
			$mo2f_google_auth = isset($_SESSION['mo2f_google_auth']) ? $_SESSION['mo2f_google_auth'] : null;
			$data = isset($_SESSION['mo2f_google_auth']) ? $mo2f_google_auth['ga_qrCode'] : null;
			$ga_secret = isset($_SESSION['mo2f_google_auth']) ? $mo2f_google_auth['ga_secret'] : null;
			$opt = (array) get_option('mo2f_auth_methods_for_users');
	?>
			<div class="miniorange_soft_auth">
				<div class="miniorange-ga-setup-container">
					<h2 class="mo_header_background">Set up Google Authenticator</h2>
					<div class="mo_margin_left">
					<br>
					<?php echo $_SESSION['mo2f-login-message']; ?>
					<br>
					<table>
					<tr>
					<td style="vertical-align:top;width:18%;">
					<h3>Select Phone Type</h3>
					<br>
						<input type="radio" name="mo2f_inline_app_type_radio" value="android" <?php checked( $mo2f_google_auth['ga_phone'] == 'android' ); ?> /> <b>Android</b><br /><br />
							<input type="radio" name="mo2f_inline_app_type_radio" value="iphone" <?php checked( $mo2f_google_auth['ga_phone'] == 'iphone' ); ?> /> <b>iPhone</b><br /><br />
							<input type="radio" name="mo2f_inline_app_type_radio" value="blackberry" <?php checked( $mo2f_google_auth['ga_phone'] == 'blackberry' ); ?> /> <b>BlackBerry</b><br /><br />
					<?php if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange-button" value="Back" />
					<?php } ?>
					</td>
					<td class="mo2f_separator"></td>
				<td style="width:46%;">
					
					
						<div id="mo2f_android_div" style="<?php echo $mo2f_google_auth['ga_phone'] == 'android' ? 'display:block' : 'display:none'; ?>" class="mo_margin_left">
							<h3>Install the Google Authenticator App for Android.</h3>
							<br>
							<ol>
								<li>On your phone,Go to Google Play Store.</li>
								<li>Search for <b>Google Authenticator.</b>
								<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Download from the Google Play Store and install the application.</a>
								</li>
							
							</ol>
							<br>
							<h3>Now open and configure Google Authenticator.</h3>
							<br>
							<ol>
								<li>In Google Authenticator, touch Menu and select "Set up account."</li>
								<li>Select "Scan a barcode". Use your phone's camera to scan this barcode.</li>
							<center><br><div id="displayQrCode" ><?php echo '<img src="data:image/jpg;base64,' . $data . '" />'; ?></div></center>
								
							</ol>
							<center>
							<div><a  data-toggle="collapse" href="#mo2f_scanbarcode_a" aria-expanded="false" ><b>Can't scan the barcode? </b></a></div>
							<div class="collapse" id="mo2f_scanbarcode_a">
								<ol>
									<li>In Google Authenticator, touch Menu and select "Set up account."</li>
									<li>Select "Enter provided key"</li>
									<li>In "Enter account name" type your full email address.</li>
									<li>In "Enter your key" type your secret key:</li>
										<div style="padding: 10px; background-color: #f9edbe;width: 20em;text-align: center;" >
											<div style="font-size: 14px; font-weight: bold;line-height: 1.5;" >
											<?php echo $ga_secret; ?>
											</div>
											<div style="font-size: 80%;color: #666666;">
											Spaces don't matter.
											</div>
										</div>
									<li>Key type: make sure "Time-based" is selected.</li>
									<li>Tap Add.</li>
								</ol>
							</div>
							</center>
						</div>
					
						<div id="mo2f_iphone_div" style="<?php echo $mo2f_google_auth['ga_phone'] == 'iphone' ? 'display:block' : 'display:none'; ?>" class="mo_margin_left">
							<h3>Install the Google Authenticator app for iPhone.</h3>
							<br>
							<ol>
								<li>On your iPhone, tap the App Store icon.</li>
								<li>Search for <b>Google Authenticator.</b>
								<a href="http://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8" target="_blank">Download from the App Store and install it</a>
								</li>
							</ol>
							<br>
							<h3>Now open and configure Google Authenticator.</h3>
							<br>
							<ol>
								<li>In Google Authenticator, tap "+", and then "Scan Barcode."</li>
								<li>Use your phone's camera to scan this barcode.
									<center><br><div id="displayQrCode" ><?php echo '<img src="data:image/jpg;base64,' . $data . '" />'; ?><br><br>
									<a  data-toggle="collapse" href="#mo2f_scanbarcode_i" aria-expanded="false" ><b>Can't scan the barcode? </b></a>
							<div class="collapse" id="mo2f_scanbarcode_i"  >
							<br>
								<ol>
									<li>In Google Authenticator, tap +.</li>
									<li>Key type: make sure "Time-based" is selected.</li>
									<li>In "Account" type your full email address.</li>
									<li>In "Key" type your secret key:</li>
										<div style="padding: 10px; background-color: #f9edbe;width: 20em;text-align: center;" >
											<div style="font-size: 14px; font-weight: bold;line-height: 1.5;" >
											<?php echo $ga_secret; ?>
											</div>
											<div style="font-size: 80%;color: #666666;">
											Spaces don't matter.
											</div>
										</div>
									<li>Tap Add.</li>
								</ol>
							</div></div></center>
								</li>
							</ol>
							<br>
							
						</div>
						<div id="mo2f_blackberry_div" style="<?php echo $mo2f_google_auth['ga_phone'] == 'blackberry' ? 'display:block' : 'display:none'; ?>" class="mo_margin_left">
							<h3>Install the Google Authenticator app for BlackBerry</h4>
							<br>
							<ol>
								<li>On your phone, open a web browser.Go to <b>m.google.com/authenticator.</b></li>
								<li>Download and install the Google Authenticator application.</li>
							</ol>
							<br>
							<h3>Now open and configure Google Authenticator.</h3>
							<br>
							<ol>
								<li>In Google Authenticator, select Manual key entry.</li>
								<li>In "Enter account name" type your full email address.</li>
								<li>In "Enter key" type your secret key:</li>
									<div style="padding: 10px; background-color: #f9edbe;width: 20em;text-align: center;" >
										<div style="font-size: 14px; font-weight: bold;line-height: 1.5;" >
										<?php echo $ga_secret; ?>
										</div>
										<div style="font-size: 80%;color: #666666;">
										Spaces don't matter.
										</div>
									</div>
								<li>Choose Time-based type of key.</li>
								<li>Tap Save.</li>
							</ol>
						</div>
						<br>
					</td>
					<td class="mo2f_separator"></td>
				<td style="vertical-align:top;">
						<div style="<?php echo isset($_SESSION['mo2f_google_auth']) ? 'display:block' : 'display:none'; ?>" class="mo_margin_left">
							<h3>Verify and Save</h3><br>
							<div>Once you have scanned the barcode, enter the 6-digit verification code generated by the Authenticator app</div><br/>
							<span><b>Code: </b>
							<input class="mo2f_table_textbox"  autofocus="true" required="true" type="text" id="google_token" name="google_token" placeholder="Enter OTP" /></span><br /><br/>
					
							<input type="button" name="validate" id="validate" class="miniorange-button" onclick="mo2f_inline_verify_ga_code();" value="Verify and Save" />
							
						</div>
						</td>
					<tr>
				</table>
				<?php if (sizeof($opt) == 1) { ?>
			<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
			<?php } ?>
				<br><br>
			<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
		
					</div>
				</div>
			</div>
			<script>
				jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			jQuery('#mo2f_inline_back_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
				jQuery('input:radio[name=mo2f_inline_app_type_radio]').click(function() {
					var selectedPhone = jQuery(this).val();
					document.getElementById("mo2f_inline_app_type_ga_form").elements[0].value = selectedPhone;
					jQuery('#mo2f_inline_app_type_ga_form').submit();
				});
				function mo2f_inline_verify_ga_code(){
					var token = jQuery('#google_token').val();
					document.getElementById("mo2f_inline_verify_ga_code_form").elements[0].value = token;
					jQuery('#mo2f_inline_verify_ga_code_form').submit();
				 }
			 
			 jQuery('#google_token').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var token = jQuery('#google_token').val();
					document.getElementById("mo2f_inline_verify_ga_code_form").elements[0].value = token;
					jQuery('#mo2f_inline_verify_ga_code_form').submit();
				  }
					
			});
			</script>
	
	<?php
	 }
	function prompt_user_for_phone_setup($current_user){ 
	$opt = (array) get_option('mo2f_auth_methods_for_users');
	?>
		<div class="miniorange_soft_auth">
		<div class="miniorange-inner-login-container">
		<h2 class="mo_header_background">Verify Your Phone</h2>
		<div class="mo_margin_left">
		<br>
		<p><b><?php echo $_SESSION['mo2f-login-message']; ?></b></p>
		<br>
				<div class="mo2f_row">
						<h4>Enter your phone number</h4>
						<input class="mo2f_textbox"  type="text" name="verify_phone" id="phone" style="padding-left:40px!important;"
						    value="<?php if( isset($_SESSION['mo2f_phone'])){ echo $_SESSION['mo2f_phone'];} else echo get_user_meta($current_user,'mo2f_user_phone',true); ?>" pattern="[\+]?[0-9]{1,4}\s?[0-9]{7,12}" title="Enter phone number without any space or dashes" />
						<input type="button" name="verify" onclick="moinlineverifyphone();" class="miniorange-button" value="Verify" />
				</div>	
				
			<br>
						<h4>Enter One Time Passcode</h4>
						
								<input class="mo2f_textbox"  autofocus="true" type="text" name="otp_token" placeholder="Enter OTP" id="otp_token"/>
								<?php if (get_user_meta($current_user, 'mo2f_selected_2factor_method',true) == 'SMS'){ ?>
									<a href="#resendsmslink">Resend OTP ?</a>
								<?php } else {?>
									<a href="#resendsmslink">Call Again ?</a>
								<?php } ?><br>
					
					<?php if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange-button" value="Back" />
					<?php } ?>
					<input type="button" name="validate" onclick="moverifyotp();" class="miniorange-button" value="Validate OTP" />
					
				<br><br>
			</div>
			<?php if (sizeof($opt) == 1) { ?>
			<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
			<?php } ?>
			<br><br>
			<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			</div>
		</div>
		<script>
			jQuery("#phone").intlTelInput();
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			
			jQuery('#mo2f_inline_back_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
			
			jQuery('a[href="#resendsmslink"]').click(function(e) {
				jQuery('#mo2fa_inline_resend_otp_form').submit();
			});
			
			function moinlineverifyphone(){
				var phone = jQuery('#phone').val();
				document.getElementById("mo2f_inline_verifyphone_form").elements[0].value = phone;
				jQuery('#mo2f_inline_verifyphone_form').submit();
			 }
			 
			 jQuery('#phone').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var phone = jQuery('#phone').val();
					document.getElementById("mo2f_inline_verifyphone_form").elements[0].value = phone;
					jQuery('#mo2f_inline_verifyphone_form').submit();
				  }
					
			});
			
			function moverifyotp(){
				var otp = jQuery('#otp_token').val();
				document.getElementById("mo2f_inline_validateotp_form").elements[0].value = otp;
				jQuery('#mo2f_inline_validateotp_form').submit();
			 }
			 
			 jQuery('#otp_token').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var otp = jQuery('#otp').val();
					document.getElementById("mo2f_inline_validateotp_form").elements[0].value = otp;
					jQuery('#mo2f_inline_validateotp_form').submit();
				  }
					
			});

		</script>
	
	
	
	<?php }
	function prompt_user_for_miniorange_app_setup($current_user){ 
	$opt = (array) get_option('mo2f_auth_methods_for_users');
			
	?>
		<div class="miniorange_app_setup_page">
		<div class="miniorange-app-setup-container">
		<h2 class="mo_header_background">Setup miniOrange Authenticator App</h2>
		<div class="mo_margin_left">
		<br>
		<p><b><?php echo $_SESSION['mo2f-login-message']; ?></b></p>
		<br>
		<p class='mo2f_success_container' ><?php echo $_SESSION['mo2f-login-message']; ?></p>
			<?php download_instruction_for_mobile_app($_SESSION['mo2f_current_user']); ?>
			<div class="mo_margin_left">
			<br>
			<h3>Step-2 : Scan QR code</h3><hr class="mo_hr">
			<br>
			<div id="mo2f_configurePhone"><h4>Please click on 'Configure your phone' button below to see QR Code.</h4>
			<br>
			<?php if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange-button" value="Back" />
			<?php } ?>
					<input type="button" name="submit" onclick="moconfigureapp();" class="miniorange-button" value="Configure your phone" />
			</div>
			
			<?php 
						if(isset($_SESSION[ 'mo2f_show_qr_code' ]) && $_SESSION[ 'mo2f_show_qr_code' ] == 'MO_2_FACTOR_SHOW_QR_CODE' && isset($_POST['miniorange_inline_show_qrcode_nonce']) && wp_verify_nonce( $_POST['miniorange_inline_show_qrcode_nonce'], 'miniorange-2-factor-inline-show-qrcode-nonce' )){
									initialize_inline_mobile_registration(); ?>
									<script>jQuery("#mo2f_app_div").hide();</script>
						<?php } ?>
			<br>
			</div>
		<?php if (sizeof($opt) == 1) { ?>
		<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
		<?php } ?>
		<br><br>
		</div>
		<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			</div>
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_app_setup_page'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			
			function moconfigureapp(){
				jQuery('#mo2f_inline_configureapp_form').submit();
			 }
			 jQuery('#mo2f_inline_back_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
		</script>
	
	
	<?php }
	
	function initialize_inline_mobile_registration(){
		$data = $_SESSION[ 'mo2f-login-qrCode' ];
		$url = get_option('mo2f_host_name');
		$opt = (array) get_option('mo2f_auth_methods_for_users');
		?>
		
			<p>Open your <b>miniOrange Authenticator</b> app and click on <b>Configure button</b> to scan the QR Code. Your phone should have internet connectivity to scan QR code.</p>
			<div class="red">
			<p>I am not able to scan the QR code, <a  data-toggle="collapse" href="#mo2f_scanqrcode" aria-expanded="false" >click here </a></p></div>
			<div class="collapse" id="mo2f_scanqrcode">
				Follow these instructions below and try again.
				<ol>
					<li>Make sure your desktop screen has enough brightness.</li>
					<li>Open your app and click on Configure button to scan QR Code again.</li>
					<li>If you get cross mark on QR Code then click on 'Refresh QR Code' link.</li>
				</ol>
			</div>
			<br>
			<table class="mo2f_settings_table">
				<a href="#mo2f_refreshQRCode">Click here to Refresh QR Code.</a>
				<div id="displayInlineQrCode" style="margin-left:250px;"><br /> <?php echo '<img style="width:200px;" src="data:image/jpg;base64,' . $data . '" />'; ?>
				</div>
			</table>
			<?php 
			if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_to_btn" class="miniorange-button" value="Back" />
			<?php } ?>
	
			<script>
			jQuery('#mo2f_inline_back_to_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
			jQuery('a[href="#mo2f_refreshQRCode"]').click(function(e) {	
					jQuery('#mo2f_inline_configureapp_form').submit();
			});
			jQuery("#mo2f_configurePhone").hide();
			var timeout;
			pollInlineMobileRegistration();
			function pollInlineMobileRegistration()
			{
				var transId = "<?php echo $_SESSION[ 'mo2f-login-transactionId' ];  ?>";
				var jsonString = "{\"txId\":\""+ transId + "\"}";
				var postUrl = "<?php echo $url;  ?>" + "/moas/api/auth/registration-status";
				jQuery.ajax({
					url: postUrl,
					type : "POST",
					dataType : "json",
					data : jsonString,
					contentType : "application/json; charset=utf-8",
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							var content = "<br/><div id='success'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo plugins_url( 'includes/images/right.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayInlineQrCode").empty();
							jQuery("#displayInlineQrCode").append(content);
							setTimeout(function(){jQuery("#mo2f_inline_mobile_register_form").submit();}, 1000);
						} else if (status == 'ERROR' || status == 'FAILED') {
							var content = "<br/><div id='error'><img style='width:165px;margin-top:-1%;margin-left:2%;' src='" + "<?php echo plugins_url( 'includes/images/wrong.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayInlineQrCode").empty();
							jQuery("#displayInlineQrCode").append(content);
							jQuery("#messages").empty();
							
							jQuery("#messages").append("<div class='error mo2f_error_container'> <p class='mo2f_msgs'>An Error occured processing your request. Please try again to configure your phone.</p></div>");
						} else {
							timeout = setTimeout(pollInlineMobileRegistration, 3000);
						}
					}
				});
			}
			</script>
	<?php }
	function prompt_user_for_kba_setup($current_user){ 
	$opt = (array) get_option('mo2f_auth_methods_for_users');
	?>
		<div class="miniorange_app_setup_page">
		
		<div class="miniorange-app-setup-container">
		<h2 class="mo_header_background">Setup Security Question (KBA)</h2>
		<div class="mo_margin_left">
		<br>
		<p><b><?php echo $_SESSION['mo2f-login-message']; ?></b></p>
		<br>
		<?php mo2f_configure_kba_questions(); ?>
		<br />
		<?php if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange-button" value="Back" />
		<?php } ?>
		<input type="button" name="validate" onclick="moinlinesavekba();" class="miniorange-button" value="Save" />
		<br>
		<?php if (sizeof($opt) == 1) { ?>
		<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
		<?php } ?>
		</div>
		
		<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			</div>
		</div>
		<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_app_setup_page'));
			function moinlinesavekba(){
				var kba_1 = jQuery('#mo2f_kbaquestion_1').val();
				var kba_2 = jQuery('#mo2f_kba_ans1').val();
				var kba_3 = jQuery('#mo2f_kbaquestion_2').val();
				var kba_4 = jQuery('#mo2f_kba_ans2').val();
				var kba_5 = jQuery('#mo2f_kbaquestion_3').val();
				var kba_6 = jQuery('#mo2f_kba_ans3').val();
				//alert("1: " + kba_1 + " 2: " + kba_2 + " 3: " + kba_3 + " 4: " + kba_4 + " 5: " + kba_5 + " 6: " + kba_6);
				document.getElementById("mo2f_inline_save_kba_form").elements[0].value = kba_1;
				document.getElementById("mo2f_inline_save_kba_form").elements[1].value = kba_2;
				document.getElementById("mo2f_inline_save_kba_form").elements[2].value = kba_3;
				document.getElementById("mo2f_inline_save_kba_form").elements[3].value = kba_4;
				document.getElementById("mo2f_inline_save_kba_form").elements[4].value = kba_5;
				document.getElementById("mo2f_inline_save_kba_form").elements[5].value = kba_6;
				jQuery('#mo2f_inline_save_kba_form').submit();
			 }
			 jQuery('#mo2f_inline_back_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
		</script>
		
	<?php }
	function prompt_user_for_email_setup($current_user){ 
	$opt = (array) get_option('mo2f_auth_methods_for_users');
	?>
	<div class="miniorange_soft_auth">
		<div class="miniorange-inner-login-container">
		<h2 class="mo_header_background">Two Factor Setup Complete</h2>
		<br><br>
		<center>
		<h4>Email Verification is set as your Two Factor method for login.<br><br /><br />
		<a href="#mo2f_login_email">Click Here</a> to sign-in into your account.</h4>
		<br>
		<?php if (sizeof($opt) > 1) { ?>
					<input type="button" name="back" id="mo2f_inline_back_btn" class="miniorange-button" value="Back" />
		<?php } ?>
		<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" />
		</center>
		<div class="mo2f_powered_by_div"><a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
		</div>
	</div>
	<script>
			jQuery("div#login").hide();
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			jQuery('a[href="#mo2f_login_email"]').click(function(e) {
				jQuery('#mo2f_inline_email_form').submit();
			});
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
			 jQuery('#mo2f_inline_back_btn').click(function() {	
					jQuery('#mo2f_goto_two_factor_form').submit();
			});
	</script>
	<?php }
	?>