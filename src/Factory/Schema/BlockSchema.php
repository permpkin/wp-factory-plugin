<?php

namespace Factory\Schema;

use Factory\Utils;

class BlockSchema extends ConfigSchema {
  
  static $defaults = [
    "name"							=> '',
    "title"							=> '',
    "icon"              => '',
    "category"          => 'default',
    "description"				=> '',
    "version"		    		=> '',
    'textdomain'        => '',
    'keywords'          => [],
    'attributes'        => [],
    "supports"					=> [],
  ];

  static function compile($parent, $args)
  {
    foreach($args as $index => $block)
    {
      if (empty($block)) continue;
      
      // merge defaults.
      $blockSchema = array_merge(BlockSchema::$defaults, $block);

      // create register code.
      $code = [
        'register_block_type(get_template_directory().\'/blocks/'.$blockSchema['key'].'/block.json\',[]);'
      ];

      $attr = [];

      $attr["\$schema"] = "https://schemas.wp.org/trunk/block.json";
      $attr["apiVersion"] = 2;
      $attr["title"] = $blockSchema['title'];
      $attr['name'] = $blockSchema['textdomain'].'/'.$blockSchema['key'];
      if ($blockSchema['icon']) {
        $attr['icon'] = $blockSchema['icon'];
      };

      $attributes = [];
      foreach($blockSchema['attributes'] as $attrs) {
        $attributes[$attrs['key']] = [
          'type' => $attrs['type'],
          'required' => (isset($attrs['required']) && $attrs['required'] == "true")
        ];
        if ($attrs['type'] == "enum") {
          $attributes[$attrs['key']]['enum'] = $attrs['value'];
        } else {
          $attributes[$attrs['key']]['default'] = $attrs['value'];
        }
      }
      if (!empty($attributes)) {
        $attr['attributes'] = $attributes;
      }
      
      if(!empty($blockSchema['description'])) {
        $attr['description'] = $blockSchema['description'];
      }

      $attr['textdomain'] = $blockSchema['textdomain'];

      if(!empty($blockSchema['usesContext'])) {
        $attr['usesContext'] = $blockSchema['usesContext'];
      }

      // add default (front-end) script using matching ID
      $attr['editorScript'] = 'file:./block.js';

      // add default (front-end) styles using matching ID
      $attr['editorStyle'] = 'file:./editor.css';

      // add default (front-end) styles using matching ID
      $attr['style'] = 'file:./style.css';
      
      if (is_array($blockSchema['category'])) {
        foreach($blockSchema['category'] as $blockCategory) {
          $parent->addBlockCategory($blockCategory);
        }
      } else {
        $parent->addBlockCategory($blockSchema['category']);
      }

      // add to init hook.
      $parent->addHook('init', join('',$code));

      $source_folder = $_SERVER['APP_PATH'].'/src/blocks/'.$blockSchema['key'];

      // add/update block.json
      file_put_contents($source_folder."/block.json", json_encode($attr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
      
      // create template files if they don't exist.
      if (
        !is_dir($source_folder.'/') &&
        !file_exists($source_folder.'/')
      ) {

        // make the block folder.
        mkdir($source_folder, 0777, true);

        // add frontend styles
        file_put_contents($source_folder."/style.scss", join("\r\n",[
          "// this is your blocks container class.",
          ".wp-block-".$blockSchema['textdomain']."-".$blockSchema['key']." {",
          "    background-color: red;",
          "    padding: 30px;",
          "}"
        ]));

        // add editor styles
        file_put_contents($source_folder."/editor.scss", join("\r\n",[
          "// this is your blocks container class.",
          "@import 'style';",
          ".wp-block-".$blockSchema['textdomain']."-".$blockSchema['key']." {",
          "    background-color: yellow;",
          "    padding: 30px;",
          "}"
        ]));

        // add block script
        file_put_contents($source_folder."/block.jsx", join("\r\n",[
          "import { registerBlockType } from '@wordpress/blocks';",
          "import { useBlockProps } from '@wordpress/block-editor';",
          "",
          "  registerBlockType('".$blockSchema['textdomain']."/".$blockSchema['key']."',",
          "{",
          "     edit: ({ attributes, setAttributes }) => {",
          "        ",
          "        const blockProps = useBlockProps();",
          "",
          "         return (",
          "            <div { ...blockProps }>",
          "            </div>",
          "        );",
          "",
          "     },",
          "     save: function () {",
          "",
          "        const blockProps = useBlockProps.save();",
          "",
          "        return (",
          "            <div { ...blockProps }>",
          "            </div>",
          "        );",
          "",
          "     },",
          " });"
        ]));

      }

    };
  }

}