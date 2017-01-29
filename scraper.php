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

// required libraries for media_sideload_image
require_once( ABSPATH . 'wp-admin/includes/post.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );

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
		<?php settings_errors(); ?>
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
	add_settings_section( 'query_1', 'Query 1', 'section_callback', 'scraper_fields' );
  add_settings_section( 'query_2', 'Query 2', 'section_callback', 'scraper_fields' );
	add_settings_section( 'query_section', 'Query Settings', 'section_callback', 'scraper_fields' );
}

function section_callback( $arguments ) {
  switch( $arguments['id'] ){
		case 'query_1':
			echo ''; //sub-headers go here
			break;
		case 'query_2':
			echo '';
			break;
		case 'frequency_section':
			echo '';
			break;
    }
}

/*
* Creates and populates fields in sub-sections
*/
function setup_fields() {

  $fields = array(
		array(
			'uid' => 'location_1',
			'label' => 'Location of job scraper 1',
			'section' => 'query_1',
			'type' => 'select',
			'helper' => 'Choose a country from the list',
			'supplemental' => '',
			'default' => 'pt',
      'placeholder' => '',
      'options' => array(
      			'it' => 'Italy',
      			'pt' => 'Portugal',
      			'uk' => 'UK',
            'us' => 'US'
          )
        ),

      array(
  			'uid' => 'location_2',
  			'label' => 'Location of job scraper 2',
  			'section' => 'query_2',
  			'type' => 'select',
  			'helper' => 'Choose a country from the list',
  			'supplemental' => '',
  			'default' => 'uk',
        'placeholder' => '',
        'options' => array(
        			'it' => 'Italy',
        			'pt' => 'Portugal',
        			'uk' => 'UK',
              'us' => 'US'
            )
      ),

			array(
  			'uid' => 'query_word_1',
  			'label' => 'Query 1 words',
  			'section' => 'query_1',
  			'type' => 'text',
  			'helper' => '',
  			'supplemental' => '',
  			'default' => '',
        'placeholder' => '',
        'options' => false
      ),

			array(
  			'uid' => 'query_word_2',
  			'label' => 'Query 2 words',
  			'section' => 'query_2',
  			'type' => 'text',
  			'helper' => '',
  			'supplemental' => '',
  			'default' => 'portuguese',
        'placeholder' => '',
        'options' => false
      ),

			array(  // note: this feature hasn't yet been implemented in the scraper logic
  			'uid' => 'frequency',
  			'label' => 'Frequency of queries',
  			'section' => 'query_section',
  			'type' => 'select',
  			'helper' => 'Choose a frequency from the list',
  			'supplemental' => '',
  			'default' => 'hourly',
        'placeholder' => '',
        'options' => array(
        			'hourly' => 'Hourly',
        			'daily' => 'Daily',
            )
      ),

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
* On plugin activation, the hook below will activate the cron_activation function
*/

register_activation_hook( __FILE__, 'cron_activation' );
register_activation_hook( __FILE__, 'create_first_posts_function' );
register_activation_hook( __FILE__, 'create_second_posts_function' );

function cron_activation() {
	if( !wp_next_scheduled( 'hourly_post_refresh' ) ) {
	   wp_schedule_event( time(), 'hourly', 'hourly_post_refresh' );
	}
}

/*
* Every hour, the hourly_post_refresh hook will be called, which links to the create_local_posts_function, which creates the draft posts.
*/

add_action( 'hourly_post_refresh', 'create_first_posts_function');

/*
* source of code http://wordpress.stackexchange.com/questions/174978/execute-a-function-every-hour-in-the-background
*/







function create_first_posts_function (){

  $location_1 = get_option('location_1');
	$query_word_1 = get_option('query_word_1');

 if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=" . $query_word_1 . "&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=" . $location_1 . "&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
  {
    foreach($xml->results->result as $detail)
    {
      $category = get_category_by_slug( 'Local Jobs' );
      $job_id = $detail->jobkey;
      $job_name = $detail->jobtitle;
      $job_link = $detail->url;
			$job_company = $detail->company;
			$job_logo_url = "https://logo.clearbit.com/" . $job_company . ".com";
      $first_post = array(
        'post_title'    => $detail->jobtitle,
        'post_content'  => $detail->snippet."</br>"."<a href =$job_link > Apply Now! </a>"."</br>"."<img align=middle src=$job_logo_url />",
        'post_category' => array( $category->term_id ),
        'post_status'   => 'draft',
        'post_author'   => 1,
      );

        if ( post_exists($job_name)) {

        }
        else {
        $post_id_lo = wp_insert_post( $first_post );
        }
      }
     }
   }



function create_second_posts_function (){
  $location_2 = get_option('location_2');
	$query_word_2 = get_option('query_word_2');

  if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=" . $query_word_2 . "&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=" . $location_2 . "&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
   {
     foreach($xml->results->result as $detail)
     {
       $category = get_category_by_slug( 'Global Jobs' );
       $job_id = $detail->jobkey;
       $job_name = $detail->jobtitle;
       $job_link = $detail->url;
			 $job_company = $detail->company;
			 $job_logo_url = "https://logo.clearbit.com/" . $job_company . ".com";
			 $job_logo_desc = "The logo of" . $job_company . "for the position of" . $job_name;
       $second_post = array(
         'post_title'    => $job_company . ": " . $job_name,
         'post_content'  => $detail->snippet."</br>"."<a href =$job_link > Apply Now! </a>"."</br>"."<img align=middle src=$job_logo_url />",
         'post_category' => array( $category->term_id ),
         'post_status'   => 'draft',
         'post_author'   => 1,
       );

         if ( post_exists($job_name)) {

         }
         else {
         $post_id_gl = wp_insert_post( $second_post );
         }
       }
      }
    }

register_deactivation_hook(__FILE__, 'my_deactivation');

function my_deactivation() {
	wp_clear_scheduled_hook('hourly_post_refresh');
}

/*
* ------------------------------------- END OF SCRAPER LOGIC -------------------------------
*/


?>
