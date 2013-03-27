<?php
if(!class_exists(Timeline_WP_JSON_API)){
  class Timeline_WP_JSON_API{

    function __construct(){
      if(!empty($_GET['timeline'])){
        require('../../../../wp-load.php');
        if(term_exists($_GET['timeline'], 'timeline_wp_timelines')){
          $data_output = array();

          $data_output['timeline'] = $this->timeline_data($_GET['timeline']);
          $nodes = $this->timeline_events_and_eras($_GET['timeline']);

          if(!empty($nodes['events'])){
            $data_output['timeline']['date'] = $nodes['events'];
          }

          if(!empty($nodes['eras'])){
            $data_output['timeline']['era'] = $nodes['eras'];
          }

          if(!empty($data_output)){
            header('Content-Type: application/json');
            echo json_encode($data_output);
            return true;
          }else{
            header($_SERVER['SERVER_PROTOCOL'] . '204 No Content', true, 204);
            return false;
          }
        }
      }
    }

    private function timeline_data($slug){
      $timeline_default_data = get_term_by('slug', $slug, 'timeline_wp_timelines');
      $timeline_media = get_option('_timeline_media_' . $slug);
      $timeline_credit = get_option('_timeline_credit_' . $slug);
      $timeline_caption = get_option('_timeline_caption_' . $slug);

      $timeline_data_output = array(
        'headline' => $timeline_default_data->name,
        'type' => 'default',
        'text' => $timeline_default_data->description,
        'asset' => array(
          'media' => $timeline_media,
          'credit' => $timeline_credit,
          'caption' => $timeline_caption
        )
      );

      return $timeline_data_output;
    }

    private function timeline_events_and_eras($timeline_slug){
      $output = array();

      $query_args = array(
        'post_type' => 'timeline_wp',
        'tax_query' => array(
          array(
            'taxonomy' => 'timeline_wp_timelines',
            'field' => 'slug',
            'terms' => $timeline_slug
          )
        )
      );

      $events_and_eras = new WP_Query($query_args);

      $events = array();
      $eras = array();

      foreach($events_and_eras->posts as $post){
        $meta = get_post_meta($post->ID);

        $event_era = $meta['_event_era'][0];
        $start_date = $meta['_start_date'][0];
        $end_date = $meta['_end_date'][0];
        $event_text = $meta['_event_text'][0];
        $event_media = $meta['_event_media'][0];
        $event_credit = $meta['_event_credit'][0];
        $event_caption = $meta['_event_caption'][0];
        $event_tag = $meta['_event_tag'][0];
        $event_classname = $meta['_event_classname'][0];

        $node = array(
          'startDate' => !empty($start_date) ? $start_date : 0,
          'headline' => $post->post_title,
          'text' => !empty($event_text) ? $event_text : '',
          'asset' => array(
            'media' => !empty($event_media) ? $event_media : '',
            'credit' => !empty($event_credit) ? $event_credit : '',
            'caption' => !empty($event_caption) ? $event_caption : ''
          )
        );

        if(!empty($event_tag)){
          $node['tag'] = $event_tag;
        }

        if(!empty($event_classname)){
          $node['tag'] = $event_classname;
        }

        if($event_era === 'era'){
          array_push($eras, $node);
        }else{
          array_push($events, $node);
        }
      }
      $output['events'] = $events;
      $output['eras'] = $eras;

      return $output;
    }
  }
}
