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
    $parent->addHook('init', join('', $code));
  }
}