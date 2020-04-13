<?php
/*
@version 4.0.0
*/

if(!defined('ABSPATH')) {
    exit;
}

global $product;
?>
<div class="product_price">
	<p class="price"><?php echo $product->get_price_html(); ?></p>
</div>