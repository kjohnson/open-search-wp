<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
Plugin Name: Open Search WP
Version: 0.1.0
Author: Kyle B. Johnson
Author URI: http://kylebjohnson.me/
*/

/**
 * Class OpenSearchWP
 */
final class OpenSearchWP
{
    private $_search = '';

    private $_results = array();

    private $_post_types = array( 'post' );

    public function __construct()
    {
        global $wpdb;

        if( isset( $_POST[ 'oswp' ] ) ){
            $this->_search = $wpdb->esc_like( $_POST[ 'oswp' ] );
        }

        add_action( 'wp_ajax_oswp_search',          array( $this, 'search_results' )  );
        add_action( 'wp_ajax_nopriv_oswp_search',   array( $this, 'search_results' )  );

        add_action( 'wp_enqueue_scripts', array( $this, 'register_script' ), -1 );
    }

    public function search_results()
    {
        $results = array();

        foreach( $results as $result ){
            $this->_results[] = array(
                'type' => '', // Post Type
                'text' => '', // Display Text
                'href' => '' // Hyperlink
            );
        }

        $this->_respond();
    }

    public function register_script()
    {
        wp_register_script( 'oswp', plugins_url( '/open-search-wp.js', __FILE__ ), array( 'jquery', 'underscore' ) );

        wp_enqueue_script( 'oswp' );

        wp_localize_script( 'oswp', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
    }

    private function _respond()
    {
        $response = array( 'results' => $this->_results );

        echo wp_json_encode( $response );

        wp_die(); // this is required to terminate immediately and return a proper response
    }

} // END CLASS OpenSearchWP

new OpenSearchWP();
