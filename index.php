<?php
/*
Plugin Name: Wp Not Login Hide
Description: You can hide your posts/pages partially or completely for your visitors.
Plugin URI: https://wpajans.net
Author: Mustafa KÜÇÜK - WpAJANS
Author URI: https://wpajans.net
Version: 1.0
*/

## Language System ##
load_plugin_textdomain('wpnlhLang', false, dirname(plugin_basename(__FILE__)) . '/langs');

## WPNLH Admin Assets ##
add_action('admin_enqueue_scripts', 'wpnlh_admin_assets');
function wpnlh_admin_assets(){
  wp_enqueue_style( 'wpnlh', plugins_url( 'css/wpnlhMain.css', __FILE__ ));
}

## WPNLH Front Assets ##
add_action('wp_footer','wpnlh_front_assets');
function wpnlh_front_assets(){
  wp_enqueue_style('wpnlh', plugins_url( 'css/wpnlh.css', __FILE__ ));
}

## Admin Menu ##
function wpnlhAdminMenu(){
  add_menu_page('WPNLH About','WPNLH','manage_options','wpnlh','wpnlhAboutPage');
  add_submenu_page('wpnlh',__('WPNLH Settings','wpnlhLang'),__('WPNLH Settings','wpnlhLang'),'manage_options','wpnlhSettings','wpnlhSettings');
}
add_action('admin_menu','wpnlhAdminMenu');

## WPNLH About Page ##
function wpnlhAboutPage(){
  ?>


	<div class="card pressthis" style="max-width:100% !important">
	<h2><?php echo _e('Welcome to WordPress Not Login Hide About Page!','wpnlhLang');?></h2>
	<p><?php echo _e('This plugin with optional the user who visited your site for content show you want to log in','wpnlhLang');?></p>
	</div>

  <div class="card pressthis" style="max-width:100% !important">
	<h2><?php echo _e('Shortcode usage','wpnlhLang');?></h2>
	<p><?php echo _e('[wpnlh] test content [/wpnlh]','wpnlhLang');?></p>
	</div>

  <div class="card pressthis" style="max-width:100% !important">
  <h2><?php echo _e('Post Full Hide','wpnlhLang');?></h2>
  <p><?php echo _e('When adding or editing post,page on the right side WPNLH Options take a look at!','wpnlhLang');?></p>
  </div>

  <div class="card pressthis" style="max-width:100% !important">
  <h2><?php echo _e('Donate!','wpnlhLang');?></h2>
  <a class="button button-secondary" target="_blank" href="https://www.patreon.com/mustafakucuk"><span class="dashicons dashicons-smiley"></span> <?php echo _e('Donate','wpnlhLang');?></a>
  <p><?php echo _e('Please donate to develop free add-ons for you','wpnlhLang');?></p>
  </div>
  <?php
}

## WPNLH Settings Page ##
function wpnlhSettings(){
  if($_POST["action"]=="wpnlhUpdate"){
    if (!isset($_POST['wpnlFormNonce']) || ! wp_verify_nonce( $_POST['wpnlFormNonce'], 'wpnlFormNonce' ) ) {
      print 'Error!!!';
      exit;
    }else{
      $wpnlhMessage = sanitize_text_field($_POST['wpnlhMessage']);
      update_option('wpnlhMessage', $wpnlhMessage);
      ?>
      <div class="updated"><p><strong><?php echo _e("Settings saved!","wpnlhLang")?></strong></p></div>
      <?php
    }
  }
  ?>
  <div class='wrap'>
    <div id="wpnlh_navbar"><span> WordPress Not Login Hide <small>1.0</small></span></div>
    <div id="wpnlh_content">
      <div class="wpnlh_content_block">
        <form method="post">
        <?php
          wp_nonce_field('wpnlFormNonce','wpnlFormNonce');
        ?>
        <input type="text" placeholder="<?php echo _e('Message','wpnlhLang');?>" id="wpnlhTextBox" value="<?php echo get_option("wpnlhMessage");?>" name="wpnlhMessage">
        <input type="hidden" name="action" value="wpnlhUpdate">
        <input type="submit" id="wpnlh_button" value="<?php echo _e('Update','wpnlhLang');?>">
        </form>
      </div>
  </div>
  <?php
}

## Shortcode Hide ##
function wpnlhShortcode($str,$content = null){
  if(is_user_logged_in()){
    return $content;
  }else{
    return '<a href="'.wp_login_url().'"><div class="wpnlhLoginBox"><span>'.get_option("wpnlhMessage").'</span></div></a>';
  }
}
add_shortcode('wpnlh','wpnlhShortcode');

## Post Hide ##
function WpnlhMetaBoxContent($object)
{
  wp_nonce_field(basename(__FILE__), "WpnlhMetaBoxonce");
?>
  <div>
    <label for="WpnlhCustomMessage"><?php echo _e('Custom Message','wpnlhLang');?></label>
    <input name="WpnlhCustomMessage" type="text" value="<?php echo get_post_meta($object->ID, "WpnlhCustomMessage", true); ?>">
    <br>
    <label for="WpnlhPostHide"><?php echo _e('Hide?','wpnlhLang');?></label>
    <?php
    $checkbox_value = get_post_meta($object->ID, "WpnlhPostHide", true);
    ?>
    <input name="WpnlhPostHide" type="checkbox" value="true" <?php if($checkbox_value!=''){echo'checked';}?>>
    </div>
    <?php
    }

  function saveOptionsWpnlh($post_id, $post, $update)
  {
      if (!isset($_POST["WpnlhMetaBoxonce"]) || !wp_verify_nonce($_POST["WpnlhMetaBoxonce"], basename(__FILE__)))
          return $post_id;

      if(!current_user_can("edit_post", $post_id))
          return $post_id;

      if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
          return $post_id;

      $slug = "post";
      if($slug != $post->post_type)
      return $post_id;

      if(isset($_POST["WpnlhCustomMessage"]))
      {
          $WpnlhCustomMessage = sanitize_text_field($_POST["WpnlhCustomMessage"]);
      }
      update_post_meta($post_id, "WpnlhCustomMessage", $WpnlhCustomMessage);

      if(isset($_POST["WpnlhPostHide"]))
      {
          $WpnlhPostHide = sanitize_text_field($_POST["WpnlhPostHide"]);
      }
      update_post_meta($post_id, "WpnlhPostHide", $WpnlhPostHide);
  }
  add_action("save_post", "saveOptionsWpnlh", 10, 3);

function addWpnlhMetaBox()
{
    add_meta_box("wpnlhMetaBox", __('WPNLH Options','wpnlhLang'), "WpnlhMetaBoxContent",array('post','page'), "side", "high", null);
}

add_action("add_meta_boxes", "addWpnlhMetaBox");

add_filter("the_content","benim_eklentim_Function");
function benim_eklentim_Function($content){
  global $wp_query;
  $postid = $wp_query->post->ID;
  $WpnlhHide = get_post_meta($postid,"WpnlhPostHide",true);
  if($WpnlhHide=='true'){
    $WpnlhCustomMessage = get_post_meta($postid,"WpnlhCustomMessage",true);
    if(is_user_logged_in()){
      return $content;
    }else{
    if($WpnlhCustomMessage!=''){
      return '<a href="'.wp_login_url().'"><div class="wpnlhLoginBox"><span>'.$WpnlhCustomMessage.'</span></div></a>';
    }
  }
  return '<a href="'.wp_login_url().'"><div class="wpnlhLoginBox"><span>'.get_option("wpnlhMessage").'</span></div></a>';
  }
  return $content;
}
