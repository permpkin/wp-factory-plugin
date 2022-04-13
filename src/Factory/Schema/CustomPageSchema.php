<?php

namespace Factory\Schema;

class CustomPageSchema extends BaseSchema {
  
  static $defaults = [
    'page_title' => '',
    'menu_title' => '',
    'capability' => '',
    'menu_slug' => '',
    'icon_url' => '',
    'callback' => '',
    'position' => 0
  ];

  static function compile($parent, $args)
  {
    foreach($args as $key => $page)
    {
      if (empty($page)) continue;
      
      // merge defaults.
      $pageSchema = array_merge(CustomPageSchema::$defaults, $page);

      $code = 'add_menu_page(';
      $code .= '\''.$pageSchema['page_title'].'\',';
      $code .= '\''.$pageSchema['menu_title'].'\',';
      $code .= '\''.$pageSchema['capability'].'\',';
      $code .= '\''.$pageSchema['menu_slug'].'\',';
      if (!isset($page['callback']))
      {
        throw new \Error("Custom Page (".$pageSchema['page_title'].") callback not defined");
      }
      $callbackFilePath = \Utils::ThemeUrl($pageSchema['callback']);
      if (!file_exists($callbackFilePath))
      {
        throw new \Error("Custom Page Callback (".$callbackFilePath.") file not found");
      }
      $callbackOutput = 'function(){include \''.$callbackFilePath.'\';}';
      $code .= $callbackOutput.',';
      $code .= '\''.$pageSchema['icon_url'].'\',';
      $code .= $pageSchema['position'];
      $code .= ');';

      $parent->addHook('init', $code);
    }
  }
}