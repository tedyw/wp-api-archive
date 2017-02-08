<?php

/*
Plugin Name: WP API Archive
Plugin URI: http://github.com/tedyw/wp-api-archive
Description: Adding endpoints to WP API to fetch available archives.
Version: 1.0
Author: Tedy Warsitha
Author URI: http://github.com/tedyw/
License: GPL2
*/

class WP_API_Archive {

	protected $keys = array(
		'years',
		'months'
	);
	private $handle = 'archives';

	function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_endpoints' ) );
	}

	/**
	 * Adds endpoints to wp route
	 */
	function add_endpoints() {

		register_rest_route( 'wp/v2', '/archives', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_callback' ),
		) );

		register_rest_route( 'wp/v2', '/archives/(?P<type>[\\w-]+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_callback' ),
			'args'     => array(
				'type' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_string( $param );
					},
				)
			)
		) );
	}

	/**
	 * Retrieves and returns archive.
	 */
	function get_callback( $data ) {
		//wp_get_archives( array( 'type' => 'yearly', 'echo'=>false ) );
		$post_type = isset( $data ) ? ! empty( $data->type ) ? $data->type : 'post' : 'post';

		$archives =  wp_get_archives(
			array(
				'type'      => 'monthly',
				'post_type' => $post_type,
				'echo'      => false,
				'limit'     => 12,
				'format'    => 'option'
			) );

		preg_match_all('(\d{4}\/\d{2})', $archives, $archives);

		$archives = $archives[0];

		$archive_array = array();
		
		foreach ($archives as $archive){

			$split = explode( '/', $archive);
			$year = $split[0];
			$month = $split[1];

			if(!isset($archive_array[$year])){
				$archive_array[$year] = array();
				$archive_array[$year]['year'] = $year;
			}
			if(empty($archive_array[$year]['months'])){	$archive_array[$year]['months'] = array();}

			array_push( $archive_array[$year]['months'],  $month);

		}

		$a = array();

		foreach ($archive_array as $key => $val){
			array_push( $a, $val  );
		}

		return $a;

	}

	//TODO: Add update endpoints

}

new WP_API_Archive();