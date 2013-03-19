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
      add_action('init', array($this, 'register_timeline_wp_post_type'));
    }

    function register_timeline_wp_post_type(){
      register_post_type('timeline_wp', array(
        'labels' => array(
          'name' => 'Timeline.js timelines',
          'singular_name' => 'Timeline.js timeline',
          'add_new' => 'Add New Event',
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
        'hierarchical' => false,
        'supports' => array('title'),
        'register_meta_box_cb' => array($this, 'timeline_wp_meta_boxes')
      ));

      register_taxonomy('timeline_wp_timelines', 'timeline_wp', array(
        'labels' => array(
          'name' => 'Timelines',
          'singular_name' => 'Timeline',
          'all_items' => 'All Timelines',
          'edit_item' => 'Edit Timeline',
          'view_item' => 'View Timeline',
          'add_new_item' => 'Add New Timeline',
          'new_item_name' => 'New Timeline Name',
          'search_items' => 'Search Timelines',
          'separate_items_with_commas' => 'Separate timelines with commas',
          'add_or_remove_items' => 'Add or Remove Timelines',
          'choose_from_most_used' => 'Choose from most used timelines'
        ),
        'hierarchical' => true
      ));

      if(is_admin()){
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-base', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
      }

      add_action('save_post', array($this, 'save_timeline_meta_data'));
      add_action('timeline_wp_timelines_add_form_fields', array($this, 'add_timeline_taxonomy_fields'));
      add_action('timeline_wp_timelines_edit_form_fields', array($this, 'edit_timeline_taxonomy_fields'));
      add_action('created_term', array($this, 'update_timeline_taxonomy_fields'));
      add_action('edit_term', array($this, 'update_timeline_taxonomy_fields'));
    }

    function add_timeline_taxonomy_fields(){
    ?>
      <div class="form-field">
        <label id="label_event_media" for="_event_media">Media</label>
        <input type="text" id="_event_media" name="timeline[_timeline_media]" size="80" /><br />
        <label id="label_event_credit" for="_event_credit">Credit</label>
        <input type="text" id="_event_credit" name="timeline[_timeline_credit]" /><br />
        <label id="label_event_caption" for="_event_caption">Caption</label>
        <input type="text" id="_event_caption" name="timeline[_timeline_caption]" />
      </div>
    <?php
    }

    function edit_timeline_taxonomy_fields($tag){
      $slug = $tag->slug;
      $timeline_media = get_option('_timeline_media_' . $slug);
      $timeline_credit = get_option('_timeline_credit_' . $slug);
      $timeline_caption = get_option('_timeline_caption_' . $slug);
    ?>
      <tr>
        <td>
          <label id="label_event_media" for="_event_media">Media</label>
        </td>
        <td>
          <input type="text" id="_event_media" name="timeline[_timeline_media]" size="80"<? echo !empty($timeline_media) ? ' value="' . $timeline_media . '"' : ''; ?> /><br />
        </td>
      </tr>
      <tr>
        <td>
          <label id="label_event_credit" for="_event_credit">Credit</label>
        </td>
        <td>
          <input type="text" id="_event_credit" name="timeline[_timeline_credit]"<? echo !empty($timeline_credit) ? ' value="' . $timeline_credit . '"' : ''; ?> /><br />
        </td>
      </tr>
      <tr>
        <td>
          <label id="label_event_caption" for="_event_caption">Caption</label>
        </td>
        <td>
          <input type="text" id="_event_caption" name="timeline[_timeline_caption]"<? echo !empty($timeline_caption) ? ' value="' . $timeline_caption . '"' : ''; ?> />
        </td>
      </tr>
    <?php
    }

    function update_timeline_taxonomy_fields(){
      if(isset($_POST['timeline'])){
        $timeline_meta = $_POST['timeline'];

        foreach($timeline_meta as $field => $value){
          update_option($field . '_' . $_POST['slug'], $value);
        }
      }
    }

    function timeline_wp_meta_boxes(){
      add_meta_box(
        'event_date_meta_box',
        'Event/Era Date',
        array($this, 'meta_box_event_date_view'),
        'timeline_wp',
        'normal',
        'high'
      );

      add_meta_box(
        'event_asset_meta_box',
        'Asset',
        array($this, 'meta_box_event_asset_view'),
        'timeline_wp',
        'advanced',
        'high'
      );

      add_meta_box(
        'event_optional_meta_box',
        'Optional',
        array($this, 'meta_box_event_optional_view'),
        'timeline_wp',
        'advanced',
        'default'
      );

      add_action('edit_form_advanced', array($this, 'meta_box_event_text_view'));
    }

    function meta_box_event_date_view(){
      global $post;
      wp_nonce_field(plugin_basename(__FILE__), 'timeline_meta_nonce');

      $event_era = get_post_meta($post->ID, $key = '_event_era', $single = true);
      $start_date = get_post_meta($post->ID, $key = '_start_date', $single = true);
      $end_date = get_post_meta($post->ID, $key = '_end_date', $single = true);

      ?>
      <select name="timeline[_event_era]" id="_event_era">
      <option value="event"<?php echo $event_era === 'event' ? ' selected' : ''; ?>>Event</option>
      <option value="era"<?php echo $event_era === 'era' ? ' selected' : ''; ?>>Era</option>
      </select><br />
      <label id="label_start_date" for="_start_date">Start Date: </label>
      <input type="text" id="_start_date" class="datepicker" name="timeline[_start_date]"<? echo !empty($start_date) ? ' value="' . $start_date . '"' : ''; ?> /><br />
      <label id="label_end_date" for="_end_date">End Date: </label>
      <input type="text" id="_end_date" class="datepicker" name="timeline[_end_date]"<? echo !empty($end_date) ? ' value="' . $end_date . '"' : ''; ?> />
            <script>
            jQuery(function($){
              $('.datepicker').datepicker();
              function displayEndDate(){
                if($('#_event_era').val() === 'event'){
                  $('#_end_date').val('').hide();
                  $('#label_end_date').hide();
                }else if($('#_event_era').val() === 'era'){
                  $('#_end_date').show();
                  $('#label_end_date').show();
                }
              }
              displayEndDate();
              $('#_event_era').change(displayEndDate);
            });
            </script>
      <?php
    }

    function meta_box_event_text_view(){
      global $post;
      $initial_text = get_post_meta($post->ID, $key = '_event_text', $single = true);
?>
<h3 style="position:absolute;">Event Text</h3>
<?php
      wp_editor($initial_text, '_event_text', array(
        'media_buttons' => false,
        'textarea_name' => 'timeline[_event_text]',
        'textarea_rows' => 5,
        'tinymce' => array(
          'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,spellchecker'
        )
      ));
?>
<br />
<?php
    }

    function meta_box_event_asset_view(){
      global $post;
      $event_media = get_post_meta($post->ID, $key = '_event_media', $single = true);
      $event_credit = get_post_meta($post->ID, $key = '_event_credit', $single = true);
      $event_caption = get_post_meta($post->ID, $key = '_event_caption', $single = true);
      ?>
      <label id="label_event_media" for="_event_media">Media: </label>
      <input type="text" id="_event_media" name="timeline[_event_media]" size="80"<? echo !empty($event_media) ? ' value="' . $event_media . '"' : ''; ?> /><br />
      <label id="label_event_credit" for="_event_credit">Credit: </label>
      <input type="text" id="_event_credit" name="timeline[_event_credit]"<? echo !empty($event_credit) ? ' value="' . $event_credit . '"' : ''; ?> /><br />
      <label id="label_event_caption" for="_event_caption">Caption: </label>
      <input type="text" id="_event_caption" name="timeline[_event_caption]"<? echo !empty($event_caption) ? ' value="' . $event_caption . '"' : ''; ?> />
      <?php
    }

    function meta_box_event_optional_view(){
      global $post;
      $event_tag = get_post_meta($post->ID, $key = '_event_tag', $single = true);
      $event_classname = get_post_meta($post->ID, $key = '_event_classname', $single = true);
      ?>
      <label id="label_event_tag" for="_event_tag">Tag: </label>
      <input type="text" id="_event_tag" name="timeline[_event_tag]"<? echo !empty($event_tag) ? ' value="' . $event_tag . '"' : ''; ?> /><br />
      <label id="label_event_classname" for="_event_classname">Class Name: </label>
      <input type="text" id="_event_classname" name="timeline[_event_classname]"<? echo !empty($event_classname) ? ' value="' . $event_classname . '"' : ''; ?> />
      <?php
    }

    function save_timeline_meta_data(){
      global $post_id;
      if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
        return;
      }

      if(!current_user_can('edit_post')){
        return;
      }

      if(!isset($_POST['timeline_meta_nonce']) || !wp_verify_nonce($_POST['timeline_meta_nonce'], plugin_basename(__FILE__))){
        return;
      }

      if(isset($_POST['timeline'])){
        $timeline_meta = $_POST['timeline'];

        foreach($timeline_meta as $field => $value){
          update_post_meta($post_id, $field, $value);
        }
      }
    }
  }
  $timeline_wp = new Timeline_WP();
}
