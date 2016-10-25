<?php
/*
Plugin Name: Dexter REST API
Description: API Modifications for DEXTER portfolio back-end
Author: A Q Khan
Version: ALPHA
Author URI: http://aqkhan.ninja
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Convert ID array into Project details array

function id2Info($id) {
    $projects = array();
    foreach ($id as $p) {
        $projects[] = array(
            'id' => $p,
            'title' => get_the_title($p),
            'type' => get_post_meta($p, 'pType', true),
            'pLink' => get_post_meta($p, 'pLink', true),
            'pLocation' => get_post_meta($p, 'pLocation', true),
            'pThumb' => get_post_meta($p, 'pThumb', true),
            'pLogo' => get_post_meta($p, 'pLogo', true),
            'cName' => get_post_meta($p, 'cName', true),
            'pTags' => get_post_meta($p, 'pTags', true),
            'pFeedback' => get_post_meta($p, 'pFeedback', true),
            'project_featured' => get_post_meta($p, 'project_featured', true),
        );
    }
    return $projects;
}

// Adding custom API ENDPOINTS

add_action( 'rest_api_init', function () {
    register_rest_route( 'api/', 'portfolio', array(
        'methods' => 'GET',
        'callback' => 'get_all_pItems',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
    register_rest_route( 'api/', 'portfolio/s/(?P<s>\d+)/d/(?P<d>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_paginated_pItems',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
    register_rest_route( 'api/', 'portfolio/featured', array(
        'methods' => 'GET',
        'callback' => 'get_featured_pItems',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
} );

function get_all_pItems() {
    $args = array(
        'post_type' => 'projects',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids'
    );

    $posts = get_posts($args);

    if ( empty( $posts ) ) {
        return new WP_Error( 'No projects found', 'Try again', array( 'status' => 404 ) );
    }
    $projects = array();
    foreach ($posts as $p) {
        $projects[] = array(
            'id' => $p,
            'title' => get_the_title($p),
            'type' => get_post_meta($p, 'pType', true),
            'pLink' => get_post_meta($p, 'pLink', true),
            'pLocation' => get_post_meta($p, 'pLocation', true),
            'pThumb' => get_post_meta($p, 'pThumb', true),
            'pLogo' => get_post_meta($p, 'pLogo', true),
            'cName' => get_post_meta($p, 'cName', true),
            'pTags' => get_post_meta($p, 'pTags', true),
            'pFeedback' => get_post_meta($p, 'pFeedback', true),
            'project_featured' => get_post_meta($p, 'project_featured', true),
        );
    }

    return $projects;
}

// For parameters use:  'portfolio/', '/tag/(?P<id>\d+)'

function get_paginated_pItems($data) {
    $startPoint = $data['s'];
    $endPoint = $data['d'];
    global $wpdb;
    $r = $wpdb->prepare("SELECT DISTINCT ID FROM wp_posts INNER JOIN wp_postmeta m1 ON (wp_posts.ID = m1.post_id) WHERE (post_type = 'projects' AND post_status = 'publish') ORDER BY (m1.meta_value AND m1.meta_key = 'project_featured') ASC LIMIT {$startPoint},{$endPoint}");
    $results = $wpdb->get_results($r, ARRAY_N);
    $projects = array();
    foreach ($results as $key => $val) {
        $projects[] = $val[0];
    }
    return id2Info($projects);
}

function get_featured_pItems($data) {
    $startPoint = $data['s'];
    $endPoint = $data['d'];
    global $wpdb;
    $r = $wpdb->prepare("SELECT ID FROM wp_posts INNER JOIN wp_postmeta m1 ON (wp_posts.ID = m1.post_id) WHERE (post_type = 'projects' AND post_status = 'publish') AND (m1.meta_key = 'project_featured' AND m1.meta_value = '1')");
    $results = $wpdb->get_results($r, ARRAY_N);
    $projects = array();
    foreach ($results as $key => $val) {
        $projects[] = $val[0];
    }
    return id2Info($projects);
}