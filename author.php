<?php
/*
Template Name: Profile Details
*/
?>
<?php get_header(); ?>
<div class="user-profile">
	<?php if(ThemexUser::isProfile()) { ?>
	<?php get_template_part('template', 'profile'); ?>
	<?php } else { ?>
	<div class="column fourcol">
		<div class="bordered-image thick-border">
			<?php echo get_avatar(ThemexUser::$data['active_user']['ID'], 200); ?>
		</div>
		<?php get_template_part('module', 'links'); ?>
	</div>
	<div class="column fourcol">
		<h1><?php echo ThemexUser::$data['active_user']['profile']['full_name']; ?></h1>
		<div class="signature"><?php echo ThemexUser::$data['active_user']['profile']['signature']; ?></div>
		<?php if(ThemexForm::isActive('profile')) { ?>
		<table class="user-fields">
			<?php 
			ThemexForm::renderData('profile', array(
				'edit' => false,
				'before_title' => '<tr><th>',
				'after_title' => '</th>',
				'before_content' => '<td>',
				'after_content' => '</td></tr>', 
			), ThemexUser::$data['active_user']['profile']);
			?>
		</table>
		<?php } ?>
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
	<?php } ?>
	<?php if(!ThemexCore::checkOption('profile_courses') || ThemexUser::isProfile()) { ?>
	<?php get_sidebar('profile-right'); ?>
	<?php } ?>
</div>
<?php get_footer(); ?>