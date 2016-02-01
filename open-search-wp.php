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
        global $wpdb;

        $query = array();

        if( is_multisite() ) {
            foreach (wp_get_sites() as $site) {

                switch_to_blog($site['blog_id']);

                $query[] = "SELECT DISTINCT * FROM {$wpdb->posts} WHERE `post_title` LIKE '%{$this->_search}%' AND `post_status` = 'publish'";

                restore_current_blog();
            }
        } else {
            $query[] = "SELECT DISTINCT * FROM {$wpdb->posts} WHERE `post_title` LIKE '%{$this->_search}%' AND `post_status` = 'publish'";
        }

        $results = $wpdb->get_results( implode( ' UNION ', $query ) );

        usort( $results, array( $this, 'compare_post_types' ) );

        $count = array();

        foreach( $results as $result ){

            if( ! in_array( $result->post_type, $this->_post_types ) ) continue;

            if( isset( $count[ $result->post_type ] ) && 10 <= $count[ $result->post_type ] ) continue;

            if( is_multisite() ) {
                $site = ucwords(end(array_reverse(explode('.', str_replace(array('http://', 'https://'), '', str_replace('-', ' ', $result->guid))))));
            } else {
                $site = site_url();
            }

            $post_type = ucwords( str_replace( array( 'sp_', 'okpreps_' ), '', $result->post_type ) ) . 's';

            $href = $result->guid;

            $this->_results[] = array(
                'site' => $site,
                'type' => $post_type,
                'text' => $result->post_title,
                'href' => $href
            );

            if( ! isset( $count[ $result->post_type ] ) ) $count[ $result->post_type ] = 0;

            $count[ $result->post_type ] += 1;
        }

        $this->_respond();
    }

    public function register_script()
    {
        wp_register_script( 'oswp', plugins_url( '/open-search-wp.js', __FILE__ ), array( 'jquery', 'underscore' ) );

        wp_enqueue_script( 'oswp' );

        wp_localize_script( 'oswp', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
    }

    private function compare_post_types( $a, $b )
    {
        return strcmp( $a->post_type, $b->post_type );
    }

    private function _respond()
    {
        $response = array( 'results' => $this->_results );

        echo wp_json_encode( $response );

        wp_die(); // this is required to terminate immediately and return a proper response
    }

} // END CLASS OpenSearchWP

new OpenSearchWP();
