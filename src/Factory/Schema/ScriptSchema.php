<?php

namespace Factory\Schema;

class ScriptSchema extends BaseSchema {

  static $defaults = [
    'path' => null,
    'insert' => [],
    'dependencies' => [],
    'version' => '0.1',
    'footer' => true
  ];

  static function compile($parent, $args, $return=false)
  {
    $code_cache = [];
    foreach($args as $id=>$opt)
    {
      // merge defaults
      $script = array_merge(ScriptSchema::$defaults, $opt);
      // setup codebase
      $code = 'wp_enqueue_script(\''.$id.'\',\''.$script['source'].'\',';
      // dependencies
      $code .= sizeof($script['dependencies'])>0?$parent->ARS.'\''.join('\',\'', $script['dependencies']).'\''.$parent->ARN:'false';
      // version
      $code .= ',\''.$script['version'].'\'';
      // media
      $code .= ','.($script['footer']?'true':'false');
      $code .= ');'.$parent->EOL;
      $code_cache[] = $code;
      if (!$return) {
        // insert into front/back hooks
        $parent->injectScriptHooks($script['insert'], $code);
      }
    }
    if ($return)
    {
      return $code_cache;
    }
  }
}