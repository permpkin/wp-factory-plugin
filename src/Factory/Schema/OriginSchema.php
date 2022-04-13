<?php

namespace Factory\Schema;

class OriginSchema extends BaseSchema {
  static function compile($parent, $args)
  {
    $code = 'add_filter(\'allowed_http_origins\',function($origins){';
    foreach($args as $origin) {
      $code .= '$origins[]=\''.$origin.'\';';
    };
    $code .= 'return $origins;});';
    return $code;
  }
}