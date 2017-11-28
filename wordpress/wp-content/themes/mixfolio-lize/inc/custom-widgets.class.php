<?php

/**
 * Categories widget class for post_type environment
 *
 * @since 1.0
 */
class widgetCategoriesEnvironment extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widgetCategoriesEnvironment', 'description' => __( "A list or dropdown of categories for environment custm post type" ) );
		parent::__construct('categories_environment', __('Categories for Environment'), $widget_ops);
	}

	/**
	 * @staticvar bool $first_dropdown
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		$title = apply_filters( 'widget_title_custom', $title, $instance, $this->id_base );

		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';
		$post_type = ! empty( $instance['post_type'] ) ? $instance['post_type'] : '';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array(
			'orderby'      => 'name',
			'show_count'   => $c,
			'hierarchical' => $h
		);

		if ( $d ) {
			$dropdown_id = ( $first_dropdown ) ? 'cat' : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;

			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>';

			$cat_args['show_option_none'] = __( 'Select Category' );
			$cat_args['id'] = $dropdown_id;
			?>

<script type='text/javascript'>
/* <![CDATA[ */
(function() {
	var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
	function onCatChange() {
		if ( dropdown.options[ dropdown.selectedIndex ].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat=" + dropdown.options[ dropdown.selectedIndex ].value;
		}
	}
	dropdown.onchange = onCatChange;
})();
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';

		/**
		 * Filter the arguments for the Categories widget.
		 *
		 * @since 2.8.0
		 *
		 * @param array $cat_args An array of Categories widget options.
		 */
		 
		global $wpdb;
		$categories = $wpdb->get_results("SELECT d.name, d.slug, c.count as count FROM `wp_posts` a
			left outer join wp_term_relationships b on b.object_id = a.id
			left outer join wp_term_taxonomy c on c.term_taxonomy_id = b.term_taxonomy_id AND c.taxonomy = 'category'
			left outer join wp_terms d on d.term_id = c.term_taxonomy_id
			where a.post_type = '$post_type'");
		$obj = get_post_type_object($post_type);
		$count = 0;
		foreach($categories as $value){
			$count += $value->count;
		}
		?>
        <li><a href="<?php echo home_url();?>/environment">All categories</a> (<?php echo $count; ?>)</li>
        <?php
		foreach($categories as $val){
			?>
            <li><a href="<?php echo home_url(). '/' .$obj->rewrite['slug']; ?>?category=<?php echo $val->slug; ?>"><?php echo ucfirst($val->name); ?></a> (<?php echo $val->count; ?>)</li>
            <?php
		}
		?>
		</ul>
		<?php
		}

		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
		$instance['post_type'] = !empty($new_instance['post_type']) ? $new_instance['post_type'] : 0;
		return $instance;
	}

	/**
	 * @param array $instance
	 */
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label>
        
        <br />
        <select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
		<?php
		$pt = get_post_types();	
		foreach($pt as $pt_value){
			echo "<option value='$pt_value' " . selected($pt_value, $post_type, false) . ">" .$pt_value. "</option>";
		}
		?>
		</select><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type'); ?></label>
        </p>
        <?php
	}

}

// Register and load the widget
function lize_load_widget() {
	register_widget( 'widgetCategoriesEnvironment' );
}
add_action( 'widgets_init', 'lize_load_widget' );