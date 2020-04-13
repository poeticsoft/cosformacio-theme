<?php 
	ThemexCourse::refresh($post->ID);
	$ProductID = ThemexCourse::getCourse($post->ID)['product'];
	$Product = wc_get_product($ProductID);
	$RoleBasedPrice = new WooCommerce_Role_Based_Price_Product_Pricing;
	$SubscriberPrice = 0;
	$CustomerPrice = 0;
	if($Product) {		
		$SubscriberPrice = $RoleBasedPrice->get_product_price(
			'', 
			$Product, 
			'regular_price', 
			'subscriber'
		); 
		$CustomerPrice = $RoleBasedPrice->get_product_price(
			'', 
			$Product, 
			'regular_price', 
			'customer'
		);
	}
?>
<div style="display: none"><?php echo $ProductID; ?></div>
<div class="course-preview <?php echo ThemexCourse::$data['status']; ?>-course">
	<div class="course-image">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('normal'); ?></a>
		<?php 
			if(empty(ThemexCourse::$data['plans']) && ThemexCourse::$data['status']!='private') { ?>
			<div class="course-price product-price soci-price">
				<div class="price-text">
					<span class="text">
						<?php _e('Subscriber','academy'); ?>
					</span>
					<span class="price">
						<?php echo wc_price($SubscriberPrice)?>
					</span>
				</div>
				<div class="corner-wrap">
					<div class="corner"></div>
					<div class="corner-background"></div> 
				</div>
			</div>	
			<div class="course-price product-price no-soci-price">
				<div class="price-text">
					<span class="text">
						<?php _e('Customer','academy'); ?>
					</span>
					<span class="price">
						<?php echo wc_price($CustomerPrice);?>
					</span>
				</div>	
				<div class="corner-wrap">
					<div class="corner"></div>
					<div class="corner-background"></div> 
				</div>	
			</div>	
		<?php } ?>
	</div>
	<div class="course-meta">
		<header class="course-header">
			<h5 class="nomargin"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
			<?php if(!ThemexCore::checkOption('course_author')) { ?>
			<a href="<?php echo ThemexCourse::$data['author']['profile_url']; ?>" class="author"><?php echo ThemexCourse::$data['author']['profile']['full_name']; ?></a>
			<?php } ?>
		</header>
		<?php if(!ThemexCore::checkOption('course_popularity') || !ThemexCore::checkOption('course_rating')) { ?>
		<footer class="course-footer clearfix">
			<?php if(!ThemexCore::checkOption('course_popularity')) { ?>
			<div class="course-users left">
				<?php echo ThemexCore::getPostMeta($post->ID, 'course_popularity', '0'); ?>
			</div>
			<?php } ?>
			<?php if(!ThemexCore::checkOption('course_rating')) { ?>
			<?php get_template_part('module', 'rating'); ?>
			<?php } ?>
		</footer>
		<?php } ?>
	</div>
</div>