<?php
 /*
 * Plugin Name: Blog Coach
 * Description: A plugin to help you maintain a healthy blog workflow with a simple visual reminder in the admin toolbar. 
 * Version: 1.0.0
 * Author: swinterroth
 * Author URI: http://swinterroth.com
 */
add_action( 'admin_menu', 'bgc_admin_menu' );
function bgc_admin_menu() {
	add_menu_page( 'Blog Coach', 'Blog Coach', 'manage_options', 'blog-coach', 'bgc_display',plugin_dir_url( __FILE__ )."/icon.png" );
}

function bgc_display() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$current_user = wp_get_current_user();
	?>
	<style type="text/css">
		#bgc_container{
			margin-left: 20px;
			margin-top:30px;
			margin-right: 30px;
		}
		#bgc_header{
			background:#dbdbdb;
			padding:10px;
		}
		#bgc_header h1{
			font-size: 40px;;

		}
		.bgc_header_area{
			background: #a6cadb;
			padding:10px;
			margin-top:30px;
			margin-bottom: 20px;;
		}
		.bgc_form_checkbox{
			width: 100%;
			display: block;
			margin:5px;
		}
	</style>
	<?php
		$options = bgc_settings();
		if(isset($_POST['bgc'])){
			 
			if(isset($_POST['bgc']['visual_reminder'])){
				$options["visual_reminder"] = true;
			}else{
				$options["visual_reminder"] = false;
			}
			if(isset($_POST['bgc']['visual_feedback'])){
				$options["visual_feedback"] = true;
			}else{
				$options["visual_feedback"] = false;
			} 


			update_option("bgc_options" , $options);
		}
	?>
	<div id="bgc_container">
	<h3>Hi <?php echo $current_user->first_name;  ?> ! Glad you're working on your blog !</h3>

	<div id="bgc_header">
		<h1>Blog Coaching</h1>

	</div>
		<div class="bgc_header_area">
			<h1>Instructions</h1>

		</div>
		<p>
			Congratulations! I'm glad you made it here on the first step towards a more productive blog.  This plugin was designed to help you establish deadlines and keep your WordPress site fed with great new content. Most importantly, we hope you have a lot of fun.  Read the getting started steps below to maximize your efforts and to set things up:	</p>
			<h3>Step 1:  Define Your Content Creation Goals</h3>
		<p>
			Most successful bloggers would recommend posting at least one high-quality blog post every week.   Decide on how frequently can you realistically publish a new post and select the appropriate notifications level below.</p>

		<p>In the beginning, be realistic with yourself and take a moment to identify a normal work week. Routine changes require baby steps, make your publishing goals achievable based on your schedule.  Once momentum builds, then it will be time to turn up the action.</p>
			<h3>Step 2: Make Your Goals Known</h3>
		<p>Studies have shown that when you make your goals public, you're more likely to keep it up. Drop me a line if you're using this plugin, I will follow up with you to make sure you're staying on track. This is, of course, optional but highly recommended if you're serious about building a better blog.</p>

		<p>Here's how:

			Introduce yourself by tweeting me <a target="_blank" href="http://twitter.com/swinterroth">@swinterroth</a> with your goal, and site URL and the hashtag: #blogwin.</p>
			<p>Or use this link to <a href="http://ctt.ec/o5cC9" target="_blank">Tweet: .@swinterroth I'm trying to blog more on my blog! #blogwin</a>.</p>
		<div class="bgc_header_area">
			<h1>Reminders</h1></div>
		<form method="POST"> 
			<label for="bgc_email_reminder"  class="bgc_form_checkbox">
				<input type="checkbox" name="bgc[visual_reminder]" id="bgc_visual_reminder"value="1" <?php checked( $options['visual_reminder'], 1 ); ?>>
				Enable visual reminders (Admin toolbar)	</label>
			<label for="bgc_email_reminder"  class="bgc_form_checkbox" >
				<input type="checkbox" name="bgc[visual_feedback]" id="bgc_visual_feedback"value="1" <?php checked( $options['visual_feedback'], 1 ); ?>>
				Enable visual feedback ( Thumbs up after post ) </label>
		 
			<br/>
			<br/>
			<br/>
			<input type="submit" value="Save settings" class="button button-primary"/>
		</form>

	</div>
	<?php


}
add_action( 'admin_bar_menu', 'bgc_visual_bar', 999 );

