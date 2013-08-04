<?php
/*
Plugin Name: Admin Chartbeat Widget
Plugin URI: https://github.com/harvitronix/wordpress-admin-chartbeat-widget
Description: Add your current chartbeat analytics into your admin dashboard and post pages.
Version: 0.2
Author: Matt Harvey
Author URI: http://twitter.com/harvitronix
*/

if(is_admin()){ 

	// **************** //
	// DISPLAY SECTION	//
	// **************** //

	function admin_chartbeat_widget() {

		// get the settings from the options table
		$siteurl = get_option( 'chartbeat_widget_siteurl' );  
		$apikey = get_option( 'chartbeat_widget_apikey' );  
		$to_strip = get_option( 'chartbeat_widget_to_strip' );  

		// check that they've set it up
		if ( $apikey != "" && $siteurl != "" ) {
		
			// call the CB API
			$url = "http://api.chartbeat.com/live/toppages/?limit=10&host=" . esc_attr( $siteurl ) . "&apikey=" . esc_attr( $apikey );
			$data = wp_remote_get( $url );				
			$output = "";

			if ( $data['response']['code'] == 200 ) {
				$top_pages = json_decode( $data['body'] );
			//var_dump($decoded);
				foreach ( $top_pages as $top_page ) {

					$path = $top_page->path;			
					if ( $to_strip != "" ) { 
						$page_title = str_replace( $to_strip, "", $top_page->i ); 
					} else {
						$page_title = $top_page->i;
					}
					$num_visitors = $top_page->visitors;
					
					$output .= '<tr><td class="num_visitors">' . number_format( $num_visitors ) . '</td><td><a href="' . esc_url( $path ) . '" target="_blank">' . esc_html( $page_title ) . '</a></td></tr>';

				}
			}

			if ( $output == "" ) { // oops, no response
				$output = "Looks like something went wrong. Either there's no one on your site, or you haven't entered your settings correctly. Head back to the settings page to double check your API Key and Site Url match your Chartbeat settings.";
			}
		}
		else { // they haven't set up yet
			$output = "Please setup your Admin Chartbeat Widget in the settings section.";
		}

		?>
		<div id="admin_chartbeat_widget">
			<table>
				<tbody>
					<?=$output?>
				</tbody>
			</table>
		</div>	
		<?
	} 

	// add the styles
	function add_admin_chartbeat_stylesheet( $hook ) {
	    if( 'index.php' != $hook )
	        return;
	    wp_enqueue_style( 'admin-chartbeat-stylesheet', plugins_url( '/style.css', __FILE__ ) );
	}
	add_action( 'admin_enqueue_scripts', 'add_admin_chartbeat_stylesheet' );

	// add the widget
	function add_admin_chartbeat_widget() {
		wp_add_dashboard_widget( 'admin-chartbeat', 'Your Site\'s Top Content Now', 'admin_chartbeat_widget' );	
	} 
	add_action( 'wp_dashboard_setup', 'add_admin_chartbeat_widget' );

	// **************** //
	// OPTIONS SECTION	//
	// **************** //

	// add the option in the menu
	function admin_chartbeat_admin_actions() {  
	    add_options_page('Chartbeat Widget Options', 'Admin Chartbeat Widget', 'manage_options', 'admin-chartbeat-widget.php', 'admin_chartbeat_admin');
	}  
	add_action('admin_menu', 'admin_chartbeat_admin_actions');  

	function admin_chartbeat_admin() {
	 
	 	// check if they submitted the form
	    if($_POST['chartbeat_widget_hidden'] == 'Y') {  

	        $siteurl = $_POST['chartbeat_widget_siteurl'];  
	        update_option('chartbeat_widget_siteurl', $siteurl);  
	        $apikey = $_POST['chartbeat_widget_apikey'];  
	        update_option('chartbeat_widget_apikey', $apikey);  
	        $to_strip = $_POST['chartbeat_widget_to_strip'];  
	        update_option('chartbeat_widget_to_strip', $to_strip);  

	        ?>  
	        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
	        <?php  

	    } else {  

	        // get the values to populate the form
	        $siteurl = get_option('chartbeat_widget_siteurl');  
	        $apikey = get_option('chartbeat_widget_apikey');  
	        $to_strip = get_option('chartbeat_widget_to_strip');  

	    }  

		?>
		
		<div class="wrap">  
		    <?php    echo "<h2>" . __( 'Admin Chartbeat Widget Options', 'chartbeat_widget' ) . "</h2>"; ?>  
		    <form name="chartbeat_widget_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
		        <input type="hidden" name="chartbeat_widget_hidden" value="Y">  
		        <?php    echo "<h4>" . __( 'Get these values from your <a href="http://chartbeat.com" target="_blank">Chartbeat.com</a> Settings page', 'chartbeat_widget' ) . "</h4>"; ?>  
		        <p><?php _e("Site URL: " ); ?><input type="text" name="chartbeat_widget_siteurl" value="<?php echo $siteurl; ?>" size="20"><br /><?php _e("<em>Be sure it matches your Chartbeat settings.</em>" ); ?></p>  
		        <p><?php _e("APIKEY: " ); ?><input type="text" name="chartbeat_widget_apikey" value="<?php echo $apikey; ?>" size="20"><br /><?php _e("<em>It's beneficial to create a new API key with only toppages permission, as this is all you need for this plugin to work.</em>" ); ?></p>  
		        <p><?php _e("Text to strip from titles: " ); ?><input type="text" name="chartbeat_widget_to_strip" value="<?php echo $to_strip; ?>" size="20"><br /><?php _e("<em>If your page titles include something like <strong>| Yoursite.com</strong>, include <strong>| Yoursite.com</strong> in this field and it will be stripped from displaying.</em>" ); ?></p>  
		        <p class="submit">  
		        <input type="submit" name="Submit" value="<?php _e('Update Options', 'chartbeat_widget' ) ?>" />  
		        </p>  
		    </form>  
		</div>  
		
		<?

	}

}

?>