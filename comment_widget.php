<?php
/* 
Plugin Name: Comment Widget
Plugin URI: 
Description: Plugin to create an widget which shows up to 1 post with the highest comment count by the selected author AND up to 1 most recent comment by the same author AND the author's gravatar
Version: 1.0
Author: Dhananjay Singh
Author URI: 
Text Domain: pmc-plugin
License: GPLv2 
*/


/*  Copyright 2015  Dhananjay Singh  (email : dsingh@pmc.com) 
     This program is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published by
     the Free Software Foundation; either version 2 of the License, or 
     (at your option) any later version. 
     This program is distributed in the hope that it will be useful, 
     but WITHOUT ANY WARRANTY; without even the implied warranty of 
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
     GNU General Public License for more details.
     You should have received a copy of the GNU General Public License
     along with this program; if not, write to the Free Software
     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	
	/*
		Creating an widget comments
		Created by : DJ
		Date : 26 Sept 2015
	*/
	namespace dj\comments_widget;
	//Adding action hook to register comments widtget
	add_action( 'widgets_init', '\dj\comments_widget\register_widgets' ); 
	
	//Function to register comments widget
	function register_widgets() {
		//WP inbuilt function to register widget
		register_widget( '\dj\comments_widget\comments_widget' ); 
	}
	
	
	
	//Adding action hook to add css in wp header for this widget.
	add_action( 'wp_head', '\dj\comments_widget\widget_css' );
	function widget_css() { 
		?> 
     		<style type="text/css"> 
				#pmc_comments_main_div { border:1px solid #2e2e2e; padding:0; overflow:auto; }
				#pmc_comments_main_div .widget_title_div { font-size:20px; background: #cccccc; color:#0088CC; text-align:center; }
				#pmc_comments_main_div .widget_title_div { font-size:20px; background: #cccccc; color:#0088CC; text-align:center; }
				#pmc_comments_main_div .news_item_main_div {  font-size:12px; padding:5px; overflow:auto; }
				#pmc_comments_main_div .news_item_thumbnail{ width:52px; height:52px; padding:1px; float:left; }
				#pmc_comments_main_div .news_item_title { width:183px; height:37px; padding:0 5px; float:left; }
				#pmc_comments_main_div .author_name { float:left; height:15px; font-size:10px; color:#7e7e7e; padding: 0 5px; }
				#pmc_comments_main_div .news_item_comment { width:230px; height:auto; padding:0 5px; float:left; font-style:italic; color:#8c8c8c; }
     		</style>
 		<?php 
	}
	
	
	
	/*
		Class for widget
	*/
	class comments_widget extends \WP_Widget {
		
		function __construct() {
     		
			$widget_ops = array(
					         'classname'   => 'comments_class',
					         'description' => 'Widget to display comments .' 
						);
		
			parent::__construct( 'comments_widget', 'Comments Widget', $widget_ops ); 
		
		}
		
		
		//build our widget settings form
     	function form( $instance ) {
			
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
							<option value="<?=$author->ID?> " <?=$selected ?>> <?=$author_info->first_name." ".$author_info->last_name?> </option>
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
		
			extract( $args );
			
         	echo $before_widget; 
			
			$currentID = get_the_ID(); //Getting current post id

			$args	=	array(
							'post_type' => 'any',
							'orderby' => 'comment_count',
							'order' => 'DESC',
							'author' => $instance['author'],
							'posts_per_page' => 1,
							'post__not_in'	=> array($currentID)
						);
						
			//Checking cache if data is available. And if so assign it to $news and avoid if part.
			$comments = get_transient('comments_keys');
			if ($comments === false) {
				$comments = new \WP_Query( $args );
				//Adding data to cache
				set_transient('comments_key', $comments, 3600 * 24);
			}
			
			//query_posts($args); //Query to get most commented post by given author
			
			//Main widget ourter div
			echo '<div id="pmc_comments_main_div">';
				//Widget title div
				echo '<div class="widget_title_div">';
					echo "Most Commented Post";
				echo '</div>';
				//Widget title div ends here
			
				if ($comments->have_posts()) :
					$counter	=	0;
					//loop through the posts and list each until done. 
					while ($comments->have_posts()) : 
						//Iterate the post index in The Loop. 
						$comments->the_post();
						
						if($counter%2 == 0 ) {
							$textColor	=	'#2e2e2e';
							$bgColor	=	'#cceeff';
						} else {
							$textColor	=	'#2e2e2e';
							$bgColor	=	'#FFFFFF';
						} 
					
						//News Item main div
						echo '<div style="color:'.$textColor.'; background:'.$bgColor.';" class="news_item_main_div">';
							//New Item thumbnail div
							echo '<div style="color:'.$textColor.'; background:'.$bgColor.';" class="news_item_thumbnail" >';
								?><a href="<?php the_permalink()?>" title="<?php the_title()?> "> <?php
								if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
									the_post_thumbnail( array(50, 50) );
								} else {
									
								}
								echo '</a>';
							echo '</div>';
							//News Item thumbnail div ends here
							
							//News Item title div
							echo '<div style="color:'.$textColor.'; background:'.$bgColor.';" class="news_item_title" >';
							?>
								<a title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><?php echo $this->short_title(50)/* . ' (' . get_comments_number() . ')'*/; ?></a><br>
							<?
							echo '</div>';
							//News item title div ends here
							
							//Authorname div
							echo '<div class="author_name">';
								echo "Author : ";
								the_author();
							echo '</div>';
							//Authername div ends here
							
							$args = array(
								'post_id' => get_the_ID(),
								'orderby' => 'comment_ID',
								'number' => 1,
								'order'	=>	'DESC'
							);
							$comment = get_comments( $args );
							
							echo '<div style="background:'.$bgColor.';" class="news_item_comment" >';
							?><a title="<?php the_title(); ?>" href="<?php echo get_comment_link( $comment[0]->comment_ID ); ?>"><?php
								echo '"'.$this->short_texts($comment[0]->comment_content, 200).'" - By: '.$comment[0]->comment_author;
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
			
			echo $after_widget; 
		}
		
		
		//Function to get authors
		function getAllAuthors() {
		
			$args = array( 'role' => 'Author' );
			
			// Create the WP_User_Query object
			$wp_user_query = new \WP_User_Query($args);
			// Get the results
			$authors = $wp_user_query->get_results();
			
			return $authors;
		}
		
		
		//Function to short title if larger than $length
		function short_title($length) {
			
			$newsTitle	=	get_the_title();
			if(strlen($newsTitle) > $length )
				return $shortNewsTitle	=	substr($newsTitle, 0, $length ). '...';
			else return $newsTitle;
		}
		
		
		//Function to short string lenght
		function short_texts( $comment, $length ) {
			if(strlen($comment) > $length )
				return $shortComment	=	substr($comment, 0, $length ). '...';
			else return $comment;
		}
	}
?> 