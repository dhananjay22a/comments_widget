<?php
/**
* Comments widget class
*
* @since 2015-10-09
* @uses $wp_query
* 
* @version 2015-10-09 Dhananjay Singh - PMCVIP-243
*
*/

	namespace dj\comments_widget;
	
	class comments_widget extends \WP_Widget {
		
		//Initialize our Comments Widget class
		function __construct() {
     		
			$widget_ops = array(
					         'classname'   => 'comments_class',
					         'description' => 'Widget to display comments .' 
						);
		
			parent::__construct( 'comments_widget', 'Comments Widget', $widget_ops ); 
		
		}
		
		
		//build our widget settings form
     	function form( $instance ) {
		
			//Get posts which are of "guest-author" type
			$args	=	array(
							'post_type' => 'guest-author',
							'posts_per_page' => -1
						);
			$guest_posts = new \WP_Query( $args );
			
			//Checking cache if data is available. And if so assign it to $guest_names and avoid if part.
			$guest_names	=	array();
			$guest_names = get_transient('guest_author_posts_key');
			if (empty($guest_names)) {
				
				if ($guest_posts->have_posts()) :
					//loop through the posts and list each until done. 
					while ($guest_posts->have_posts()) : 
						//Iterate the post index in The Loop. 
						$guest_posts->the_post();
						$guest_names[]	=	get_the_title();
						
					endwhile;
				endif;
				//Adding data to cache
				set_transient('guest_author_posts_key', $guest_names, 60);
			}
			
			
			$instance = wp_parse_args( (array) $instance ); 
			$author_id = $instance['author'];
			
			$authors	=	$this->getAllAuthors();
			
			
			?>
				<p>Select Author : 
					<select name="<?php echo $this->get_field_name( 'author' ); ?>" id="author">
					<?
						foreach($authors as $author ) {
							$author_info = get_userdata($author->ID);
							$selected	= ($author_id == $author->ID) ? "selected" : "";
					?>
							<option value="<?php echo $author->ID ?> " <?php echo $selected ?>> <?php echo esc_html($author_info->first_name." ".$author_info->last_name)?> </option>
					<?
						}
						foreach($guest_names as $guest_name) {
							$selected	= ($author_id == $guest_name) ? "selected" : "";
							?>
								<option value="<?php echo esc_html($guest_name) ?> " <?php echo $selected ?>> <?php echo esc_html($guest_name) ?> </option>
							<?
						}
					?>
					</select>
				</p>
         	<?php 
		}
		
		
		//save our widget settings 
     	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['author']  =  sanitize_text_field( $new_instance['author'] );
         	return $instance;
		}
		
		
		//display our widget
		function widget( $args, $instance ) {
		
			$currentID	=	(is_single()) ? get_the_ID() : 0;
			
			$selected_author	=	$instance['author']; //to be used in cache name
			
			if(!is_numeric($instance['author'])) {
				//get posts by guest user name
				$args = array(
								'post_type' => 'news',
								'post_status' => 'publish',
								'author_name' => $instance['author'],
								'posts_per_page' => 1,
								'post__not_in'	=> array($currentID)
							);
			} else {
				//Get posts by author
				$args	=	array(
								'post_type' => 'any',
								'orderby' => 'comment_count',
								'order' => 'DESC',
								'author' => $instance['author'],
								'posts_per_page' => 1,
								'post__not_in'	=> array($currentID)
							);
			}
						
			//Checking cache if data is available. And if so assign it to $most_commented_post and avoid if part.
			$most_commented_post = get_transient('comments_key_'.$selected_author);
			if ($most_commented_post === false) {

				$most_commented_post = new \WP_Query( $args );
				//Adding data to cache
				set_transient('comments_key_'.$selected_author, $most_commented_post, 60);
			}
			
			//Main widget ourter div
			echo '<div id="pmc_comments_main_div">';
				//Widget title div
				echo '<div class="widget_title_div">';
					echo "Most Commented Post";
				echo '</div>';
				//Widget title div ends here
				
				if ($most_commented_post->have_posts()) :
					$counter	=	0;
					//loop through the posts and list each until done. 
					while ($most_commented_post->have_posts()) : 
						//Iterate the post index in The Loop. 
						$most_commented_post->the_post();
						
						$class	=	($counter%2 == 0 ) ? 'row1' : 'row2';
					
						//News Item main div
						echo '<div class="news_item_main_div '.$class.'">';
							//New Item thumbnail div
							echo '<div class="news_item_thumbnail '.$class.'" >';
								?><a href="<?php the_permalink()?>" title="<?php the_title()?> "> <?php
								if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
									the_post_thumbnail( array(50, 50) );
								} else {
									
								}
								echo '</a>';
							echo '</div>';
							//News Item thumbnail div ends here
							
							//News Item title div
							echo '<div class="news_item_title '.$class.'" >';
							?>
								<a title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><?php echo esc_html($this->short_title(100))/* . ' (' . get_comments_number() . ')'*/; ?></a><br>
							<?
							echo '</div>';
							//News item title div ends here
							
							$post_id	=	get_the_ID();
							
							//Authorname div
							echo '<div class="author_name">';
								echo "Author : ";
								if(!is_numeric($instance['author'])) echo esc_html($instance['author']); else the_author();
							echo '</div>';
							//Authername div ends here
							
							if(!is_numeric($instance['author'])) {  
								//Get all posts for guest author
								$args = array(
									'post_type' => 'news',
									'post_status' => 'publish',
									'author_name' => $instance['author'],
									'posts_per_page' => -1
								);
							} else {	
								//Get all posts for author
								$author_id	=	get_the_author_meta( 'ID' );//get current author id
								$args = array(
									'post_type' => 'news',
									'post_status' => 'publish',
									'author' => $author_id,
									'posts_per_page' => -1
								);
								
								$author_email_id	=	get_the_author_meta( 'email' ); //email to get author thumbnail
							}
							
							$author_id_or_guest_name	=	(!is_numeric($instance['author'])) ? $instance['author'] : $author_id; //variable to be used in cache name ahead
			
							
							//Checking cache if data is available. And if so assign it to $authors_post_ids and avoid if part.
							$authors_post_ids = get_transient('authors_posts_'.$author_id_or_guest_name);
							if ($authors_post_ids === false) {
								
								$authors_posts = new \WP_Query( $args );
								$author_ids	=	array();
								if ( $authors_posts->have_posts() ) :
									while ( $authors_posts->have_posts() ) : $authors_posts->the_post();
										
										$authors_post_ids[] = get_the_ID();
								 
									endwhile;
								endif;
								
								//Adding data to cache
								set_transient('authors_posts_'.$author_id_or_guest_name, $authors_post_ids, 60);
							}
							
							wp_reset_query();
							
							//Get author image
							if(!is_numeric($instance['author'])) {
								//Get posts which are of "guest-author" type and post title is $instance['author']
								$args	=	array(
												'name' => 'cap-'.strtolower($instance['author']),
												'post_type' => 'guest-author',
												'posts_per_page' => 1,
											);
								//Checking cache if data is available. And if so assign it to $guest_author_post_id and avoid if part.
								$guest_author_post_id = get_transient('guest_author_post_cap-'.strtolower($instance['author']));
								if ($guest_author_post_id === false) {
									
									$guest_author_post = new \WP_Query( $args );
									if ($guest_author_post->have_posts()) :
											//Iterate the post index in The Loop. 
											$guest_author_post->the_post();
											$guest_author_post_id	=	get_the_ID();
									endif;
									wp_reset_query();
									
									//Adding data to cache
									set_transient('guest_author_post_cap-'.strtolower($instance['author']), $guest_author_post_id, 60);	
								}
								
								
								//Now get email id for this $guest_author_post_id from <prefix>_postmeta table
								$guest_author_email_id = get_post_meta( $guest_author_post_id, "cap-user_email", true);
								
								
								//if it is guest author 
								echo get_avatar( $guest_author_email_id, '20', 'http://www.goldderby.com/img/avtar.gif');

							} else {
								//echo $author_id;
								echo get_avatar( $author_email_id, '20', 'http://www.goldderby.com/img/avtar.gif');
							}
							
							
							//Get most recent comment from $authors_post_ids
							$args = array(
								'post__in' => $authors_post_ids,
								'orderby' => 'comment_ID',
								'number' => 1,
								'order'	=>	'DESC'
							);
							//Checking cache if data is available. And if so assign it to $comment and avoid if part.
							$comment = get_transient('comments_'.$author_id_or_guest_name);
							if ($comment === false) {
								$comment = get_comments( $args );
								//Adding data to cache
								set_transient('comments_'.$author_id_or_guest_name, $comment, 60);
							}
							
							
							echo '<div class="news_item_comment row1" >';
							?><a title="<?php the_title(); ?>" href="<?php echo get_comment_link( $comment[0]->comment_ID ); ?>"><?php
								echo '"'.esc_html($this->short_texts($comment[0]->comment_content, 200)).'" - By: '.esc_html($comment[0]->comment_author);
							?></a><?php							
							echo '</div>';
							
							
						$counter++;
						echo '</div>';
						//News item main div ends here
					endwhile; 
				endif;
				
			//Widget main div ends here
			echo "</div>";
			//Destroy the previous query. This is a MUST.
			wp_reset_query();
			
		}
		
		
		
		/**
		* Function to get all authors
		*
		* @since 2015-10-09
		* @uses $wp_query
		* 
		* @return $authors an object of results.
		*/
		function getAllAuthors() {
		
			$args = array( 'role' => 'Author' );
			
			// Create the WP_User_Query object
			$wp_user_query = new \WP_User_Query($args);
			
			//Checking cache if data is available. And if so assign it to $news and avoid if part.
			$authors = get_transient('authors_key');
			if ($authors === false) {
				// Get the results
				$authors = $wp_user_query->get_results();
				//Adding data to cache
				set_transient('authors_key', $authors, 60);
			}
			
			return $authors;
		}
		
		
		/**
		* Function to short title if larger than $length
		*
		* @since 2015-10-09
		* @uses get_the_title() to get title of current post
		* 
		* @param $length is maximum allowed length
		* @return $newsTitle after shortening.
		*/
		function short_title($length) {
			
			$newsTitle	=	get_the_title();
			if(strlen($newsTitle) > $length )
				return $shortNewsTitle	=	substr($newsTitle, 0, $length ). '...';
			else return $newsTitle;
		}
		
		
		/**
		* Function to short length of any given string.
		*
		* @since 2015-10-09
		* 
		* @param $comment is original string which need to be shorten
		* @param $length is maximum allowed length
		* @return $comment after shortening.
		*/
		function short_texts( $comment, $length ) {
			if(strlen($comment) > $length )
				return $shortComment	=	substr($comment, 0, $length ). '...';
			else return $comment;
		}

	}
?>