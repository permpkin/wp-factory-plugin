<?php

namespace Factory\Schema;

use Factory\Utils;

class SettingSchema extends BaseSchema {

  static $defaults = [
    'title'      => '',
    'capability'  => '',
    'description' => '',
    'priority'   => 30,
    'settings' => []
  ];
  static $fieldDefaults = [
    'default'    => '',
    'label'      => '',
    'type'       => 'theme_mod',
    'input_type' => 'Color',
    'capability' => 'edit_theme_options',
    'transport'  => 'refresh'
  ];

  static function compile($parent, $args)
  {
    foreach($args as $key => $setting)
    {
      continue;
      // TODO: update this for globals (which is now using settings key)
      if (empty($setting)) continue;
      
      // merge defaults.
      $settingSchema = array_merge_recursive(SettingSchema::$defaults, $setting);

      $code = 'add_action(\'customize_register\',function($wp_customize){';
      
      $sectionId = Utils::StringToSlug($settingSchema['title']);

      $code .= '$wp_customize->add_section(\'';
      $code .= $sectionId.'\','.$parent->ARS;
      $code .= '\'title\'=>\''.$settingSchema['title'].'\',';
      $code .= '\'priority\'=>'.$settingSchema['priority'].',';
      if (!empty($settingSchema['capability']))
      {
        $code .= '\'capability\'=>\''.$settingSchema['capability'].'\',';
      }
      $code .= '\'description\'=>\''.$settingSchema['description'].'\',';
      $code .= $parent->ARN.');';
      
      foreach($settingSchema['settings'] as $settingField)
      {
        $settingFieldKey = Utils::StringToSlug($settingField['label']);
        $fieldSchema = array_merge(SettingSchema::$fieldDefaults, $settingField);

        $code .= '$wp_customize->add_setting(\'';
        $code .= $settingFieldKey.'\','.$parent->ARS;
        $code .= '\'default\'=>\''.$settingField['default'].'\',';
        $code .= '\'type\'=>\''.$settingField['type'].'\',';
        if (!empty($settingField['capability']))
        {
          $code .= '\'capability\'=>\''.$settingField['capability'].'\',';
        }
        $code .= '\'transport\'=>\''.$settingField['transport'].'\'';
        $code .= $parent->ARN.');';

        $code .= '$wp_customize->add_control(new WP_Customize_'.$settingField['input_type'].'_Control(';
        $code .= '$wp_customize,';
        $code .= '\''.$settingFieldKey.'_control\','.$parent->ARS;
        $code .= '\'label\'=>\''.$settingField['label'].'\',';
        $code .= '\'settings\'=>\''.$settingFieldKey.'\',';
        $code .= '\'priority\'=>'.$settingField['priority'];
        $code .= $parent->ARN;
        $code .= '));';
      }
        
      $code .= '});';

      $parent->addHook('init', $code);
    }
  }
  
}