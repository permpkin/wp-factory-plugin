<?php

namespace Factory\Schema;

class SupportSchema extends BaseSchema {

  static function compile($parent, $args)
  {
    $code = '';
    foreach($args as $optKey => $opt)
    {
      $code .= 'add_theme_support(\'';
      if (is_array($opt)) {
        $code .= $optKey.'\','.$parent->ARS;
        $segments = [];
        foreach($opt as $key=>$val)
        {
          $segment = '';
          if (!is_int($key))
          {
            $segment .= '\''.$key.'\'=>';
          }
          $segment .= is_int($val)?$val:('\''.$val.'\'');
          $segments[] = $segment;
        }
        $code .= join(',',$segments).$parent->ARN;
      } else {
        $code .= $opt.'\'';
      }
      $code .= ');';
    }
    return $code;
  }
  
}