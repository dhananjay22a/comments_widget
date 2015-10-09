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

	namespace dj\comments_widget;
	
	require_once( dirname( __FILE__ ) . '/comments_class.php' );
	require_once( dirname( __FILE__ ) . '/functions.php' );
	
	//Adding action hook to register comments widtget
	add_action( 'widgets_init', '\dj\comments_widget\register_widgets' ); 
	
	//Adding action hook to include css file
	add_action( 'wp_enqueue_scripts', '\dj\comments_widget\comment_widget_css' );
	
?> 