<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
    Plugin Name: AddFunc Head & Footer Code
    Plugin URI:
    Description: Allows administrators to add code to the &lt;head&gt; and/or &lt;footer&gt; of an individual post and/or site-wide. Ideal for scripts such as Google Analytics conversion tracking codes and any other general or page-specific JavaScript.
    Version: 2.5
    Author: AddFunc
    Author URI: http://profiles.wordpress.org/addfunc
    Text Domain: addfunc-head-footer-code
    License: GPLv2 or later
    @since 3.0.1
           ______
       _  |  ___/   _ _ __   ____
     _| |_| |__| | | | '_ \ / __/™
    |_ Add|  _/| |_| | | | | (__
      |_| |_|   \__,_|_| |_|\___\
                    by Joe Rhoney
*/



/*
    S E T T I N G S   P A G E
    =========================
    For site-wide head and footer code
*/

if(!class_exists('aFHFCClass')) :
  define('AFHDFTRCD_ID', 'aFhfc');
  define('AFHDFTRCD_NICK', 'Head & Footer Code');
  class aFHFCClass {
    public static function file_path($file) {
      return plugin_dir_path(__FILE__).$file;
    }
    public static function register() {
      register_setting(AFHDFTRCD_ID.'_options', 'aFhfc_site_wide_head_code');
      register_setting(AFHDFTRCD_ID.'_options', 'aFhfc_head_code_priority');
      register_setting(AFHDFTRCD_ID.'_options', 'aFhfc_site_wide_body_code');
      register_setting(AFHDFTRCD_ID.'_options', 'aFhfc_site_wide_footer_code');
      register_setting(AFHDFTRCD_ID.'_options', 'aFhfc_footer_code_priority');
    }
    public static function register_meta_keys() {
      register_meta('post', 'aFhfc_head_code', array(
        'auth_callback'     => function() { return current_user_can('manage_options'); },
        'show_in_rest'      => false,
      ));
      register_meta('post', 'aFhfc_body_code', array(
        'auth_callback'     => function() { return current_user_can('manage_options'); },
        'show_in_rest'      => false,
      ));
      register_meta('post', 'aFhfc_footer_code', array(
        'auth_callback'     => function() { return current_user_can('manage_options'); },
        'show_in_rest'      => false,
      ));
    }
    public static function menu() {
      add_options_page(AFHDFTRCD_NICK.' Plugin Options', AFHDFTRCD_NICK, 'manage_options', AFHDFTRCD_ID.'_options', array('aFHFCClass', 'options_page'));
    }
    public static function options_page() {
      if (!current_user_can('manage_options'))
      {
        wp_die(__('You do not have sufficient permissions to access this page.', 'addfunc-head-footer-code'));
      }
      $plugin_id = AFHDFTRCD_ID;
      include(self::file_path('options.php'));
    }
    public static function output_head_code() {
      $site_head_code = get_option('aFhfc_site_wide_head_code');
      $meta_head_code = ((is_archive()) || (is_author()) || (is_category()) || (is_tag()) || (is_home()) || (is_search()) || (is_404())) ? '' : get_post_meta(get_the_ID(),'aFhfc_head_code',true);
      $head_replace = get_post_meta(get_the_ID(),'aFhfc_head_replace',true);
      if(!empty($head_replace)) {
        echo $meta_head_code."\n";
      }else{
        echo $site_head_code."\n".$meta_head_code."\n";
      }
    }
    public static function output_body_code() {
      $site_body_code = get_option('aFhfc_site_wide_body_code');
      $meta_body_code = ((is_archive()) || (is_author()) || (is_category()) || (is_tag()) || (is_home()) || (is_search()) || (is_404())) ? '' : get_post_meta(get_the_ID(),'aFhfc_body_code',true);
      $body_replace = get_post_meta(get_the_ID(),'aFhfc_body_replace',true);
      if(!empty($body_replace)) {
        return $meta_body_code."\n";
      }else{
        return $site_body_code."\n".$meta_body_code."\n";
      }
    }
    public static function output_footer_code() {
      $site_footer_code = get_option('aFhfc_site_wide_footer_code');
      $meta_footer_code = ((is_archive()) || (is_author()) || (is_category()) || (is_tag()) || (is_home()) || (is_search()) || (is_404())) ? '' : get_post_meta(get_the_ID(),'aFhfc_footer_code',true);
      $footer_replace = get_post_meta(get_the_ID(),'aFhfc_footer_replace',true);
      if(!empty($footer_replace)) {
        echo $meta_footer_code."\n";
      }else{
        echo $site_footer_code."\n".$meta_footer_code."\n";
      }
    }
  }
  add_action('init', array('aFHFCClass','register_meta_keys'));
  if (is_admin()) {
    add_action('admin_init', array('aFHFCClass','register'));
    add_action('admin_menu', array('aFHFCClass','menu'));
  }
  $head_code_prior = get_option('aFhfc_head_code_priority');
  if(!empty($head_code_prior)) {
    add_action('wp_head', array('aFHFCClass','output_head_code'),$head_code_prior);
  }
  else {
    add_action('wp_head', array('aFHFCClass','output_head_code'));
  }
  function aFHFCBuffRec() {
    ob_start();
  }
  add_action('wp_head','aFHFCBuffRec');
  function aFHFCBuffPlay() {
    $body_code = new aFHFCClass;
    $pattern = '/<[bB][oO][dD][yY]\s[A-Za-z]{2,5}[A-Za-z0-9 "_,=%*\'\/():;\[\]\-\.]+>|<body>/';
    $queue = array();
    $tape = ob_get_clean();
    preg_match($pattern,$tape,$queue);
    $q = empty($queue[0]) ? '' : $queue[0].$body_code->output_body_code();
    echo preg_replace($pattern,$q,$tape);
  }
  add_action('wp_print_footer_scripts','aFHFCBuffPlay');
  $footer_code_prior = get_option('aFhfc_footer_code_priority');
  if(!empty($footer_code_prior)) {
    add_action('wp_footer', array('aFHFCClass','output_footer_code'),$footer_code_prior);
  }
  else {
    add_action('wp_footer', array('aFHFCClass','output_footer_code'));
  }
endif;



/*
    M E T A B O X   F O R   P O S T S
    =================================
    Metabox w/head & footer fields for 
    all post types (including custom)
*/

add_action('add_meta_boxes','aFhfc_add');
function aFhfc_add() {
  if(current_user_can('manage_options')) {
    $args = array('public'=>true);
    $post_types = get_post_types($args);
    add_meta_box('aFhfcMetaBox','Head & Footer Code','aFhfc_mtbx',$post_types,'normal','low');
  }
}
function aFhfc_mtbx($post) {
  $values = get_post_custom($post->ID);
  $head_text = isset($values['aFhfc_head_code']) ? esc_attr($values['aFhfc_head_code'][0]) : '';
  $head_replace = isset($values['aFhfc_head_replace']) ? esc_attr($values['aFhfc_head_replace'][0]) : '';
  $body_text = isset($values['aFhfc_body_code']) ? esc_attr($values['aFhfc_body_code'][0]) : '';
  $body_replace = isset($values['aFhfc_body_replace']) ? esc_attr($values['aFhfc_body_replace'][0]) : '';
  $footer_text = isset($values['aFhfc_footer_code']) ? esc_attr($values['aFhfc_footer_code'][0]) : '';
  $footer_replace = isset($values['aFhfc_footer_replace']) ? esc_attr($values['aFhfc_footer_replace'][0]) : '';
  wp_nonce_field('aFhfc_nonce', 'aFhfc_mb_nonce');
  ?>
  <div style="border: 1px solid #b94a48; border-radius: 4px; padding: 0 1em 1em; margin-bottom: 2em;">
    <p style="color: #b94a48; line-height: 1.3; margin-bottom: .5em;"><strong style="font-size: 1.25em;">WARNING</strong><br />
    <em>Mandatory reading:</em></p>
    <details>
      <summary style="color: #b94a48; font-weight: bold;">Security Risk</summary>
      <div class="description">
        <p><strong>Overview:</strong> Values entered here are stored and output raw into site pages without sanitization or escaping. Any HTML or JavaScript will execute in visitors' browsers with the same privileges as the page.</p>
        <p><strong>Potential impacts:</strong> Cross-site scripting (XSS), session or credential theft, data exfiltration, third-party script compromises that serve malware or tracking, broken layout or runtime errors, and degraded performance or outage if scripts are faulty or load slow resources.</p>
        <p><strong>High-risk patterns to avoid:</strong> <code>eval()</code>, <code>document.write()</code>, inline event handlers, inserting unreviewed third-party snippets, code that reads/writes cookies or localStorage, and scripts that load remote resources from untrusted domains.</p>
        <p><strong>Who should edit:</strong> Only fully trusted administrators (capability: manage_options) should add or modify these fields. Do not grant this capability to untrusted users.</p>
        <p><strong>Mitigations:</strong> Prefer server-side integrations or registering scripts via <code>wp_enqueue_script()</code>, host assets over HTTPS, enable Content Security Policy (CSP) and Subresource Integrity (SRI) when possible, review external sources before adding, and keep WordPress, themes, and plugins updated.</p>
      </div>
    </details>
    <details>
      <summary style="color: #b94a48; font-weight: bold;">Guidelines</summary>
      <div class="description">
        <p>Follow these pragmatic steps to reduce risk and keep changes reversible:</p>
        <ul style="list-style: disc; margin-left: 1.25em;">
          <li>Test changes in a staging or development environment first.</li>
          <li>Create a full backup (files + database) before adding or modifying code.</li>
          <li>Add only the minimal snippet required; prefer vendor libraries from a single, trusted source.</li>
          <li>Prefer using <code>wp_enqueue_script()</code> in a theme or plugin instead of injecting code here when feasible.</li>
          <li>For external scripts, use <code>async</code>/<code>defer</code> attributes and SRI (if available) and load from HTTPS endpoints only.</li>
          <li>Document the change with a date, reason, and author so it can be audited and removed if needed.</li>
          <li>Monitor the site after deployment for console errors, slow requests, security alerts, and unexpected network calls.</li>
          <li>Remove or expire temporary marketing or tracking scripts promptly—do not leave unused code in place.</li>
        </ul>
        <p>If you are not confident making these changes safely, consult a developer for assistance.</p>
      </div>
    </details>
  </div>
  <p style="padding-bottom: 1em;">
    <label for="aFhfc_head_code">Head:</label>
    <textarea class="large-text" name="aFhfc_head_code" id="aFhfc_head_code" style="width: 100%;"><?php echo $head_text; ?></textarea>
    <input id="aFhfc_head_replace" type="checkbox" name="aFhfc_head_replace" value="1" <?php checked($head_replace,'1'); ?> />
    <label for="aFhfc_head_replace">Replace Site-wide Head Code</label>
  </p>
  <p style="padding-bottom: 1em;">
    <label for="aFhfc_body_code">Body Start: <span class="dashicons dashicons-info" title="Inserts immediately after the opening body tag."></span></label>
    <textarea class="large-text" name="aFhfc_body_code" id="aFhfc_body_code" style="width: 100%;"><?php echo $body_text; ?></textarea>
    <input id="aFhfc_body_replace" type="checkbox" name="aFhfc_body_replace" value="1" <?php checked($body_replace,'1'); ?> />
    <label for="aFhfc_body_replace">Replace Site-wide Body Code</label>
  </p>
  <p style="padding-bottom: 1em;">
    <label for="aFhfc_footer_code">Footer:</label>
    <textarea class="large-text" name="aFhfc_footer_code" id="aFhfc_footer_code" style="width: 100%;"><?php echo $footer_text; ?></textarea>
    <input id="aFhfc_footer_replace" type="checkbox" name="aFhfc_footer_replace" value="1" <?php checked($footer_replace,'1'); ?> />
    <label for="aFhfc_footer_replace">Replace Site-wide Footer Code</label>
  </p>
  <?php
}
add_action('save_post','aFhfc_save');
function aFhfc_save($post_id) {
  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)return;
  if(!isset($_POST['aFhfc_mb_nonce']) || !wp_verify_nonce($_POST['aFhfc_mb_nonce'],'aFhfc_nonce'))return;
  if(!current_user_can('manage_options'))return;

  if(isset($_POST['aFhfc_head_code']))
    if(empty($_POST['aFhfc_head_code']))
      delete_post_meta($post_id,'aFhfc_head_code');
    else
      update_post_meta($post_id,'aFhfc_head_code',$_POST['aFhfc_head_code']);
  $aFHFCHRChk = (isset($_POST['aFhfc_head_replace']) && $_POST['aFhfc_head_replace'])?'1':'';
  if(empty($_POST['aFhfc_head_replace']))
    delete_post_meta($post_id,'aFhfc_head_replace');
  else
    update_post_meta($post_id,'aFhfc_head_replace',$aFHFCHRChk);

  if(isset($_POST['aFhfc_body_code']))
    if(empty($_POST['aFhfc_body_code']))
      delete_post_meta($post_id,'aFhfc_body_code');
    else
      update_post_meta($post_id,'aFhfc_body_code',$_POST['aFhfc_body_code']);
  $aFHFCHRChk = (isset($_POST['aFhfc_body_replace']) && $_POST['aFhfc_body_replace'])?'1':'';
  if(empty($_POST['aFhfc_body_replace']))
    delete_post_meta($post_id,'aFhfc_body_replace');
  else
    update_post_meta($post_id,'aFhfc_body_replace',$aFHFCHRChk);

  if( isset($_POST['aFhfc_footer_code']))
    if( empty($_POST['aFhfc_footer_code']))
      delete_post_meta($post_id,'aFhfc_footer_code');
    else
      update_post_meta($post_id,'aFhfc_footer_code',$_POST['aFhfc_footer_code']);
  $aFHFCFRChk = (isset($_POST['aFhfc_footer_replace']) && $_POST['aFhfc_footer_replace'])?'1':'';
  if(empty($_POST['aFhfc_footer_replace']))
    delete_post_meta($post_id,'aFhfc_footer_replace');
  else
    update_post_meta($post_id,'aFhfc_footer_replace',$aFHFCFRChk);
}
