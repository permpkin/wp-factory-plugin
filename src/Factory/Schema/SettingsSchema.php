<?php

namespace Factory\Schema;

use Factory\Utils;

class SettingsSchema extends BaseSchema {

  static $defaults = [
    'theme_support'         => [],
    'allowed_origins'       => [],
    'disabled_admin_pages'  => [],
    'disabled_user_roles'   => [],
    'hide_block_categories' => []
  ];

  static function compile($parent, $args)
  {
    $code = [];
    // register post type options pages.
    if (isset($args['theme_support']))
    {
      foreach($args['theme_support'] as $support) {
        if (is_string($support)) {
          $code[] = 'add_theme_support(\''.$support.'\');';
        } else {
          $code[] = 'add_theme_support(\''.$support['type'].'\','.Utils::ExportArrayToString($support['args']).');';
        }
      };
    }
    // register post type options pages.
    if (isset($args['allowed_origins']))
    {
      $code[] = 'add_filter(\'allowed_http_origins\',function($origins){return array_merge($origins,["'.(join('","',$args['allowed_origins'])).'"]);},10,2);';
    }
    // register post type options pages.
    if (isset($args['disabled_admin_pages']))
    {
      foreach($args['disabled_admin_pages'] as $page) {
        $parent->addHook('admin_init', 'remove_menu_page(\''.$page.'\');');
      }
    }
    // register post type options pages.
    if (isset($args['disabled_user_roles']))
    {
      foreach($args['disabled_user_roles'] as $role) {
        $parent->addHook('init', 'remove_role(\''.$role.'\');');
      }
    }
    // register post type options pages.
    if (isset($args['hide_block_categories']))
    {
      $code[] = 'add_filter(\'allowed_block_types_all\',';
      $code[] = 'function($allowed_block_types, $editor_context)';
      $code[] = '{if(!empty($editor_context->post)){return ';
      $code[] = 'array_filter($allowed_block_types,function($v){return in_array($v,["';
      $code[] = join('","',$args['hide_block_categories']);
      $code[] = '"]);});};return $allowed_block_types;},10,2);';
    }

    if (isset($args['disable_rss_feed']) && $args['disable_rss_feed'] === true) {
      $rss_feed_code = [
        "function wpb_disable_feed(){",
        "wp_die(",
        "__('No feed available,please visit our <a href=\"'.get_bloginfo('url').'\">homepage</a>!",
        "'));};",
        "add_action('do_feed','wpb_disable_feed', 1);",
        "add_action('do_feed_rdf','wpb_disable_feed', 1);",
        "add_action('do_feed_rss','wpb_disable_feed', 1);",
        "add_action('do_feed_rss2','wpb_disable_feed', 1);",
        "add_action('do_feed_atom','wpb_disable_feed', 1);",
        "add_action('do_feed_rss2_comments','wpb_disable_feed', 1);",
        "add_action('do_feed_atom_comments','wpb_disable_feed', 1);"
      ];
      $parent->addHook('init', join('',$rss_feed_code));
    }

    if (isset($args['remove_api_meta']) && $args['remove_api_meta'] === true) {
      $parent->addHook('init', "remove_action('wp_head','rest_output_link_wp_head');");
      $parent->addHook('init', "remove_action('wp_head','wp_oembed_add_discovery_links');");
      $parent->addHook('init', "remove_action('template_redirect','rest_output_link_header',11);");
    }

    if (isset($args['remove_oembed']) && $args['remove_oembed'] === true) {
      $parent->addHook('init', "remove_action('wp_head','wp_oembed_add_discovery_links',10);");
    }

    if (isset($args['remove_wlwmanifest_link']) && $args['remove_wlwmanifest_link'] === true) {
      $parent->addHook('init', "remove_action('wp_head','wlwmanifest_link');");
    }

    if (isset($args['remove_rsd_link']) && $args['remove_rsd_link'] === true) {
      $parent->addHook('init', "remove_action('wp_head','rsd_link');");
    }

    if (isset($args['disable_global_styles']) && $args['disable_global_styles'] === true) {
      $parent->addHook('wp_enqueue_scripts', "wp_dequeue_style('global-styles');");
    }
  
    if (isset($args['disable_adminbar']) && $args['disable_adminbar'] === true) {
      $parent->addHook('init', "add_filter('show_admin_bar','__return_false');");
    }

    if (isset($args['remove_generator_meta']) && $args['remove_generator_meta'] === true) {
      $parent->addHook('init', "remove_action('wp_head','wp_generator');");
    }

    if (isset($args['remove_wp_emojis']) && $args['remove_wp_emojis'] === true) {
      $emojiActions = [
        "remove_action('admin_print_styles','print_emoji_styles');",
        "remove_action('wp_head','print_emoji_detection_script',7);",
        "remove_action('admin_print_scripts','print_emoji_detection_script' );",
        "remove_action('wp_print_styles','print_emoji_styles' );",
        "remove_filter('wp_mail','wp_staticize_emoji_for_email' );",
        "remove_filter('the_content_feed','wp_staticize_emoji' );",
        "remove_filter('comment_text_rss','wp_staticize_emoji' );",
        "add_filter( 'tiny_mce_plugins',function(\$plugins){",
        "if(is_array(\$plugins)){",
        "return array_diff(\$plugins,array('wpemoji'));",
        "}else{return array();}",
        "});",
        "add_filter('emoji_svg_url', '__return_false' );"
      ];
      $parent->addHook('init', join('',$emojiActions));
    }

    $parent->addHook('init', join('', $code));
  }
}