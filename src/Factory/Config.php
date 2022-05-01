<?php

namespace Factory;

use Factory\Utils;
use Factory\Schema\ConfigConstructor;
use JsonSchema\Validator;

class Config {

  static function get() {
    
    return file_get_contents($_SERVER['APP_PATH'].'/@config.json');

  }

  static function update($data) {

    $config = Config::get();
    
    file_put_contents($_SERVER['APP_PATH'].'/@config.json', array_merge_recursive($config, $data));
    
    return json_encode([
      "success"=>true
    ]);

  }

  static function replace() {
    
    $value = json_encode(input('config'), JSON_PRETTY_PRINT);
    
    file_put_contents($_SERVER['APP_PATH'].'/@config.json', $value);
    
    return json_encode([
      "success"=>true
    ]);

  }

  static function schema($type) {
    
    if (file_exists(__DIR__."/Schema/Types/$type.json")) {
    
      return file_get_contents(__DIR__."/Schema/Types/$type.json");

    } else {
    
      return json_encode([
        "errors"=>["\"$type\" not found"]
      ]);

    }

  }

  static function validate($type="all") {

    return json_encode([
      "valid" => false,
      "errors" => [
      ]
    ]);

    if (file_exists($_SERVER['APP_PATH'].'/@config.json'))
    {

      $validator = new Validator;

      $data = json_decode(file_get_contents($_SERVER['APP_PATH'].'/@config.json'));

      $validator->validate($data, (object)['$ref' => 'file://'.__DIR__.'/Schema.json']);

      // check @json file is valid first.
      if (!$validator->isValid()) {
        return json_encode([
          "valid" => false,
          "errors" => array_map(function($row) {
            $split_pointer = explode('/', $row['pointer']);
            $pointer = array_pop($split_pointer);
            return [
              'title' => 'validation_error',
              'message' => $row['message'],
              'url' => '/wp/factory/config'.preg_replace('/(\/(?!.*\/))/', '#', $row['pointer']),
              'path' => join('.',array_filter($split_pointer, fn($value) => $value !== '' || $value === $pointer)),
              'field' => $pointer
            ];
          }, $validator->getErrors())
        ]);
      }

      $config = new ConfigConstructor(Utils::GetConfigJSON($_SERVER['APP_PATH'].'/@config.json'));

      $config->build();

      $errors = $config->errors($type);
      
      return json_encode(empty($errors) ? [
        "valid" => true
      ] : [
        "valid" => false,
        "errors" => $config->errors($type)
      ]);

    } else {
    
      return json_encode([
        "errors"=>["@config.json file missing"]
      ]);

    }

  }

  static function deploy() {

    if (file_exists($_SERVER['APP_PATH'].'/@config.json'))
    {

      $config = new ConfigConstructor(Utils::GetConfigJSON($_SERVER['APP_PATH'].'/@config.json'));

      $config->build();

      file_put_contents(Utils::GetContentSrc().'/mu-plugins/factory-config.php', $config->compile());

      return json_encode([
        "success"=>true
      ]);

    } else {
    
      return json_encode([
        "errors"=>["@config.json file missing"]
      ]);

    }

  }

  static function verify() {

    $errors = [];

    if (!file_exists($_SERVER['APP_PATH'].'/@config.json'))
    {
      $errors[] = [
        "label" => "@config.json file missing",
        "message" => "create a \"@config.json\" file in the root of your project folder.",
        "severity" => "medium"
      ];
    }

    if (!file_exists(Utils::GetContentSrc().'/mu-plugins/factory-config.php'))
    {
      $errors[] = "factory config not deployed";
      $errors[] = [
        "label" => "@config.json file missing",
        "message" => "create a \"@config.json\" file in the root of your project folder.",
        "severity" => "medium"
      ];
    }

    if (!is_dir(Utils::GetContentSrc().'/plugins/advanced-custom-fields'))
    {
      $errors[] = "missing acf plugin";
      $errors[] = [
        "label" => "missing acf plugin",
        "message" => "download the \"Advanced Custom Fields\" to enable custom fields options.",
        "severity" => "low"
      ];
    }

    if (!file_exists(Utils::GetSrc().'/wp-config.php'))
    {
      $errors[] = [
        "label" => "missing wp-config.php",
        "message" => "click \"generate wp-config\" to resolve.",
        "severity" => "high"
      ];
    }
    
    return json_encode(empty($errors) ? [
      "valid" => true,
      "errors" => []
    ] : [
      "valid" => false,
      "errors" => $errors
    ]);

  }

}
