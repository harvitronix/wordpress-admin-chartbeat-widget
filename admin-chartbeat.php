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
		$siteurl = get_option( 'admin_chartbeat_siteurl' );  
		$apikey = get_option( 'admin_chartbeat_apikey' );  
		$to_strip = get_option( 'admin_chartbeat_striptext' );  

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
	    add_action( 'admin_init', 'admin_chartbeat_register_settings' );
	}  
	add_action('admin_menu', 'admin_chartbeat_admin_actions');  

	function admin_chartbeat_register_settings() {
		register_setting( 'admin-chartbeat-settings', 'admin_chartbeat_siteurl' );
		register_setting( 'admin-chartbeat-settings', 'admin_chartbeat_apikey' );
		register_setting( 'admin-chartbeat-settings', 'admin_chartbeat_striptext' );
	}

	function admin_chartbeat_admin() {
	 
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>

		<div class="wrap">

			<h2>Admin Chartbeat Widget Options</h2>

			<form action="options.php" method="post" name="options">
				<?php settings_fields( 'admin-chartbeat-settings' ) ?>
				<?php do_settings_sections( 'admin-chartbeat-settings' ); ?>

				<p>Enter these values exactly how they appear on your <a href="http://chartbeat.com" target="_blank">Chartbeat.com</a> Settings page</p>
		
				<p><label for="admin_chartbeat_siteurl">Site URL</label></p>
				<p><input type="text" name="admin_chartbeat_siteurl" value="<?php echo esc_attr( get_option( 'admin_chartbeat_siteurl' ) ); ?>" /></p>
				
				<p><label for="admin_chartbeat_apikey">API Key</label></p>
				<p><input type="text" name="admin_chartbeat_apikey" value="<?php echo esc_attr( get_option( 'admin_chartbeat_apikey' ) ); ?>" /></p>
				
				<p><label for="admin_chartbeat_striptext">Text to Strip</label><br /><em>If your page titles include something like <strong>| Yoursite.com</strong>, include <strong>| Yoursite.com</strong> in this field and it will be stripped from displaying.</em></p>
				<p><input type="text" name="admin_chartbeat_striptext" value="<?php echo esc_attr( get_option( 'admin_chartbeat_striptext' ) ); ?>" /></p>

		 		<?php submit_button(); ?>
	 		</form>

	 	</div>

	 	<?php 
	}
}