function bgc_visual_bar( $wp_admin_bar ) {
	$options = bgc_settings();
	if($options["visual_reminder"]) {
		$days  = bgc_get_days_passed();
		$args = array(
			'id'    => 'bgc-days',
			'title' =>  $days . ' days',
			'meta'  => array( 'class' => 'bgc-'.(($days < 10) ? "green" : (($days < 30 && $days > 9) ? "yellow" : (($days <90 && $days > 29) ? "orange": "red") ) ) )
		);
		$wp_admin_bar->add_node( $args );
	}
}
function bgc_add_meta_box() {

	$screens = array( 'post'  );
	$options = bgc_settings();
	if($options["visual_feedback"]){
		global $post;
		if(get_post_meta($post->ID,"bgc_show_notification",true) == "yes"){
			 
			foreach ( $screens as $screen ) {

				add_meta_box(
					'bgc_notification',
					"You're awesome! This post counts towards your blogging goals and resets the timer",
					'bgc_notification_html',
					$screen,
					"bgc",
					"high"
				);
			}
			update_post_meta($post->ID,"bgc_show_notification","no");
		}
	}
}
add_action( 'add_meta_boxes', 'bgc_add_meta_box' );

function bgc_move_deck() {
	global $post, $wp_meta_boxes;

	do_meta_boxes( get_current_screen(), 'bgc', $post );

	unset($wp_meta_boxes['post']['bgc']);
}
add_action( 'publish_post', 'bgc_post_published_notification', 10, 2 );
function bgc_post_published_notification( $ID, $post ) {
	update_post_meta($ID,"bgc_show_notification","yes");
}
add_action('edit_form_top', 'bgc_move_deck');
function bgc_notification_html( $post ) {
	?>
		<script type="text/javascript">
			window.onload = function() {
				setTimeout(function(){
					document.getElementById("bgc-sortables").style.display="none";
				},10 * 1000);
			};
		</script>
		<style type="text/css">

			#bgc-sortables .postbox,
			#bgc-sortables{
				cursor: default;
				border:none;
				background:#16e067;
			}
			#bgc-sortables {
				 border-left:3px solid #000;
			 }
			#bgc_notification .inside{
				display:none !important;
			}
			#bgc_notification button{
				display: none;
			}
			#bgc_notification h2{
				margin-bottom: 0px;
				margin-top: 0px;;
				cursor: default;
				padding:10px;
			}

		</style>
	<?php

}

add_action('admin_head', 'bgc_custom_css');

function bgc_custom_css() {
	?>
	<style type="text/css">
	    #wp-admin-bar-bgc-days.bgc-green div {
	        color:green !important;
	    }
	    #wp-admin-bar-bgc-days.bgc-yellow  div{
		    color:yellow !important;
	    }

	    #wp-admin-bar-bgc-days.bgc-orange  div{
		    color:orange !important;
	    }
	    #wp-admin-bar-bgc-days.bgc-red div {
		    color:red !important;
	    }
	    #wp-admin-bar-bgc-days div{
	    #wp-admin-bar-bgc-days div{
	    #wp-admin-bar-bgc-days div{
		    font-weight: bold;
	    }
    </style>
	<?php
}
function bgc_settings(){
	$default = array( 
		"visual_reminder"=>true,
		"visual_feedback"=>true,
		"notify_recurence"=>"weekly",
		"notify_time"=>"10",
	);
	$values =  get_option("bgc_options");

	return wp_parse_args($values,$default);
}
function bgc_get_last_post_date(){
	$last_post = wp_get_recent_posts(array("number"=>1,"orderby"=>"post_date","order"=>"DESC"));

	return $last_post[0]['post_date_gmt'];

}
function bgc_get_days_passed(){
	$time = time() - strtotime(bgc_get_last_post_date());
	return floor ($time / ( 24 * 3600 ) );
}


 /* 
add_action('init', 'bgc_check_event');
 
 


function bgc_check_event() {

	$options = bgc_settings();

	if($options['notify_recurence'] === "daily"){
		if(date( 'G', current_time( 'timestamp', 1 ) == $options['notify_time'] ) ){
			bgc_run_event();
		}
	}
	if($options['notify_recurence'] === "monthly"){
		if(date( 'G', current_time( 'timestamp', 1 ) == $options['notify_time'] && date( 'j', current_time( 'timestamp', 1 ) == 1 ) ) ){
			bgc_run_event();
		}
	}
	if($options['notify_recurence'] === "weekly"){
		if(date( 'G', current_time( 'timestamp', 1 ) == $options['notify_time'] )  && date( 'w', current_time( 'timestamp', 1 ) == 0 ) ){
			bgc_run_event();
		}
	}
} */

