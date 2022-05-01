<?php

namespace Factory\Schema;

class StyleSchema extends BaseSchema {

  static $defaults = [
    'path' => null,
    'location' => 'front',
    'dependencies' => [],
    'version' => '0.1',
    'media' => 'all'
  ];

  static function compile($parent, $args, $return=false)
  {
    $code_cache = [];
    foreach($args as $index=>$opt)
    {
      // merge defaults
      $script = array_merge(StyleSchema::$defaults, $opt);
      // setup codebase
      $code = 'wp_enqueue_style(\''.$script['key'].'\',\''.$script['source'].'\',';
      // dependencies
      $code .= sizeof($script['dependencies'])>0?$parent->ARS.'\''.join('\',\'', $script['dependencies']).'\''.$parent->ARN:'false';
      // version
      $code .= ',\''.$script['version'].'\'';
      // media
      $code .= ',\''.$script['media'].'\');'.$parent->EOL;
      $code_cache[] = $code;
      if (!$return) {
        // insert into front/back hooks
        $parent->injectScriptHooks($script['location'], $code);
      }
    }
    if ($return)
    {
      return $code_cache;
    }
  }
}