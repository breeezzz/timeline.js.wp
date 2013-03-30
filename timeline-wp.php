<?php
/*
Plugin Name: Timeline.WP
Plugin URI: http://github.com/kylereicks/timeline.js.wp
Description: A wordpress plugin to create timeline.js timelines to wordpress.
Author: Kyle Reicks
Version: 0.1
Author URI: http://kylereicks.me
*/

if(!class_exists('Timeline_WP')){
  class Timeline_WP{

    private $timeline_args = array();

    function __construct(){
      require('php/class-timeline-wp-post-type.php');

      add_action('wp_enqueue_scripts', array($this, 'timeline_scripts'));

      add_action('wp_footer', array($this, 'localize_timeline_data'));

      $timeline_wp_post_type = new Timeline_WP_Post_Type();

      add_shortcode('timeline_js', array($this, 'timeline_shortcode'));
    }

    function localize_timeline_data(){
      wp_localize_script('timeline_js_activation', 'timelines', $this->timeline_args);
    }

    function timeline_shortcode($atts){
      extract(shortcode_atts(array(
        'timeline' => '',
        'width' => '100%',
        'height' => '600'
      ), $atts));

      if(!empty($timeline)){
        wp_enqueue_script('timeline_js_activation');
        $src = plugins_url('data/json.php?timeline=' . $timeline, __FILE__);
        $this->timeline_args[] = array(
          'timeline' => 'timeline_' . $timeline,
          'src' => $src,
          'width' => $width,
          'height' => $height
        );
        $output = '<div id="timeline_' . $timeline .'"></div>';
        return $output;
      }
    }

    function timeline_scripts(){
      wp_register_script('timeline_js', plugins_url('timeline.js/js/storyjs-embed.js', __FILE__), array('jquery'), false, true);
      wp_register_script('timeline_js_activation', plugins_url('js/timeline-activation.js', __FILE__), array('timeline_js'), false, true);
    }
  }
  $timeline_wp = new Timeline_WP();
}