function bgc_run_event(){
	
	$to = get_bloginfo('admin_email');
	$user = get_user_by("email",$to);
	$subject = 'Get blogging coach';

	$body ="<h4>".$user->display_name."</h4>";
	$body .= "<p> Get blogging ! It's been ".bgc_get_days_passed()." days since a post was published.</p>".
	$body .= "<p> Keep your long-term goals in mind and make time for your blog</p>".
	$body .= "<p> Scott Winterroth<br/>@swinterroth</p>";


	$headers = array('Content-Type: text/html; charset=UTF-8');

	wp_mail( $to, $subject, $body, $headers );
}



function bgc_dashboard_widget_function() {
	$rss = fetch_feed( "http://feeds.feedburner.com/contentacademy" );

	if ( is_wp_error($rss) ) {
		if ( is_admin() || current_user_can('manage_options') ) {
			echo '<p>';
			printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
			echo '</p>';
		}
		return;
	}

	if ( !$rss->get_item_quantity() ) {
		echo '<p>Apparently, there are no updates to show!</p>';
		$rss->__destruct();
		unset($rss);
		return;
	}

	echo "<ul>\n";

	if ( !isset($items) )
		$items = 5;

	foreach ( $rss->get_items(0, $items) as $item ) {
		$publisher = '';
		$site_link = '';
		$link = '';
		$content = '';
		$date = '';
		$link = esc_url( strip_tags( $item->get_link() ) );
		$title = esc_html( $item->get_title() );
		$content = $item->get_content();
		$content = wp_html_excerpt($content, 250) . ' ...';

		echo "<li><a class='rsswidget' href='$link'>$title</a> \n";
	}

	echo "</ul>\n";
	$rss->__destruct();
	unset($rss);
}

function bgc_add_dashboard_widget() {
	wp_add_dashboard_widget('bgc_dashboard_widget', 'BlogCoach Feed', 'bgc_dashboard_widget_function');
}

add_action('wp_dashboard_setup', 'bgc_add_dashboard_widget');

add_action( 'admin_enqueue_scripts', 'bgc_pointer_load', 1000 );
 
