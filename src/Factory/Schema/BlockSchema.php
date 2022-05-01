<?php

namespace Factory\Schema;

use Factory\Utils;

class BlockSchema extends ConfigSchema {
  
  static $defaults = [
    "name"							=> '',
    "title"							=> '',
    "icon"              => 'dashicons-warning',
    "category"          => 'default',
    "description"				=> '',
    'align'             => 'full',
    "supports"					=> [
      "align"			=> true,
      "mode"			=> true,
      "multiple"	=> true
    ],
  ];

  static function compile($parent, $args)
  {
    foreach($args as $index => $block)
    {
      if (empty($block)) continue;
      
      // merge defaults.
      $blockSchema = array_merge(BlockSchema::$defaults, $block);

      // set template source.
      $blockSchema["render_template"] = Utils::ThemeUrl('src/blocks/'.$blockSchema['key'].'/template.php');

      // if either @styles or @scripts are set, inject them into the block queue_assets method.
      if (isset($blockSchema['@styles']) || isset($blockSchema['@scripts']))
      {
        $asset_code = 'function(){';
        if (isset($blockSchema['@styles']))
        {
          $asset_code .= join('',ScriptSchema::compile($parent, $blockSchema['@styles'], true));
          unset($blockSchema['@styles']);
        }
        if (isset($blockSchema['@scripts']))
        {
          $asset_code .= join('',ScriptSchema::compile($parent, $blockSchema['@scripts'], true));
          unset($blockSchema['@scripts']);
        }
        $asset_code .= '}';
        $blockSchema['enqueue_assets'] = $asset_code;
      }

      // register field dependencies.
      if (isset($blockSchema['@fields']))
      {
        $parent->addFields(Utils::MergeEach($blockSchema['@fields'], [
          'location' => [
            [
              'param' => 'block',
              'operator' => '==',
              'value' => "acf/{$blockSchema['key']}"
            ]
          ]
        ]));
        unset($blockSchema['@fields']);
      }

      $blockSchema['name'] = $blockSchema['key'];

      unset($blockSchema['key']);

      // create register code.
      $code = [
        'acf_register_block_type([',
        Utils::ArrayToString($blockSchema),
        ']);'.$parent->EOL
      ];
      
      if (is_array($blockSchema['category'])) {
        foreach($blockSchema['category'] as $blockCategory) {
          $parent->addBlockCategory($blockCategory);
        }
      } else {
        $parent->addBlockCategory($blockSchema['category']);
      }

      // add to init hook.
      $parent->addHook('acf/init', join('',$code));
    };
  }

}