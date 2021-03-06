<?php ThemexUser::refresh($GLOBALS['user']->ID); ?>
<div class="expert-preview">
	<div class="expert-meta">
		<div class="expert-image bordered-image">
			<a href="<?php echo ThemexUser::$data['active_user']['profile_url']; ?>">
			<?php echo get_avatar(ThemexUser::$data['active_user']['ID'], 300); ?>
		</a>
		</div>
		<?php get_template_part('module', 'links'); ?>
	</div>
	<div class="expert-text">
		<h4 class="nomargin">
			<a href="<?php echo ThemexUser::$data['active_user']['profile_url']; ?>">
			<?php echo ThemexUser::$data['active_user']['profile']['full_name']; ?>
			</a>
		</h4>
		<span class="expert-signature"><?php echo ThemexUser::$data['active_user']['profile']['signature']; ?></span>
		<?php 
			if(pll_current_language() == 'es') {
				
				echo themex_sections(ThemexUser::$data['active_user']['profile']['description'], 1); 
			}
		?>
		<?php 
			 
			if(pll_current_language() == 'ca') {

				echo themex_sections(ThemexUser::$data['active_user']['profile']['ca_description'], 1); 
			}
		?>
	</div>
</div>