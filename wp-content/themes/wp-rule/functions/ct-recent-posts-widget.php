<?php
/*
-----------------------------------------------------------------------------------

	Plugin Name: CT Recent Posts Widget
	Plugin URI: http://www.color-theme.com
	Description: A widget that show recent posts ( Specified by cat-id )
	Version: 1.0
	Author: ZERGE
	Author URI:  http://www.color-theme.com
 
-----------------------------------------------------------------------------------
*/


/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'ct_posts_load_widgets' );

function ct_posts_load_widgets()
{
	register_widget('CT_Recent_Posts_Widget');
}


/**
 * Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update. 
 *
 */
class CT_Recent_Posts_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function CT_Recent_Posts_Widget()
	{
		/* Widget settings. */
		$widget_ops = array('classname' => 'ct_posts_widget', 'description' => __( 'Display recent posts by categories (or related)' , 'color-theme-framework' ) );
		
		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350, 'id_base' => 'ct_posts_widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'ct_posts_widget', __( 'CT: Recent Posts' , 'color-theme-framework' ), $widget_ops, $control_ops);
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		global $wpdb;
		$time_id = rand();

		/* Our variables from the widget settings. */
		$title = $instance['title'];
		$num_posts = $instance['num_posts'];
		$categories = $instance['categories'];
		$show_image = isset($instance['show_image']) ? 'true' : 'false';
		$show_related = isset($instance['show_related']) ? 'true' : 'false';
		$show_comments = isset($instance['show_comments']) ? 'true' : 'false';
		$show_date = isset($instance['show_date']) ? 'true' : 'false';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ){
			echo "\n<!-- START RECENT POSTS WIDGET -->\n";
			echo $before_title.$title.$after_title;
		} else {
			echo "\n<!-- START RECENT POSTS WIDGET -->\n";
		}
		?>
			
		<?php 
			global $post, $ct_data;

			if ( $show_related == 'true' ) { //show related category
				$related_category = get_the_category($post->ID);
				$related_category_id = get_cat_ID( $related_category[0]->cat_name );			
			  
				$recent_posts = new WP_Query(array(
					'showposts'				=> $num_posts,
					'cat'					=> $related_category_id, 
					'post__not_in'			=> array( $post->ID ),
					'ignore_sticky_posts'	=> 1
				));
			}
			else {
				$recent_posts = new WP_Query(array(
					'showposts'				=> $num_posts,
					'cat'					=> $categories,
					'ignore_sticky_posts'	=> 1
				));
			}
		?>

	<?php if ($recent_posts->have_posts()) : ?>
		<ul class="recent-posts-widget recent-widget-<?php echo $time_id; ?>">
			<?php while($recent_posts->have_posts()): $recent_posts->the_post(); ?>
				<li class="clearfix">
					<?php
					if( $show_image == 'true' ):
						if(has_post_thumbnail()):
							$image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'small-thumb'); 
							if ( $image[1] == 75 && $image[2] == 75 ) : //if has generated thumb ?>
								<div class="widget-post-small-thumb">
									<a href='<?php the_permalink(); ?>' title='<?php _e('Permalink to ','color-theme-framework'); the_title(); ?>'><img src="<?php echo $image[0]; ?>" alt="<?php the_title(); ?>" /></a>
								</div><!-- widget-post-small-thumb -->
							<?php 
							else : // else use standard 150x150 thumb
								$image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail'); ?>
								<div class="widget-post-small-thumb">
									<a href='<?php the_permalink(); ?>' title='<?php _e('Permalink to ','color-theme-framework'); the_title(); ?>'><img src="<?php echo $image[0]; ?>" alt="<?php the_title(); ?>" /></a>
								</div><!-- widget-post-small-thumb -->
							<?php
							endif;
						endif; //has_post_thumbnail
					endif; //show_image ?>

					<div class="post-title">
						<h5><a href='<?php the_permalink(); ?>' title='<?php _e('Permalink to ','color-theme-framework'); the_title(); ?>'><?php the_title(); ?></a></h5>						
					</div><!-- post-title -->

					<?php if ( ( $show_date == 'true' ) or ( $show_comments == 'true') ) : ?>
						<div class="entry-widget-date">							
							<?php 
								if ( $show_date == 'true' )	echo esc_attr( get_the_date( 'M j, Y' ) ); ?> 
								<?php
									if ( $show_comments == 'true' ) {
										_e( '&mdash;&nbsp;' , 'color-theme-framework'); 
										comments_popup_link(__('Leave a comment','color-theme-framework'),__('1 Comment','color-theme-framework'),__('% Comments','color-theme-framework')); 
									}
								?>									
						</div> <!-- /entry-widget-date -->	
					<?php endif; ?>

					<footer class="meta clearfix">
						<?php ct_get_post_meta( $post->ID, false, false, false ); ?>
					</footer><!-- .meta -->
				</li>	
			<?php endwhile; ?>
		</ul>
	<?php else : echo __('No posts were found for display','color-theme-framework');
	endif; ?>

		<?php
		/* After widget (defined by themes). */
		echo $after_widget;
		echo "\n<!-- END RECENT POSTS WIDGET -->\n";

		// Restor original Query & Post Data
		wp_reset_query();
		wp_reset_postdata();		
		}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['num_posts'] = $new_instance['num_posts'];
		$instance['categories'] = $new_instance['categories'];
		$instance['show_image'] = $new_instance['show_image'];
		$instance['show_related'] = $new_instance['show_related'];
		$instance['show_comments'] = $new_instance['show_comments'];
		$instance['show_date'] = $new_instance['show_date'];
	
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form($instance)
	{
		/* Set up some default widget settings. */
		$defaults = array(
			'title'			=> __( 'Recent Posts' , 'color-theme-framework' ),
			'num_posts'		=> 4,
			'categories'	=> 'all',
			'show_related'	=> 'off',
			'show_image'	=> 'on', 
			'show_comments'	=> 'on',
			'show_date'		=> 'on'
		);

		$instance = wp_parse_args((array) $instance, $defaults); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' , 'color-theme-framework' ) ?></label>
			<input class="widefat" style="width: 216px;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		
	
		<p>
			<label for="<?php echo $this->get_field_id('num_posts'); ?>"><?php _e( 'Number of posts:' , 'color-theme-framework' ); ?></label>
			<input type="number" min="1" max="100" class="widefat" id="<?php echo $this->get_field_id('num_posts'); ?>" name="<?php echo $this->get_field_name('num_posts'); ?>" value="<?php echo $instance['num_posts']; ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_related'], 'on'); ?> id="<?php echo $this->get_field_id('show_related'); ?>" name="<?php echo $this->get_field_name('show_related'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_related'); ?>"><?php _e( 'Show related category posts' , 'color-theme-framework' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_image'], 'on'); ?> id="<?php echo $this->get_field_id('show_image'); ?>" name="<?php echo $this->get_field_name('show_image'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_image'); ?>"><?php _e( 'Show thumbnail image' , 'color-theme-framework' ); ?></label>
		</p>

		<p style="margin-top: 20px;">
			<label style="font-weight: bold;"><?php _e( 'Post meta info' , 'color-theme-framework' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_date'], 'on'); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e( 'Show date' , 'color-theme-framework' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_comments'], 'on'); ?> id="<?php echo $this->get_field_id('show_comments'); ?>" name="<?php echo $this->get_field_name('show_comments'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_comments'); ?>"><?php _e( 'Show comments' , 'color-theme-framework' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('categories'); ?>"><?php _e( 'Filter by Category:' , 'color-theme-framework' ); ?></label> 
			<select id="<?php echo $this->get_field_id('categories'); ?>" name="<?php echo $this->get_field_name('categories'); ?>" class="widefat categories" style="width:100%;">
				<option value='all' <?php if ( 'all' == $instance['categories'] ) echo 'selected="selected"'; ?>>all categories</option>
				<?php $categories = get_categories( 'hide_empty=0&depth=1&type=post' ); ?>
				<?php foreach( $categories as $category ) { ?>
				<option value='<?php echo $category->term_id; ?>' <?php if ($category->term_id == $instance['categories']) echo 'selected="selected"'; ?>><?php echo $category->cat_name; ?></option>
				<?php } ?>
			</select>
		</p>
	<?php 
	}
}

?>