<?php

namespace Factory\Schema;

use Factory\Utils;

class CustomFieldSchema extends ConfigSchema {

  static function compile($parent, $args)
  {
    if (empty($args)) return;
    $code = 'acf_add_local_field_group('.$parent->ARS;
    $groups = [];
    foreach($args as $fieldGroup)
    {
      // throw error if no location param is present in group.
      if (empty(Utils::CheckAnyContain($args, 'location')))
      {
        throw new \Error("Missing location param.");
      }
      $groups[] = $parent->ARS.Utils::ArrayToString($fieldGroup).$parent->ARN;
    }
    $code .= join(',',$groups).$parent->ARN.');'.$parent->EOL;
    $parent->addHook('acf/init', $code);
  }

}