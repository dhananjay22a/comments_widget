<?php
/**
* This file contains functions to be used in Comments Widget plugin. This file is being imported in comments_widget.php file.
*
* @since 2015-10-09
* 
* @version 2015-10-09 Dhananjay Singh - PMCVIP-243
*
*/

	namespace dj\comments_widget;
	
	/**
	* Function to register comments widget.
	*
	* @since 2015-10-09
	*/
	function register_widgets() {
		//WP inbuilt function to register widget
		register_widget( '\dj\comments_widget\comments_widget' ); 
	}
	
	
	/**
	* Function to include css file
	*
	* @since 2015-10-09
	*/
	function comment_widget_css() {
		wp_register_style( 'commentWidgetStyle', plugins_url( 'comments_widget/css/comment_widget.css' ) );
		wp_enqueue_style( 'commentWidgetStyle' );
	}
?>