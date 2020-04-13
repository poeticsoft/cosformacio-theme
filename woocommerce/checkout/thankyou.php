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
		<?php if($order){ ?>
			<?php if($order->has_status( 'failed')){ ?>
				<p><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'academy'); ?></p>
				<p><?php
					if(is_user_logged_in())
						_e( 'Please attempt your purchase again or go to your account page.', 'academy');
					else
						_e( 'Please attempt your purchase again.', 'academy');
				?></p>
				<p>
					<a href="<?php echo esc_url( $order->get_checkout_payment_url()); ?>" class="button pay"><?php _e( 'Pay', 'academy') ?></a>
					<?php if(is_user_logged_in()) : ?>
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount'))); ?>" class="button pay"><?php _e( 'My Account', 'academy'); ?></a>
					<?php endif; ?>
				</p>
			<?php } else { ?>
				<p><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'academy'), $order); ?></p>
				<ul class="order_details">
					<li class="order">
						<?php _e( 'Order:', 'academy'); ?>
						<strong><?php echo $order->get_order_number(); ?></strong>
					</li>
					<li class="date">
						<?php _e( 'Date:', 'academy'); ?>
						<strong><?php echo date_i18n( get_option( 'date_format'), strtotime( $order->get_date_created())); ?></strong>
					</li>
					<li class="total">
						<?php _e( 'Total:', 'academy'); ?>
						<strong><?php echo $order->get_formatted_order_total(); ?></strong>
					</li>
					<?php if($order->get_payment_method_title()) : ?>
					<li class="method">
						<?php _e( 'Payment method:', 'academy'); ?>
						<strong><?php echo $order->get_payment_method_title(); ?></strong>
					</li>
					<?php endif; ?>
				</ul>
				<div class="clear"></div>
			<?php } ?>
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id()); ?>
		<?php } else { ?>
			<p><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'academy'), null); ?></p>
		<?php } ?>
		</div>
	<?php get_sidebar('profile-right'); ?>
</div>
<?php remove_filter('the_title', 'wc_page_endpoint_title'); ?>