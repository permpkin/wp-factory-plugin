<?php

namespace Factory\Schema;

class ScriptSchema extends BaseSchema {

  static $defaults = [
    'source' => null,
    'location' => 'front',
    'dependencies' => [],
    'version' => '0.1',
    'footer' => true
  ];

  static function compile($parent, $args, $return=false)
  {
    $code_cache = [];
    foreach($args as $index=>$opt)
    {
      // merge defaults
      $script = array_merge(ScriptSchema::$defaults, $opt);
      // setup codebase
      $code = 'wp_enqueue_script(\''.$script['key'].'\','.(is_array($script['source'])?$script['source'][0]:'\''.$script['source'].'\'').',';
      // dependencies
      if (!is_array($script['dependencies']) && substr((string)$script['dependencies'],0,1) == "$") {
        $code .= $script['dependencies'];
      } else if (!empty($script['dependencies'])) {
        $code .= sizeof($script['dependencies'])>0?$parent->ARS.'\''.join('\',\'', $script['dependencies']).'\''.$parent->ARN:'false';
      } else {
        $code .= 'false';
      }
      // version
      if (!is_array($script['version']) && substr((string)$script['version'],0,1) == "$") {
        $code .= ','.$script['version'];
      } else {
        $code .= ',\''.$script['version'].'\'';
      }
      // media
      $code .= ','.($script['footer']?'true':'false');
      $code .= ');'.$parent->EOL;
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