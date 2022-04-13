<?php

namespace Factory\Schema;

class OptionSchema extends ConfigSchema {
  
  static $defaults = [
    'page_title'    => 'Options',
    'menu_title'    => 'Options',
    'icon_url' => 'dashicons-forms',
    'capability'    => 'edit_posts',
    'autoload' => true,
    'redirect' => false
  ];

  static function compile($parent, $args)
  {

    foreach($args as $option)
    {
      // merge defaults.
      $optionsSchema = array_merge(OptionSchema::$defaults, $option);

      $menu_slug = \Utils::StringToSlug($optionsSchema['page_title']);

      // register field dependencies.
      if (isset($optionsSchema['@fields']))
      {
        $parent->addFields(\Utils::MergeEach($optionsSchema['@fields'], [
          'location' => [
            [
              'param' => 'options_page',
              'operator' => '==',
              'value' => $menu_slug,
            ]
          ]
        ]));
        unset($optionsSchema['@fields']);
      }

      // create register code.
      $code = join('',[
        'acf_add_options_page('.$parent->ARS,
        \Utils::ArrayToString($optionsSchema),
        $parent->ARN.');'.$parent->EOL
      ]);

      // add to init hook.
      $parent->addHook('acf/init', $code);

      // recursively register child options.
      // (append after parent register)
      if (isset($optionsSchema['@options']))
      {
        $parent->addOptions(\Utils::MergeEach($optionsSchema['@options'], [
          'parent_slug' => "edit.php?post_type={$menu_slug}"
        ]), 'acf_add_options_sub_page');
        unset($optionsSchema['@options']);
      }
    };
  }

}