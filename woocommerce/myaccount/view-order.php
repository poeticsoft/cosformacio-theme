<?php
/*
@version 4.0.0
*/

if(!defined('ABSPATH')) {
    exit;
}
?>
<div class="user-profile">
	<?php get_sidebar('profile-left'); ?>
	<div class="column fourcol">
		<?php wc_print_notices(); ?>DFGHDFHGDFGH
		<?php do_action('woocommerce_view_order', $order_id); ?>
	</div>
	<?php get_sidebar('profile-right'); ?>
</div>