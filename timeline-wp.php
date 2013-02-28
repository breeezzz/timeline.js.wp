<?php
/*
Plugin Name: Timeline.WP
Plugin URI: http://github.com/kylereicks/timeline.js.wp
Description: A wordpress plugin to create timeline.js timelines to wordpress.
Author: Kyle Reicks
Version: 0.1
Author URI: http://kylereicks.me
*/

if(!class_exists(Timeline_WP)){
  class Timeline_WP{

    function __construct(){
      add_action('init', array($this, 'register_timeline_wp_post_type'));
    }

    function register_timeline_wp_post_type(){
      register_post_type('timeline_wp', array(
        'labels' => array(
          'name' => 'Timeline.js timelines',
          'singular_name' => 'Timeline.js timeline',
          'add_new' => 'Add New',
          'add_new_item' => 'Add New Event',
          'all_items' => 'All Events',
          'edit_item' => 'Edit Event',
          'new_item' => 'New Event',
          'view_item' => 'View Event',
          'search_items' => 'Search Events',
          'not_found' => 'No Events match your query'
        ),
        'description' => 'Events/Eras are grouped together onto timelines via the "Timelines" taxonomy.',
        'public' => true,
        'menu_position' => 20,
        'capability_type' => 'post',
        'hierarchical' => true,
        'supports' => array('title', 'editor'),
        'register_meta_box_cb' => array($this, 'timeline_wp_meta_box')
      ));

      register_taxonomy('timeline_wp_timelines', 'timeline_wp', array(
        'labels' => array(
          'name' => 'Timelines',
          'singular_name' => 'Timeline'
          'all_items' => 'All Timelines',
          'edit_item' => 'Edit Timeline'
        )
      ));
    }

    function timeline_wp_meta_box(){

    }

  }
  $timeline_wp = new Timeline_WP();
}
