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

 if( $xml = simplexml_load_file("http://api.indeed.com/ads/apisearch?publisher=4425239534329302&q=%22%22&sort=&radius=&st=&jt=&start=&limit=25&fromage=&filter=&latlong=1&co=pt&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2"))
  {
    foreach($xml->results->result as $detail)
    {
      $category = get_category_by_slug( 'Local Jobs' );
      $job_id = $detail->jobkey;
      $job_name = $detail->jobtitle;
      $job_link = $detail->url;
      $my_post = array(
        'post_title'    => $detail->jobtitle,
        'post_content'  => $detail->snippet."<a href =$job_link > Apply Now! </a>",
        'post_category' => array( $category->term_id ),
        'post_status'   => 'draft',
        'post_author'   => 1,
      );

        if ( post_exists($job_name)) {

        }
        else {
        wp_insert_post( $my_post );

        }
      }
     }
   }
?>
