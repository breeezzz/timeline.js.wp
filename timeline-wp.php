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

    function __construct(){
      require('php/class-timeline-wp-post-type.php');

      add_action('wp_enqueue_scripts', array($this, 'timeline_scripts'));

      $timeline_wp_post_type = new Timeline_WP_Post_Type();

      $this->make_shortcodes();
    }

    private function make_shortcodes(){
      $timelines = get_terms('timeline_wp_timelines');

      foreach($timelines as $timeline){
        add_shortcode('timeline_js', array($this, 'timeline_shortcode'));
      }
    }

    function timeline_shortcode($atts){
      $timeline_atts = array();

      extract(shortcode_atts(array(
        'timeline' => '',
        'width' => '100%',
        'height' => '600'
      ), $atts));

      if(!empty($timeline)){
        $timeline_atts['type'] = 'timeline';
        $src = plugins_url('data/json.php?timeline=' . $timeline, __FILE__);
        $timeline_atts['embed_id'] = 'timeline-' . $timeline;
        $timeline_atts['width'] = $width;
        $timeline_atts['height'] = $height;

        $output = '<div id="timeline_' . $timeline .'"></div>';
        $output .= '<script>
          jQuery(function($){
            createStoryJS({
              type: \'timeline\',
                width: \'' . $width . '\',
                height: \'' . $height . '\',
                source: \'' . $src . '\',
                embed_id: \'timeline_' . $timeline . '\'
            });
          });
        </script>';

        return $output;
      }
    }

    function timeline_scripts(){
      wp_register_script('timeline_js', plugins_url('timeline.js/js/storyjs-embed.js', __FILE__), array('jquery'), false, false);
      wp_enqueue_script('timeline_js');
    }
  }
  $timeline_wp = new Timeline_WP();
}
