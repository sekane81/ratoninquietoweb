<?php 
	function mo2f_show_help_and_troubleshooting($current_user) {
	?>
	<div class="mo2f_table_layout">
		<?php echo mo2f_check_if_registered_with_miniorange($current_user); ?>
		<br>
		<ul class="mo2f_faqs">
			<?php if(current_user_can( 'manage_options' )) { ?>
			
			<h3><a  data-toggle="collapse" href="#question1" aria-expanded="false" ><li>How to enable PHP cURL extension? (Pre-requisite)</li></a></h3>
				<div class="collapse" id="question1">
				cURL is enabled by default but in case you have disabled it, follow the steps to enable
				<ol>
					<li>Open php.ini(it's usually in /etc/ or in php folder on the server).</li>
					<li>Search for extension=php_curl.dll. Uncomment it by removing the semi-colon( ; ) in front of it.</li>
					<li>Restart the Apache Server.</li>
					</ol>
					For any further queries, please submit a query on right hand side in our <b>Support Section</b>.
				
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question2" aria-expanded="false" ><li>I am getting error - curl_setopt(): CURLOPT_FOLLOWLOCATION cannot be activated when an open_basedir is set.
				</li></a></h3>
				<div class="collapse" id="question2">
				Just setsafe_mode = Off in your php.ini file (it's usually in /etc/ on the server). If that's already off, then look around for the open_basedir in the php.ini file, and change it to open_basedir = .
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question3" aria-expanded="false" ><li>I did not recieve OTP while trying to register with miniOrange. What should I do?
				</li></a></h3>
				<div class="collapse" id="question3">
				The OTP is sent to your email address with which you have registered with miniOrange. If you can't see the email from miniOrange in your mails, please make sure to check your <b>SPAM folder</b>. If you don't see an email even in SPAM folder, please submit a query on right hand side in our <b>Support Section</b> or you can contact us at info@miniorange.com.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question4" aria-expanded="false" ><li>I forgot the password of my miniOrange account. How can I reset it?
				</li></a></h3>
				<div class="collapse" id="question4">
				There are two cases according to the page you see -
				<ul>
				<li>1. <b>Login with miniOrange screen:</b> You should click on forgot password link. You will get a new password on your email address with which you have registered with miniOrange . Now you can login with the new password.</li><br>
				<li>2. <b>Register with miniOrange screen:</b> Enter your email ID and any random password in password and confirm password input box. This will redirect you to Login with miniOrange screen. Now follow first step.</li>
				</ul>

				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question5" aria-expanded="false" ><li>I have a custom / front-end login page on my site and I want the look and feel to remain the same when I add 2 factor ?</li></a></h3>
				<div class="collapse" id="question5">
					 If you have a custom login form other than wp-login.php then you can copy the shortcode from <b>Advanced Options Tab</b> and embed in your login form. If you need any help setting up 2-Factor for your custom login form, please submit a query in our <b>Support Section</b> on right hand side.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question6" aria-expanded="false" ><li>I have Woocommerce theme login page on my site. How can I enable Two Factor ?</li></a></h3>
				<div class="collapse" id="question6">
					 If you have Woocommerce theme login then go to Advanced Options Tab and check <b>Enable Two-Factor for Woocommerce Front End Login</b>. If you need any help setting up 2-Factor for your Woocommerce theme login form, please submit a query in our <b>Support Section</b> on right hand side.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question7" aria-expanded="false" ><li>I am trying to login with Two-Factor but my screen got blank after entering username and password. I am locked out of my account. What to do now ?</li></a></h3>
				<div class="collapse" id="question7">
					If you have an additional administrator account whose Two Factor is not enabled yet. Login with it. Otherwise,
					Go to WordPress Database. Select wp_options, search for mo2f_activate_plugin key and update its value to 0. Two Factor will get disabled.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question8" aria-expanded="false" ><li>If you are using any Security Plugin in WordPress like Simple Security Firewall, All in One WP Security Plugin and you are not able to login with Two-Factor.</li></a></h3>
				<div class="collapse" id="question8">
					Our Two-Factor plugin is compatible with most of the security plugins, but if it is not working for you.
				   Please submit a query in our <b>Support Section</b> on right hand side or you can contact us at <b>info@miniorange.com</b>.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question9" aria-expanded="false" ><li>If you are using any render blocking javascript and css plugin like Async JS and CSS Plugin and you are not able to login with Two-Factor or your screen got blank.</li></a></h3>
				<div class="collapse" id="question9">
					If you are using <b>Async JS and CSS Plugin</b>. Please go to its settings and add jquery in the list of exceptions and save settings. It will work. If you are still not able to get it right,
				   Please submit a query in our <b>Support Section</b> on right hand side or you can contact us at <b>info@miniorange.com</b>.
				</div>
				<hr>
				<h3><a  data-toggle="collapse" href="#question10" aria-expanded="false" ><li>I want to enable 2-factor only for administrators ?</li></a></h3>
				<div class="collapse" id="question10">
					2-Factor is enabled by default for administrators on plugin activation. You just need to complete your account setup and configure your mobile from <b>Configure Mobile Tab</b>. Once this is done administrators can login using 2-Factor and other users can still login with their password.
				</div>
				<hr>
			<h3><a  data-toggle="collapse" href="#question11" aria-expanded="false" ><li>I want to enable 2 factor for administrators and end users ?</li></a></h3>
				<div class="collapse" id="question11">
					Go to <b>Login Settings Tab</b> and check <b>Enable 2-Factor for all other users</b>. Enable 2-Factor for admins is checked by default.
				</div>
				<hr>

				<h3><a  data-toggle="collapse" href="#question12" aria-expanded="false" ><li>My phone has no internet connectivity, how can I login?</li></a></h3>
				<div class="collapse" id="question12">
				   You can login using our alternate login method. Please follow below steps to login or <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo#myCarousel2">click here</a> to see how it works.<br>
					<br><ol>
					 <li>Enter your username and click on login with your phone.</li>
					  <li>Click on <b>Phone is Offline?</b> button below QR Code.</li>
					   <li>You will see a textbox to enter one time passcode.</li>
					   <li>Open miniOrange Authenticator app and Go to Soft Token Tab.</li>
					   <li>Enter the one time passcode shown in miniOrange Authenticator app in textbox.</li>
					   <li>Click on submit button to validate the otp.</li>
					   <li>Once you are authenticated, you will be logged in.</li>
					  </ol>
				</div>
				<hr>
			<h3><a  data-toggle="collapse" href="#question13" aria-expanded="false" ><li>My users have different types of phones. What phones are supported?</li></a></h3>
				<div class="collapse" id="question13">
					We support all types of phone. Smart Phones, Basic Phones, Landlines, etc. Go to Setup Two-Factor Tab and select Two-Factor method of your choice from a range of 6 different options.
				</div>
				<hr>
			<h3><a  data-toggle="collapse" href="#question14" aria-expanded="false" ><li>What if a user does not have a smart phone?</li></a></h3>
				<div class="collapse" id="question14">
					You can select OTP over SMS, Phone Call Verification or Email Verification as your Two-Factor method. All these methods are supported on basic phones.
				</div>
				<hr>
			<?php }?>	
			<h3><a data-toggle="collapse" href="#question15" aria-expanded="false" ><li>What if I am trying to login from my phone ?</li></a></h3>
				<div class="collapse" id="question15">
					If you are logging in from your phone, just enter the one time passcode from miniOrange Authenticator App.
					Go to Soft Token Tab to see one time passcode.
				</div>
				<hr>
			<?php if(current_user_can( 'manage_options' )) { ?>
				
			
			<h3><a  data-toggle="collapse" href="#question16" aria-expanded="false" ><li>I want to hide default login form and just want to show login with phone?</li></a></h3>
				<div class="collapse" id="question16">
					You should go to <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_login">Login Settings Tab</a> and check <b>I want to hide default login form.</b> checkbox to hide the default login form. 
					
					
				</div>
				<hr>
			<?php }?>
			<h3><a  data-toggle="collapse" href="#question17" aria-expanded="false" ><li>My phone has no internet connectivity, how can I login?</li></a></h3>
				<div class="collapse" id="question17">
				   You can login using our alternate login method. Please follow below steps to login or <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo#myCarousel2">click here</a> to see how it works.<br>
					<br><ol>
					 <li>Enter your username and click on login with your phone.</li>
					  <li>Click on <b>Phone is Offline?</b> button below QR Code.</li>
					   <li>You will see a textbox to enter one time passcode.</li>
					   <li>Open miniOrange Authenticator app and Go to Soft Token Tab.</li>
					   <li>Enter the one time passcode shown in miniOrange Authenticator app in textbox.</li>
					   <li>Click on submit button to validate the otp.</li>
					   <li>Once you are authenticated, you will be logged in.</li>
					  </ol>
				</div>
				<hr>
			<h3><a  data-toggle="collapse" href="#question18" aria-expanded="false" ><li>My phone is lost, stolen or discharged. How can I login?</li></a></h3>
				<div class="collapse" id="question18">
				    You can login using our alternate login method. Please follow below steps to login or <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo#myCarousel3">click here</a> to see how it works.
					<br><br>
					<ol>
					<li>Enter your username and click on login with your phone.</li>
					  <li>Click on <b>Forgot Phone?</b> button below QR Code.</li>
					   <li>You will see a textbox to enter one time passcode.</li>
					   <li>Check your registered email and copy the one time passcode in this textbox.</li>
					   <li>Click on submit button to validate the otp.</li>
					   <li>Once you are authenticated, you will be logged in.</li>
					   </ol>
				</div>
				<hr>
			<h3><a  data-toggle="collapse" href="#question19" aria-expanded="false" ><li>My phone has no internet connectivity and i am entering the one time passcode from miniOrange Authenticator App, it says Invalid OTP.</li></a></h3>
				<div class="collapse" id="question19">
					Click on the <b>Settings Icon</b> on top right corner in <b>miniOrange Authenticator App</b> and then press <b>Sync button</b> under 'Time correction for codes' to sync your time with miniOrange Servers. If you still can't get it right, submit a query here in our <b>support section</b>.<br><br>
				</div>
				<hr>
				<?php if(current_user_can( 'manage_options' )) { ?>
			
		
			<h3><a  data-toggle="collapse" href="#question20" aria-expanded="false" ><li>I want to go back to default login with password.</li></a></h3>
				<div class="collapse" id="question20">
					You can disable Two Factor from Login settings Tab by unchecking Enable Two Factor Plugin checkbox.
				</div>
				<hr>
		
	
		
	
		
			<h3><a>For any other query/problem/request, please feel free to submit a query in our support section on right hand side. We are happy to help you and will get back to you as soon as possible.</a></h3>
		   	<?php }?>
		</ul>
					
	</div>
	<?php } ?>