function bgc_pointer_load( $hook_suffix ) {
 
    // Don't run on WP < 3.3
    if ( get_bloginfo( 'version' ) < '3.3' )
        return; 
 
    // Get pointers for this screen
    $pointers = apply_filters( 'bgc_admin_pointers', array() );
 
    if ( ! $pointers || ! is_array( $pointers ) )
        return;
 
    // Get dismissed pointers
    $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
    $valid_pointers =array();
 
    // Check pointers and remove dismissed ones.
    foreach ( $pointers as $pointer_id => $pointer ) {
 
        // Sanity check
        if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
            continue;
 
        $pointer['pointer_id'] = $pointer_id;
 
        // Add the pointer to $valid_pointers array
        $valid_pointers['pointers'][] =  $pointer;
    }
 
    // No valid pointers? Stop here.
    if ( empty( $valid_pointers ) )
        return;
 
    // Add pointers style to queue.
    wp_enqueue_style( 'wp-pointer' );
 
    // Add pointers script to queue. Add custom script.
    wp_enqueue_script( 'bgc-pointer', plugins_url( 'script.js', __FILE__ ), array( 'wp-pointer' ) );
 
    // Add pointer options to script.
    wp_localize_script( 'bgc-pointer', 'bgcPointer', $valid_pointers );
}
add_filter( 'bgc_admin_pointers', 'bgc_register_pointer' );
function bgc_register_pointer( $p ) {
	$mailing_title = "Free Email Coaching";
	$mailing_description = "Take full advantage of the Blog Coach plugin! Signup for gentle encouragements to keep your blog writing on track. No spam.";
	$mailing_join_btn = "Join now";
	$mailing_join_hide = "Hide"; 
	
	
	$email = wp_get_current_user();
	$email = $email->user_email; 
    $p['bgc'] = array(
        'target' => '#wp-admin-bar-bgc-days',
        'options' => array(
            'content' => sprintf( '<h3> %s </h3> <p> %s </p> <p> %s </p>',
                __( $mailing_title ,'bgc'),
                __( '<p >'.$mailing_description.'</p>','bgc'),
                __( '<p ><p>' . '<input type="text" name="bgc_email" id="bgc_email" value="' . $email . '" style="width: 100%"/>' . '</p></p>','bgc')
            ),
			"mailing_join_btn"=>$mailing_join_btn,
			"mailing_join_hide"=>$mailing_join_hide,
			"mailing_confirmation"=>$mailing_confirmation,
            'position' => array( 'edge' => 'top', 'align' => 'middle' )
        )
    );
    return $p;
}

add_action( 'wp_ajax_bgc_mailing_list', 'bgc_add_mail' ); 

function bgc_add_mail() {
     $email = $_POST["email"];
	  
	 $MailChimp = new BGCMailChimp('64207c26d3e69bec770b5f922cf95dfc-us10');
	 $result = $MailChimp->call('lists/subscribe', array(
                'id'                => '6aaf9f2a0c',
                'email'             => array('email'=>$email) 
            ));
			 
	 die();
}

class BGCMailChimp
{
    private $api_key;
    private $api_endpoint = 'https://<dc>.api.mailchimp.com/2.0';
    private $verify_ssl   = false;
    /**
     * Create a new instance
     * @param string $api_key Your MailChimp API key
     */
    public function __construct($api_key)
    {
        $this->api_key = $api_key;
        list(, $datacentre) = explode('-', $this->api_key);
        $this->api_endpoint = str_replace('<dc>', $datacentre, $this->api_endpoint);
    }
	
	/**
     * Validates MailChimp API Key
     */
    public function validateApiKey()
    {
        $request = $this->call('helper/ping');
		return !empty($request);
    }
    /**
     * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
     * @param  string $method The API method to call, e.g. 'lists/list'
     * @param  array  $args   An array of arguments to pass to the method. Will be json-encoded for you.
     * @return array          Associative array of json decoded API response.
     */
    public function call($method, $args = array(), $timeout = 10)
    {
        return $this->makeRequest($method, $args, $timeout);
    }
    /**
     * Performs the underlying HTTP request. Not very exciting
     * @param  string $method The API method to be called
     * @param  array  $args   Assoc array of parameters to be passed
     * @return array          Assoc array of decoded result
     */
    private function makeRequest($method, $args = array(), $timeout = 10)
    {
        $args['apikey'] = $this->api_key;
        $url = $this->api_endpoint.'/'.$method.'.json';
        $json_data = json_encode($args);
        if (function_exists('curl_init') && function_exists('curl_setopt')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result    = file_get_contents($url, null, stream_context_create(array(
                'http' => array(
                    'protocol_version' => 1.1,
                    'user_agent'       => 'PHP-MCAPI/2.0',
                    'method'           => 'POST',
                    'header'           => "Content-type: application/json\r\n".
                                          "Connection: close\r\n" .
                                          "Content-length: " . strlen($json_data) . "\r\n",
                    'content'          => $json_data,
                ),
            )));
        }
        return $result ? json_decode($result, true) : false;
    }
}