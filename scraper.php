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
* On plugin activation, the function below will schedule activate of the create posts function
*/
register_activation_hook( __FILE__, 'create_posts_function' );

/*
* The following function will run every hour. It is a for each loop that creates a draft from each new item in the indeed XML feed.
*/


function create_posts_function (){

 if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=%22%22&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=id&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
  {
    foreach($xml->results->result as $detail)
    {
      $camp_id = $detail->jobkey;
      $camp_name = $detail->company;
      $my_post = array(
        'post_title'    => $detail->company,
        'post_content'  => $detail->snippet,
        'post_status'   => 'draft',
        'post_author'   => 1,
      );

      $query_post = array(
        'post_tite'    => (string)$detail->company,
        'post_status'   => 'draft',
      );


      $my_query = new WP_Query( $query_post );

      if ( $my_query->have_posts() ) {

      }
      //$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND (post_status = 'draft' OR post_status = 'publish')", $camp_name
      //);

        //$wpdb->query( $query );

        //if ( $wpdb->num_rows > 0) {
        //}
        else {
        wp_insert_post( $my_post );

        }
      }
     }
   }
?>
