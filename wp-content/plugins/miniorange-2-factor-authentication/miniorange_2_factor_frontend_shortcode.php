<?php 
if(mo2f_is_customer_registered()){

class MO2F_ShortCode  {
	
 public function mo2FAFormShortCode(){
	
	if( ! is_user_logged_in() ) {
	$html = '';
	$html .="<link rel='stylesheet' id='bootstrap_style-css'  href='". plugins_url('includes/css/bootstrap.min.css?version=3.4', __FILE__) ."' type='text/css' media='all' />
			<link rel='stylesheet' id='2fa_login_style-css'  href='".plugins_url('includes/css/front_end_login.css?version=3.4', __FILE__)."' type='text/css' media='all' />";

	$login_status = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : null;
	
	if($login_status == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS' || $login_status == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL'){
		
	$html .= "<div class='modal' tabindex='-1' role='dialog' id='mo2f-modal1'><div class='mo2f-modal-backdrop'></div>
		<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-header'>
				<button type='button' class='close' data-dismiss='modal' aria-label='Close' onclick='mologinback();'><span aria-hidden='true'>&times;</span></button>
			</div>
		<div class='modal-body center'>
	
			<div id='otpMessage' > 
				<p class='mo2fa_display_message_frontend'>" . $_SESSION['mo2f-login-message'] . "</p>
			</div>
	
			<div id='mo_2_factor_push_page'>
			<center>
				<div id='pushSection'>
					
					<a href='#showPushHelp' id='pushHelpLink' class='mo2f-link'>See How It Works ?</a>
					<br>
				
					<h4>Waiting for your approval...</h4>
			
					<div id='showPushImage' style='margin-bottom:10%;'>
			
						<img src='". plugins_url( 'includes/images/ajax-loader-login.gif' , __FILE__ )."' style='display:inline!important;'/>
		
					</div>
					<div style='display:table-row;'	>";
				if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_PUSH_NOTIFICATIONS'){ 
						if(get_option('mo2f_enable_forgotphone')){
							
				$html .=	"<a name='miniorange_login_forgotphone' onclick='mologinforgotphone();' id='miniorange_login_forgotphone' class='mo2f-link' >Forgot Phone?</a>";
						 } 
				$html .= "	&nbsp;&nbsp;&nbsp;&nbsp;
						<a name='miniorange_login_offline' onclick='mologinoffline();' id='miniorange_login_offline' class='mo2f-link' >Phone is Offline?</a>";
		
						} 
				$html .= "	</div>
						<br>
				
				</div>
				</center>
				<div id='showPushHelp' class='showPushHelp' hidden>
					<br>
					<center><a href='#showPushHelp' id='pushLink' class='mo2f-link'>←Go Back.</a>
					<br>
						<div id='myCarousel' class='carousel slide' data-ride='carousel'>
						  <ol class='carousel-indicators'>
							<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
							<li data-target='#myCarousel' data-slide-to='1'></li>
							<li data-target='#myCarousel' data-slide-to='2'></li>
						</ol>
						<div class='carousel-inner' role='listbox'>";
						if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OOB_EMAIL') {
						
						$html .= "	<div class='item active'>
								
							  <img class='first-slide' src='http://miniorange.com/images/help/email-with-link-login-flow-1.png' alt='First slide'>
							</div>
						   <div class='item'>
							<p>Click on Accept Transaction link to verify your email .</p><br>
							<img class='first-slide' src='http://miniorange.com/images/help/email-with-link-login-flow-2.png' alt='First slide'>
						 
						  </div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/email-with-link-login-flow-3.png' alt='First slide'>
						  </div>";
						 } 
						 else {
						
						 $html .= "	<div class='item active'>
								<p>You will receive a notification on your phone.</p><br>
							  <img class='first-slide' src='http://miniorange.com/images/help/push-login-flow.png' alt='First slide'>
							</div>
						   <div class='item'>
							<p>Open the notification and click on accept button.</p><br>
							<img class='first-slide' src='http://miniorange.com/images/help/push-login-flow-1.png' alt='First slide'>
						 
						  </div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/push-login-flow-2.png' alt='First slide'>
						  </div>";
						  } 
						$html .= "</div>
						</div>
					</center>
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<?php if(get_option('mo2f_enable_custom_poweredby')!=1){?>}
				<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('/miniorange-2-factor-authentication/includes/images/miniOrange2.png');'></div></a></div>
				<?php }else{
					<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('".site_url()."/wp-content/uploads/custom.png');'></div></a></div>
				}?>
				<?php }?>
			</div>
		
		 </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
		<script>
			jQuery('#mo2f-modal1').modal('show');
			
			var timeout;
			pollPushValidation();
			function pollPushValidation()
			{	
				var transId = '". $_SESSION[ 'mo2f-login-transactionId' ] ."';
				
				var jsonString = '{\"txId\":\"'+ transId + '\"}';
				var postUrl = '". get_option('mo2f_host_name') ."/moas/api/auth/auth-status';
				
				jQuery.ajax({
					url: postUrl,
					type : 'POST',
					dataType : 'json',
					data : jsonString,
					contentType : 'application/json; charset=utf-8',
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						
						if (status == 'SUCCESS') {
						
							jQuery('#mo2f_mobile_validation_form').submit();
						} else if (status == 'ERROR' || status == 'FAILED' || status == 'DENIED') {
							
							jQuery('#mo2f_2fa_form_close').submit();
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
				jQuery('#mo2f_2fa_form_close').submit();
			 }
			 function mologinoffline(){
				jQuery('#mo2f_show_softtoken_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			 </script>
	";
	}
	
	if($login_status == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION'){ 
	
	$html .= " <div class='modal' tabindex='-1' role='dialog' id='mo2f-modal2'>
		<div class='mo2f-modal-backdrop'></div>
		<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-header'>
				<button type='button' class='close' data-dismiss='modal' aria-label='Close' onclick='mologinback();'><span aria-hidden='true'>&times;</span></button>
			</div>
				<div class='modal-body center'>";
			
			if(isset($_SESSION['mo2f-login-message']) && $_SESSION['mo2f-login-message'] == 'Error:OTP over Email'){
		$html .= "
			<div  id='otpMessage'> 
				<p class='mo2fa_display_message_frontend'>Error occurred while sending OTP over email. Please try again. </p>
			</div>";
			}
			$html .= "		<div id='scanQRSection'>
					<p>Identify yourself by scanning the QR code with miniOrange Authenticator app.</p>
					<a href='#showQRHelp' id='helpLink' class='mo2f-link'>See How It Works ?</a>
						<br><br>
					<div id='showQrCode' style='margin-bottom:10%;'>
					<center> <img src='data:image/jpg;base64," . $_SESSION[ 'mo2f-login-qrCode' ] . "' /> </center>
					</div>";
					
				if(get_option('mo2f_enable_forgotphone')){ 
					$html .= "	<a name='miniorange_login_forgotphone' onclick='mologinforgotphone();' id='miniorange_login_forgotphone' class='mo2f-link' >Forgot Phone?</a>";
				}
					 
			$html .= "	&nbsp;&nbsp;&nbsp;&nbsp;
						<a name='miniorange_login_offline' onclick='mologinoffline();' id='miniorange_login_offline' class='mo2f-link' >Phone is Offline?</a>
				
				
				</div>
				<div id='showQRHelp' class='showQRHelp' hidden>
					<br>
					<center><a href='#showQRHelp' id='qrLink' class='mo2f-link'>←Back to Scan QR Code.</a>
					<br>
						<div id='myCarousel' class='carousel slide' data-ride='carousel'>
						  <!-- Indicators -->
						  <ol class='carousel-indicators'>
							<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
							<li data-target='#myCarousel' data-slide-to='1'></li>
							<li data-target='#myCarousel' data-slide-to='2'></li>
							<li data-target='#myCarousel' data-slide-to='3'></li>
							<li data-target='#myCarousel' data-slide-to='4'></li>
						  </ol>
						<div class='carousel-inner' role='listbox'>
							<div class='item active'>
							  <img class='first-slide' src='http://miniorange.com/images/help/qr-help-1.png' alt='First slide'>
							</div>
						   <div class='item'>
							<p>Open miniOrange Authenticator app and click on Authenticate.</p><br>
							<img class='first-slide' src='http://miniorange.com/images/help/qr-help-2.png' alt='First slide'>
						 
						  </div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/qr-help-3.png' alt='First slide'>
						  </div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com//images/help/qr-help-4.png' alt='First slide'>
						  </div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/qr-help-5.png' alt='First slide'>
						  </div>
						</div>
						</div>
					</center>
				</div>
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<?php if(get_option('mo2f_enable_custom_poweredby')!=1){?>}
				<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('/miniorange-2-factor-authentication/includes/images/miniOrange2.png');'></div></a></div>
				<?php }else{
					<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('".site_url()."/wp-content/uploads/custom.png');'></div></a></div>
				}?>
				<?php }?>
		</div>
		 </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
		<script>
			
			jQuery('#mo2f-modal2').modal('show');
			
			var timeout;
			pollMobileValidation();
			function pollMobileValidation()
			{
				var transId = '".  $_SESSION[ 'mo2f-login-transactionId' ] ."';
				var jsonString = '{\"txId\":\"'+ transId + '\"}';
				var postUrl = '". get_option('mo2f_host_name') ."/moas/api/auth/auth-status';
				jQuery.ajax({
					url: postUrl,
					type : 'POST',
					dataType : 'json',
					data : jsonString,
					contentType : 'application/json; charset=utf-8',
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							var content = '<div id=\"success\"><center><img src=\"". plugins_url( 'includes/images/right.png' , __FILE__ ) ."\" /></center></div>';
							jQuery('#showQrCode').empty();
							jQuery('#showQrCode').append(content);
							setTimeout(function(){jQuery('#mo2f_mobile_validation_form').submit();}, 100);
						} else if (status == 'ERROR' || status == 'FAILED') {
							 var content = '<div id=\"error\"><center><img src=\"". plugins_url( 'includes/images/wrong.png' , __FILE__ ) ."\" /></center></div>';
							jQuery('#showQrCode').empty();
							jQuery('#showQrCode').append(content);
							setTimeout(function(){jQuery('#mo2f_2fa_form_close').submit();}, 1000);
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
				jQuery('#mo2f_2fa_form_close').submit();
			 }
			 function mologinoffline(){
				jQuery('#mo2f_show_softtoken_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			</script>
	";
	}

	if($login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS' || $login_status == 'MO_2_FACTOR_CHALLENGE_PHONE_VERIFICATION' || $login_status == 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION'){
	
	$html .= "	<div class='modal' tabindex='-1' role='dialog' id='mo2f-modal3'>
		<div class='mo2f-modal-backdrop'></div>
		<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-header'>
				<button type='button' class='close' data-dismiss='modal' aria-label='Close' onclick='mologinback();'><span aria-hidden='true'>&times;</span></button>
			</div>
				<div class='modal-body center'>
     

			<div  id='otpMessage'>
				<p class='mo2fa_display_message_frontend' >". $_SESSION['mo2f-login-message'] . "</p>
			</div> 
			
				<div id='showOTP'>
				<div class='mo2f-login-container'>";
					 if($_SESSION[ 'mo_2factor_login_status' ] != 'MO_2_FACTOR_CHALLENGE_GOOGLE_AUTHENTICATION'){ 
						$html .= " <a href='#showOTPHelp' id='otpHelpLink' class='mo2f-link'>See How It Works ?</a>";
					 } 
					
				
					$html .= " <input type='text' name='mo2fa_softtokenkey'  placeholder='Enter one time passcode' id='mo2fa_softtokenkey' required='true' class='mo2f-textbox' autofocus='true' pattern='[0-9]{4,8}' title='Only digits within range 4-8 are allowed.'/>
					
					<input type='button' name='miniorange_soft_token_submit' onclick='mootploginsubmit();' id='miniorange_soft_token_submit' class='mo2f-button'  value='Validate' />
					<br><br>";
					
			 if(get_option('mo2f_enable_forgotphone') && isset($_SESSION[ 'mo_2factor_login_status' ] ) && $_SESSION[ 'mo_2factor_login_status' ] != 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL'){ 
			 
				$html .= "<a name='miniorange_login_forgotphone'  onclick='mologinforgotphone();' id='miniorange_login_forgotphone' class='mo2f-link'   >Forgot Phone ?</a>";
			 
			 } 
			 
			$html .= "		<br><br>
				</div>
				</div>
			<div id='showOTPHelp' class='showOTPHelp' hidden>
				<br>
					<center><a href='#showOTP' id='otpLink' class='mo2f-link'>←Go Back</a>
				<br>
				<div id='myCarousel' class='carousel slide' data-ride='carousel'> ";
					 
					if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){
					
				$html .= "		<ol class='carousel-indicators'>
						<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
						<li data-target='#myCarousel' data-slide-to='1'></li>
						<li data-target='#myCarousel' data-slide-to='2'></li>
						<li data-target='#myCarousel' data-slide-to='3'></li>
						
						</ol>
						<div class='carousel-inner' role='listbox'>
      
						
						   <div class='item active'>
						   <p>Open miniOrange Authenticator app and click on settings icon on top right corner.</p><br>
						  <img class='first-slide' src='http://miniorange.com/images/help/qr-help-2.png' alt='First slide'>
						  </div>
						   <div class='item'>
						   <p>Click on Sync button below to sync your time with miniOrange Servers. This is a one time sync to avoid otp validation failure.</p><br>
						  <img class='first-slide' src='http://miniorange.com/images/help/token-help-3.png' alt='First slide'>
						  </div>
						  <div class='item'>
						   <p>Go to Soft Token tab.</p><br>
						  <img class='first-slide' src='http://miniorange.com/images/help/token-help-2.png' alt='First slide'>
						  </div>
						  <div class='item'>
						   <p>Enter the one time passcode shown in miniOrange Authenticator app here.</p><br>
						  <img class='first-slide' src='http://miniorange.com/images/help/token-help-4.png' alt='First slide'>
						  </div>
						</div>";
						
				} else  if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL') { 
					
					$html .= " <ol class='carousel-indicators'>
						<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
						<li data-target='#myCarousel' data-slide-to='1'></li>
						<li data-target='#myCarousel' data-slide-to='2'></li>
						
						</ol>
						<div class='carousel-inner' role='listbox'>
							<div class='item active'>
							  <img class='first-slide' src='http://miniorange.com/images/help/otp-help-1.png' alt='First slide'>
							</div>
						   <div class='item'>
						   <p>Check your email with which you registered and copy the one time passcode.</p><br>
							<img class='first-slide' src='http://miniorange.com/images/help/otp-help-2.png' alt='First slide'>
							</div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/otp-help-3.png' alt='First slide'>
						  </div>
						 </div>";
						 
			 } else if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_SMS') { 
					
				$html .= "<ol class='carousel-indicators'>
						<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
						<li data-target='#myCarousel' data-slide-to='1'></li>
						<li data-target='#myCarousel' data-slide-to='2'></li>
						
						</ol>
						<div class='carousel-inner' role='listbox'>
							<div class='item active'>
							  <img class='first-slide' src='http://miniorange.com/images/help/otp-over-sms-login-flow-1.png' alt='First slide'>
							</div>
						   <div class='item'>
							<img class='first-slide' src='http://miniorange.com/images/help/otp-over-sms-login-flow-2.png' alt='First slide'>
							</div>
						  <div class='item'>
						  <img class='first-slide' src='http://miniorange.com/images/help/otp-over-sms-login-flow-3.png' alt='First slide'>
						  </div>
						 </div>";
					 } else { 
				$html .=	"<!-- phone call verification  -->
						<ol class='carousel-indicators'>
						<li data-target='#myCarousel' data-slide-to='0' class='active'></li>
						<li data-target='#myCarousel' data-slide-to='1'></li>
						
						
						</ol>
						<div class='carousel-inner' role='listbox'>
							<div class='item active'>
								<p>You will receive a phone call. Pick up the call and listen to the one time passcode carefully. </p>
							  <img class='first-slide' src='http://miniorange.com/images/help/phone-call-login-flow-2.png' alt='First slide'>
							</div>
						   <div class='item'>
						   <p>Enter the one time passcode here and click on validate button to login.</p><br>
							<img class='first-slide' src='http://miniorange.com/images/help/phone-call-login-flow.png' alt='First slide'>
							</div>
						  
						 </div>";
					 } 
					
				$html .= "</div>
					</div>	
				<?php if(get_option('mo2f_disable_poweredby') != 1 ){?>
				<?php if(get_option('mo2f_enable_custom_poweredby')!=1){?>}
				<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('/miniorange-2-factor-authentication/includes/images/miniOrange2.png');'></div></a></div>
				<?php }else{
					<div class='mo2f_powered_by_div'><a target='_blank' href='http://miniorange.com/2-factor-authentication'><div class='mo2f_powered_by_miniorange' style='background-image: url('".site_url()."/wp-content/uploads/custom.png');'></div></a></div>
				}?>
				<?php }?>
			</div>
		
    
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

		<script>
		
			jQuery('#mo2f-modal3').modal('show');
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
			
			function mologinback(){
				jQuery('#mo2f_2fa_form_close').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			  function mootploginsubmit(){
				var otpkey = jQuery('#mo2fa_softtokenkey').val();
				document.getElementById('mo2f_submitotp_loginform').elements[0].value = otpkey;
				jQuery('#mo2f_submitotp_loginform').submit();
				
			 }
			 
			 jQuery('#mo2fa_softtokenkey').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var otpkey = jQuery('#mo2fa_softtokenkey').val();
					document.getElementById('mo2f_submitotp_loginform').elements[0].value = otpkey;
					jQuery('#mo2f_submitotp_loginform').submit();
				  }
				 
			});
			
			

		</script>
		";
	} 
	
	$html .= "<form name='f' id='mo2f_show_softtoken_loginform' method='post' action='' style='display:none;'>
			<input type='hidden' name='miniorange_softtoken' value='". wp_create_nonce('miniorange-2-factor-softtoken') . "' />
		</form>
		<form name='f' id='mo2f_show_forgotphone_loginform' method='post' action='' style='display:none;'>
			<input type='hidden' name='miniorange_forgotphone' value='" . wp_create_nonce('miniorange-2-factor-forgotphone') . "' />
		</form>
		<form name='f' id='mo2f_2fa_form_close' method='post' style='display:none;'>
			<input type='hidden' name='miniorange_mobile_validation_failed_nonce' value='" . wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce') ."' />
		</form>
		<form name='f' id='mo2f_mobile_validation_form' method='post' action='' style='display:none;'>
			<input type='hidden' name='miniorange_mobile_validation_nonce' value='" . wp_create_nonce('miniorange-2-factor-mobile-validation-nonce') ."' />
		</form>
		<form name='f' id='mo2f_submitotp_loginform' method='post' action='' style='display:none;'>
			<input type='text' name='mo2fa_softtoken' id='mo2fa_softtoken' hidden/>
			<input type='hidden' name='miniorange_soft_token_nonce' value='" . wp_create_nonce('miniorange-2-factor-soft-token-nonce') ."' />
		</form>";
		
		return $html;
	
	}
	
}

	public function mo2FALoginFormShortCode(){
		if( ! is_user_logged_in() ) {
			
			$html = '';
			$html .= "<input type='hidden' name='miniorange_login_nonce' value='". wp_create_nonce('miniorange-2-factor-login-nonce') ."' />";
			return $html;
		}
	
	}
	
	
}

}
?>