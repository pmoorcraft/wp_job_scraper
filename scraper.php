<?php
/**
 * @package wp_job_scraper
 * @version 1.0
 */
/*
Plugin Name: WP Job Scraper
Plugin URI: https://wordpress.org/plugins/wp_job_scraper/
Description: WP Job scraper automatically loads up your draft posts with job postings from indeed.com
Author: Pedro Moorcraft
Version: 1.0
Author URI: http://instacraft.io
*/

/*
* -------------------------------------- OPTIONS PAGE STARTS HERE -----------------------------------------
*/

/*
* Create Options page, page sub-sections. Taken from https://www.smashingmagazine.com/2016/04/three-approaches-to-adding-configurable-fields-to-your-plugin/#approach_1
*/

add_action( 'admin_menu', 'create_plugin_settings_page');
add_action( 'admin_init', 'setup_sections' );
add_action( 'admin_init', 'setup_fields' );


function create_plugin_settings_page() {
	// Add the menu item and page
	$page_title = 'WP job scraper settings';
	$menu_title = 'WP job scraper settings';
	$capability = 'manage_options';
	$slug = 'scraper_fields';
	$callback = 'plugin_settings_page_content';

	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback);
}

/*
* plugin_settings_page_content dictates what appears in the options page
*/

function plugin_settings_page_content() { ?>
  <div class="wrap">
		<h2>WP job scraper settings</h2>
		<form method="post" action="options.php">
            <?php
                settings_fields( 'scraper_fields' );
                do_settings_sections( 'scraper_fields' );
                submit_button();
            ?>
		</form>
	</div> <?php
}

/*
* Creates and populates sub-sections in page
*/

function setup_sections() {
	add_settings_section( 'location_section', 'Options:', 'section_callback', 'scraper_fields' );
  add_settings_section( 'frequency_section', 'Scraping frequency', 'section_callback', 'scraper_fields' );
}

function section_callback( $arguments ) {
  switch( $arguments['id'] ){
		case 'location_section':
			echo 'Location options';
			break;
		case 'frequency_section':
			echo 'Frequency of scraping';
			break;
    }
}

/*
* Creates and populates fields in sub-sections
*/
function setup_fields() {

  $fields = array(
		array(
			'uid' => 'local_location',
			'label' => 'Location of job scraper 1',
			'section' => 'location_section',
			'type' => 'select',
			'helper' => 'Choose a country from the list',
			'supplemental' => 'use reference table',
			'default' => 'it',
      'placeholder' => 'use reference table',
      'options' => array(
      			'it' => 'Italy',
      			'pt' => 'Portugal',
      			'uk' => 'UK',
            'us' => 'US'
          )
        ),

        array(
    			'uid' => 'global_location',
    			'label' => 'Location of job scraper 2',
    			'section' => 'location_section',
    			'type' => 'select',
    			'helper' => 'Choose a country from the list',
    			'supplemental' => 'use reference table',
    			'default' => 'it',
          'placeholder' => 'use reference table',
          'options' => array(
          			'it' => 'Italy',
          			'pt' => 'Portugal',
          			'uk' => 'UK',
                'us' => 'US'
              )
        )

	);

  foreach( $fields as $field ){
    add_settings_field( $field['uid'], $field['label'], 'field_callback', 'scraper_fields', $field['section'], $field  );
    register_setting( 'scraper_fields', $field['uid'] );
  }
}

function field_callback( $arguments ) {
  $value = get_option( $arguments['uid'] ); // Get the current value, if there is one
    if( ! $value ) { // If no value exists
        $value = $arguments['default']; // Set to our default
    }

	// Check which type of field we want
    switch( $arguments['type'] ){
      case 'text': // If it is a text field
        printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
        break;
      case 'textarea': // If it is a textarea
        printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
        break;
      case 'select': // If it is a select dropdown
        if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
          $options_markup = '';
          foreach( $arguments['options'] as $key => $label ){
            $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
          }
          printf( '<select name="%1$s" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup );
        }
        break;
    }

	// If there is help text
    if( $helper = $arguments['helper'] ){
        printf( '<span class="helper"> %s</span>', $helper ); // Show it
    }

	// If there is supplemental text
    if( $supplimental = $arguments['supplemental'] ){
        printf( '<p class="description">%s</p>', $supplimental ); // Show it
    }
  }


/*
* ------------------------------------- END OF OPTIONS PAGE -------------------------------
*/


/*
* ------------------------------------- SCRAPER LOGIC STARTS HERE -------------------------------
*/


/*
* On plugin activation, the function below will schedule activate of the create posts function
*/

if( !wp_next_scheduled( 'post_refresh' ) ) {
   wp_schedule_event( time(), 'daily', 'post_refresh' );
}

add_action( 'post_refresh', 'create_local_posts_function' );

// register_activation_hook( __FILE__, 'create_local_posts_function' );
register_activation_hook( __FILE__, 'create_global_posts_function' );
/*
* add_action will be used to schedule the below functions: http://wordpress.stackexchange.com/questions/174978/execute-a-function-every-hour-in-the-background
*/


function create_local_posts_function (){

  $local_location = get_option('local_location');

 if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=%22%22&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=" . $local_location . "&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
  {
    foreach($xml->results->result as $detail)
    {
      $category = get_category_by_slug( 'Local Jobs' );
      $job_id = $detail->jobkey;
      $job_name = $detail->jobtitle;
      $job_link = $detail->url;
      $local_post = array(
        'post_title'    => $detail->jobtitle,
        'post_content'  => $detail->snippet."<a href =$job_link > Apply Now! </a>",
        'post_category' => array( $category->term_id ),
        'post_status'   => 'draft',
        'post_author'   => 1,
      );

        if ( post_exists($job_name)) {

        }
        else {
        wp_insert_post( $local_post );

        }
      }
     }
   }



function create_global_posts_function (){
  $global_location = get_option('global_location');

  if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=%22%22&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=" . $global_location . "&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
   {
     foreach($xml->results->result as $detail)
     {
       $category = get_category_by_slug( 'Global Jobs' );
       $job_id = $detail->jobkey;
       $job_name = $detail->jobtitle;
       $job_link = $detail->url;
       $global_post = array(
         'post_title'    => $detail->jobtitle,
         'post_content'  => $detail->snippet."<a href =$job_link > Apply Now! </a>",
         'post_category' => array( $category->term_id ),
         'post_status'   => 'draft',
         'post_author'   => 1,
       );

         if ( post_exists($job_name)) {

         }
         else {
         wp_insert_post( $global_post );

         }
       }
      }
    }
/*
* ------------------------------------- END OF SCRAPER LOGIC -------------------------------
*/


?>
