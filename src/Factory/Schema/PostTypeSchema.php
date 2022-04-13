<?php

namespace Factory\Schema;

use \Factory\Utils;

class PostTypeSchema extends BaseSchema {

  static $defaults = [
    'label'                 => 'Post Type',
    'description'           => 'Post Type Description',
    'supports'              => false,
    'taxonomies'            => [],
    'hierarchical'          => false,
    'public'                => false,
    'show_ui'               => false,
    'show_in_menu'          => false,
    'menu_position'         => 0,
    'show_in_admin_bar'     => false,
    'show_in_nav_menus'     => false,
    'can_export'            => false,
    'has_archive'           => false,
    'exclude_from_search'   => false,
    'publicly_queryable'    => false,
    'capability_type'       => 'page',
  ];

  static function compile($parent, $args)
  {
    foreach($args as $key=>$type)
    {
      // merge defaults.
      $typeSchema = array_merge(PostTypeSchema::$defaults, $type);

      // register post type styles.
      if (isset($typeSchema['@styles']))
      {
        $parent->addStyles($typeSchema['@styles']);
        unset($typeSchema['@styles']);
      }

      // register post type scripts.
      if (isset($typeSchema['@scripts']))
      {
        $parent->addScripts($typeSchema['@scripts']);
        unset($typeSchema['@scripts']);
      }

      // register post type field dependencies.
      if (isset($typeSchema['@fields']))
      {
        $parent->addFields(Utils::MergeEach($typeSchema['@fields'], [
          'location' => [
            [
              'param' => 'post_type',
              'operator' => '==',
              'value' => $key,
            ]
          ]
        ]));
        unset($typeSchema['@fields']);
      }

      // register post type options pages.
      if (isset($typeSchema['@options']))
      {
        // throw error on nested options from post type config. (unsupported).
        if (!empty(Utils::CheckAnyContain($typeSchema['@options'], '@options')))
        {
          throw new \Error('Post Type ('.$typeSchema['label'].') nested \"@options\" not permitted.');
        };

        // register post type options pages
        $parent->addOptions(Utils::MergeEach($typeSchema['@options'], [
          'parent_slug' => "edit.php?post_type={$key}"
        ]));
        unset($typeSchema['@options']);
      }

      // create register code.
      $code = join('',[
        'register_post_type(\'',
        $key,
        '\','.$parent->ARS,
        Utils::ArrayToString($typeSchema),
        $parent->ARN.');'.$parent->EOL
      ]);

      // add to init hook.
      $parent->addHook('init', $code);
    };
  }

}