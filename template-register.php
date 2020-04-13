<?php
/*
Template Name: Registration
*/
$PostRedirect = esc_attr(themex_value($_POST, 'user_redirect'));
$UserRedirect = $PostRedirect ? $PostRedirect : $_GET["user_redirect"];

?>
<?php get_header(); ?>
<?php if(get_option('users_can_register')) { ?>
<div class="eightcol column CosRegister">
	<?php if($UserRedirect) { ?>
    <p>
      <?php _e('You must register to sign up for a course...','academy'); ?>
    </p>
  <?php } ?>
	<form class="ajax-form formatted-form" action="<?php echo AJAX_URL; ?>" method="POST">
		<div class="message"></div>
		<div class="sixcol column">
			<div class="field-wrapper">
				<input type="text" name="user_login" placeholder="<?php _e('Username','academy'); ?>" />
			</div>								
		</div>
		<div class="sixcol column last">
			<div class="field-wrapper">
				<input type="text" name="user_email" placeholder="<?php _e('Email','academy'); ?>" />
			</div>
		</div>
		<div class="clear"></div>
		<div class="sixcol column">
			<div class="field-wrapper">
				<input type="password" name="user_password" placeholder="<?php _e('Password','academy'); ?>" />
			</div>
		</div>
		<div class="sixcol column last">
			<div class="field-wrapper">
				<input type="password" name="user_password_repeat" placeholder="<?php _e('Repeat Password','academy'); ?>" />
			</div>
		</div>
		<div class="twelve column last Agree">
			<div class="field-wrapper agree-terms">
				<input type="checkbox" name="agree_terms" id="agree_terms"/>
				<label for="agree_terms"><?php _e('Agree terms','academy'); ?></label>
			</div>
			<div class="field-wrapper confirm-subscriber">
				<input type="checkbox" name="confirm_subscriber" id="confirm_subscriber"/>
				<label for="confirm_subscriber"><?php _e('Confirm Subscriber','academy'); ?></label>
			</div>
		</div>
		<div class="clear"></div>			
		<?php if(ThemexCore::checkOption('user_captcha')) { ?>
		<div class="form-captcha">
			<img src="<?php echo THEMEX_URI; ?>assets/images/captcha/captcha.php" alt="" />
			<input type="text" name="captcha" id="captcha" size="6" value="" />
		</div>
		<div class="clear"></div>
		<?php } ?>
		<a href="#" class="element-button submit-button left"><span class="button-icon register"></span><?php _e('Register','academy'); ?></a>
		<div class="form-loader"></div>
		<input type="hidden" name="user_action" value="register_user" />
		<input type="hidden" name="user_redirect" value="<?php echo $UserRedirect; ?>" />
		<input type="hidden" name="nonce" class="nonce" value="<?php echo wp_create_nonce(THEMEX_PREFIX.'nonce'); ?>" />
		<input type="hidden" name="action" class="action" value="<?php echo THEMEX_PREFIX; ?>update_user" />
	</form>
</div>
<?php } ?>
<div class="fourcol column last">
	<?php if(get_option('users_can_register') AND $UserRedirect) { ?>
		<?php if($UserRedirect) { ?><p><?php _e('...or enter your credentials','academy'); ?></p> <?php } ?>
	<?php } ?>
	<form class="ajax-form formatted-form" action="<?php echo AJAX_URL; ?>" method="POST">
		<div class="message"></div>
		<div class="field-wrapper">
			<input type="text" name="user_login" placeholder="<?php _e('Username','academy'); ?>" />
		</div>
		<div class="field-wrapper">
			<input type="password" name="user_password" placeholder="<?php _e('Password','academy'); ?>" />
		</div>			
		<a href="#" class="element-button submit-button left"><span class="button-icon login"></span><?php _e('Sign In','academy'); ?></a>
		<?php if(ThemexFacebook::isActive()) { ?>
		<span class="element-button facebook-button left" title="<?php _e('Sign in with Facebook','academy'); ?>" data-redirect="<?php echo ThemexFacebook::getURL(); ?>">
			<span class="button-icon facebook"></span>
		</span>
		<?php } ?>
		<div class="form-loader"></div>
		<input type="hidden" name="user_action" value="login_user" />
		<input type="hidden" name="user_redirect" value="<?php echo $UserRedirect; ?>" />
		<input type="hidden" name="nonce" class="nonce" value="<?php echo wp_create_nonce(THEMEX_PREFIX.'nonce'); ?>" />
		<input type="hidden" name="action" class="action" value="<?php echo THEMEX_PREFIX; ?>update_user" />
	</form>			
</div>
<?php get_footer(); ?>