<?php
/**
 * Themex Woo
 *
 * Handles WooCommerce data
 *
 * @class ThemexWoo
 * @author Themex
 */
 
class ThemexWoo {

	/** @var array Contains module data. */
	public static $data;
	
	/**
	 * Adds actions and filters
     *
     * @access public
     * @return void
     */
	public static function init() {	
		//refresh data
		self::refresh();
		
		if(self::isActive()) {
			
			//order actions
			add_action('woocommerce_order_status_completed', array(__CLASS__, 'completeOrder'));
			add_action('subscriptions_activated_for_order', array(__CLASS__, 'completeOrder'));	
			
			add_action('woocommerce_order_status_processing', array(__CLASS__, 'uncompleteOrder'));
			add_action('woocommerce_order_status_refunded', array(__CLASS__, 'uncompleteOrder'));
			add_action('subscriptions_cancelled_for_order', array(__CLASS__, 'uncompleteOrder'));
			
			//product filters
			add_filter('woocommerce_output_related_products_args', array(__CLASS__, 'updateRelatedLimit'));
			add_action('woocommerce_after_single_product_summary', array(__CLASS__, 'updateUpsellLimit'), 15);
			
			//checkout filters
			add_filter('woocommerce_checkout_fields', array(__CLASS__, 'filterFields'), 10, 1);
			
			//remove actions
			remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('template_redirect', 'wc_disable_author_archives_for_customers');
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
			
			//add actions
			add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 10);
		}
	}
	
	/**
	 * Refreshes module data
     *
     * @access public
     * @return void
     */
	public static function refresh() {
		self::$data=(array)ThemexCore::getOption(__CLASS__);
	}
	
	/**
	 * Renders module settings
     *
     * @access public
     * @return string
     */
	public static function renderSettings() {
		$out='<input type="hidden" name="'.__CLASS__.'[]" value="" />';
	
		$out.=ThemexInterface::renderOption(array(	
			'name' => __('Show Country', 'academy'),
			'id' => __CLASS__.'[billing_country]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_country'),
		));
		
		$out.=ThemexInterface::renderOption(array(	
			'name' => __('Show City', 'academy'),
			'id' => __CLASS__.'[billing_city]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_city'),
		));
			
		$out.=ThemexInterface::renderOption(array(
			'name' => __('Show State', 'academy'),
			'id' => __CLASS__.'[billing_state]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_state'),
		));
			
		$out.=ThemexInterface::renderOption(array(
			'name' => __('Show Address', 'academy'),
			'id' => __CLASS__.'[billing_address]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_address'),
		));
			
		$out.=ThemexInterface::renderOption(array(	
			'name' => __('Show Postcode', 'academy'),
			'id' => __CLASS__.'[billing_postcode]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_postcode'),
		));
			
		$out.=ThemexInterface::renderOption(array(	
			'name' => __('Show Company', 'academy'),
			'id' => __CLASS__.'[billing_company]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_company'),
		));
			
		$out.=ThemexInterface::renderOption(array(
			'name' => __('Show Phone', 'academy'),
			'id' => __CLASS__.'[billing_phone]',
			'type' => 'checkbox',
			'default' => themex_value(self::$data, 'billing_phone'),
		));
	
		return $out;
	}
	
	/**
	 * Gets product price
     *
     * @access public
	 * @param int $ID
	 * @param bool $numeric
     * @return string
     */
	public static function getPrice($ID) {
		$price['text']=__('Free', 'academy');
		$price['number']=0;
		$price['type']='simple';
		
		if(self::isActive() && $ID!=0) {
			$product=wc_get_product($ID);
			
			if($product!==false) {
				$price['type']=$product->get_type();
				$price['text']=$product->get_price_html();
				$price['number']=$product->get_price();
			}
		}

		return $price;
	}
	
	/**
	 * Adds product to cart
     *
     * @access public
	 * @param int $ID
     * @return void
     */
	public static function addProduct($ID) {
		
		if(self::isActive()) {
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart($ID, 1);
			wp_redirect(WC()->cart->get_checkout_url());
			exit();
		}
	}
	
	/**
	 * Completes order
     *
     * @access public
	 * @param int $ID
     * @return void
     */
	public static function completeOrder($ID) {
		$relations=ThemexWoo::getRelatedPost($ID, array('course_product', 'plan_product'));
		
		if(!empty($relations)) {
			foreach($relations as $related) {
				if($related->post_type=='course') {
					ThemexCourse::addUser($related->ID, $related->post_author, true);
				} else if($related->post_type=='plan') {
					ThemexCourse::subscribeUser($related->ID, $related->post_author, true);
				}
			}
		}		
	}
	
	/**
	 * Uncompletes order
     *
     * @access public
	 * @param int $ID
     * @return void
     */
	public static function uncompleteOrder($ID) {
		$relations=ThemexWoo::getRelatedPost($ID, array('course_product', 'plan_product'));
		
		if(!empty($relations)) {
			foreach($relations as $related) {
				if($related->post_type=='course') {
					ThemexCourse::removeUser($related->ID, $related->post_author, true);
				} else if($related->post_type=='plan') {
					ThemexCourse::unsubscribeUser($related->ID, $related->post_author, true);
				}
			}
		}	
	}
	
	/**
	 * Gets order note
     *
     * @access public
	 * @param int $ID
     * @return string
     */
	public static function getNote($ID) {
		$note='';
		
		if(self::isActive()) {
			$args=array(
				'post_id' => $ID,
				'approve' => 'approve',
				'type' => 'order_note',			
				'meta_key' => 'is_customer_note',
				'meta_value' => '1',
				'number' => 1,
			);

			remove_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);
			$comments=get_comments( $args );
			add_filter('comments_clauses', array('WC_Comments', 'exclude_order_comments'), 10, 1);
			
			if(!empty($comments)) {
				$comment=reset($comments);
				$note=$comment->comment_content;
			}
		}
		
		return $note;
	}
	
	/**
	 * Gets related post
     *
     * @access public
	 * @param int $ID
	 * @param mixed $type
	 * @param bool $single
     * @return array
     */
	public static function getRelatedPost($ID, $type, $single=false) {
		$order=wc_get_order($ID);
		
		if(!empty($order)) {
			$products=$order->get_items();
			
			if(!empty($products)) {
				$ID=array();
				
				foreach($products as $product) {
					$ID[]=$product->get_product_id();					
				}
			}
		}

		$relations=array_merge(array(0), ThemexCore::getPostRelations(0, $ID, $type));
		$posts=get_posts(array(
			'numberposts' => -1,
			'post_type' => array('course', 'plan'),
			'post__in' => $relations,
		));
		
		if(!empty($posts)) {
			if(!empty($order)) {
				foreach($posts as &$post) {
					$post->post_author=$order->get_user_id();
				}
			}
			
			if($single) {
				$posts=reset($posts);
			}
		}
		
		return $posts;
	}
	
	/**
	 * Filters checkouts
     *
     * @access public
	 * @param array $fields
     * @return array
     */
	public static function filterFields($fields) {
		$optional=array(
			'billing_company',
			'billing_phone',
			'billing_country',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'shipping_company',
			'shipping_country',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
		);
		
		foreach($fields as $form_key => $form) {
			foreach($form as $field_key => $field) {
				$short_key=str_replace(array('shipping_', 'billing_', '_1', '_2'), '', $field_key);				
				if(!in_array($field_key, $optional) || isset(self::$data[$field_key]) || isset(self::$data['billing_'.$short_key]) || isset(self::$data['shipping_'.$short_key])) {
					if(isset($fields[$form_key][$field_key]['label'])) {
						$fields[$form_key][$field_key]['placeholder']=$fields[$form_key][$field_key]['label'];
					}
				} else {
					unset($fields[$form_key][$field_key]);
				}
			}
		}
		
		return $fields;
	}
	
	/**
	 * Updates related limit
     *
     * @access public
	 * @param array $args
     * @return array
     */
	public static function updateRelatedLimit($args) {
		$args['columns']=4;
		$args['posts_per_page']=ThemexCore::getOption('product_related_number', 4);
		
		return $args;
	}
	
	/**
	 * Updates upsell limit
     *
     * @access public
     * @return void
     */
	public static function updateUpsellLimit() {
		$columns=4;
		$limit=ThemexCore::getOption('product_upsell_number', 4);
		
		woocommerce_upsell_display($limit, $columns);
	}
	
	/**
	 * Checks checkout page
     *
     * @access public
     * @return bool
     */
	public static function isCheckout() {
		if(self::isActive() && (is_checkout() || isset($_GET['order']))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks plugin activity
     *
     * @access public
     * @return bool
     */
	public static function isActive() {
		if(class_exists('WooCommerce')) {
			return true;
		}
		
		return false;
	}
}