<?php

namespace Factory\Schema;

use \Factory\Utils;

class TaxonomySchema extends BaseSchema {

  static $defaults = [
    'object_type'         => [],
    'labels'              => [],
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_admin_column'   => true,
    'show_in_nav_menus'   => true,
    'show_tagcloud'       => true,
  ];

  static function compile($parent, $args)
  {
    foreach($args as $index=>$tax)
    {
      // merge defaults.
      $taxSchema = array_merge(TaxonomySchema::$defaults, $tax);

      // register post type field dependencies.
      if (isset($taxSchema['@fields']))
      {
        $parent->addFields(Utils::MergeEach($taxSchema['@fields'], [
          'location' => [
            [
              'param' => 'post_type',
              'operator' => '==',
              'value' => $taxSchema['key'],
            ]
          ]
        ]));
        unset($taxSchema['@fields']);
      }

      if ($taxSchema['show_in_menu'] == false) {
        unset($taxSchema['show_in_menu']);
      }

      $object_type = [];

      if (isset($taxSchema['object_type'])) {
        $object_type = $taxSchema['object_type'];
        unset($taxSchema['object_type']);
      }

      $key = $taxSchema['key'];
      unset($taxSchema['key']);

      // create register code.
      $code = join('',[
        'register_taxonomy(\'',
        $key,
        '\',["'.join('","',$object_type).'"],'.$parent->ARS,
        Utils::ArrayToString($taxSchema),
        $parent->ARN.');'.$parent->EOL
      ]);

      // add to init hook.
      $parent->addHook('init', $code);
    };
  }

}