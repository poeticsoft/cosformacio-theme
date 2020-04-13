<?php
class ThemexTwitter extends WP_Widget {

	//Widget Setup
	function __construct() {
		$widget_ops=array('classname' => 'widget_recent_tweets', 'description' => __('The most recent tweets', 'academy'));
		parent::__construct('recent-tweets', __('Recent Tweets','academy'), $widget_ops);
		$this->alt_option_name='widget_recent_tweets';
		
		if(is_active_widget( false, false, $this->id_base, true)) {
			add_action('wp_head', array($this, 'enqueue'));
		}
	}

	//Widget View
	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		$instance=wp_parse_args((array)$instance, array(
			'title' => __('Recent Tweets', 'academy'),
			'id' => 'themextemplates',
			'number' => '3',
		));
		
		$title=apply_filters( 'widget_title', empty($instance['title'])?__('Recent Tweets', 'academy'):$instance['title'], $instance, $this->id_base);
		
		$out=$before_widget;
		$out.=$before_title.$title.$after_title;
		
		$out.='<div id="widget-tweets"></div>';
		$out.='<input type="hidden" class="twitter-id" value="'.$instance['id'].'" />';
		$out.='<input type="hidden" class="twitter-number" value="'.$instance['number'].'" />';
		
		$out.=$after_widget;
		echo $out;
	}
	
	//Widget Scripts
	function enqueue() {
		wp_enqueue_script('twitterFetcher', THEME_URI.'js/jquery.twitterFetcher.js', array('jquery'));
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.<?php echo $this->alt_option_name; ?>').each(function() {
				twitterFetcher.fetch({
					'profile': {
						'screenName': $(this).find('input.twitter-id').val(),
					},
					'domId': 'widget-tweets',
					'maxTweets': $(this).find('input.twitter-number').val(),
					'enableLinks': true, 
					'showUser': false,
					'showTime': false,
					'showImages': false,
					'showInteraction': false,
				});
			});
		});
		</script>
		<?php
	}

	//Update Widget
	function update($new_instance, $old_instance) {
		$instance=$old_instance;
		$instance['title']=$new_instance['title'];
		$instance['id']=sanitize_key($new_instance['id']);
		$instance['number']=intval($new_instance['number']);
		
		return $instance;
	}
	
	//Widget Form
	function form($instance) {
		$instance=wp_parse_args( (array)$instance, array(
			'number'=>'3',
			'title'=>'',
			'id'=>'',
		));
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'academy'); ?>:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Username', 'academy'); ?>:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" value="<?php echo $instance['id']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number', 'academy'); ?>:</label>
			<input class="widefat" type="number" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $instance['number']; ?>" />
		</p>
	<?php
	}
}