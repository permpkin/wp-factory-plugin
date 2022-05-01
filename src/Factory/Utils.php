<?php

namespace Factory;

class Utils {

  static function ThemeUrl($path)
  {
    return $path;
  }

  static function StartsWith($string, $match) 
  { 
    if (!is_string($string)) return false;
    $len = strlen($match);
    return (substr($string, 0, $len) === $match); 
  } 

  static function ArrayToString($array=[])
  {
    if (empty($array)) return '';
    $segments = [];
    foreach($array as $key=>$val)
    {
      $segment = '';
      // if key is a string, set key
      if (!is_int($key))
      {
        $segment .= '\''.$key.'\'=>';
      }
      // if value is integer, or a function return raw value
      if (is_int($val) || Utils::StartsWith($val, 'function()'))
      {
        $segment .= $val;
      }
      // if value is array, wrap + recursively append.
      else if (is_array($val))
      {
        $segment .= '['.Utils::ArrayToString($val).']';
      }
      // if is defined string.
      else if (is_string($val))
      {
        $segment .= '\''.$val.'\'';
      }
      // if is defined boolean.
      else if (is_bool($val))
      {
        $segment .= $val ? 'true' : 'false';
      }
      // otherwise define value as supplied.
      else
      {
        $segment .= $val;
      };
      $segments[] = $segment;
    }
    return join(',', $segments);
  }

  static function GetSrc()
  {
    return $_SERVER['APP_PATH']."/public";
  }

  static function GetContentSrc()
  {
    return Utils::GetSrc()."/wp-content";
  }

  static function GetThemeSrc()
  {
    return Utils::GetContentSrc()."/themes/".$_ENV['APP_THEME'];
  }

  /**
   * Scan directory for configuration files.
   * @param folder target path to scan
   */
  static function GetConfig($folder)
  {
    $configMerge = [];
    if (is_dir($folder))
    {
      $scriptFound = preg_grep('~\.(php)$~', scandir($folder));
      $scriptQueue = (!empty($scriptFound))?array_values($scriptFound):[];
      foreach($scriptQueue as $scriptSource)
      {
        $configInclude = include $folder . '/' . $scriptSource;
        if (!is_array($configInclude))
        {
          throw new \Error("(".$scriptSource.") invalid config file, expecting array.");
        }
        // ignore if file is empty array
        if (!empty($configInclude))
        {
          $configMerge[rtrim($scriptSource, '.php')] = $configInclude;
        }
      }
    };
    return $configMerge;
  }

  /**
   * Scan directory for folders, scan each folder for config.
   * @param folder target path to scan
   */
  static function GetEachConfig($path)
  {
    $configMerge = [];
    if (!is_dir($path)) return $configMerge;
    $response = scandir($path);
    foreach ($response as $folder) {
        if ($folder === '.' or $folder === '..') continue;
        if (is_dir($path . '/' . $folder)) {
          $configMerge[$folder] = include $path . '/' . $folder . '/config.php';
        }
    }
    return $configMerge;
  }

  /**
   * Get Config JSON file
   * @param file target file to import
   */
  static function GetConfigJSON($file)
  {
    if (file_exists($file))
    {
      return json_decode(file_get_contents($file), true);
    };
  }

  /**
   * Return string in slugified format.
   * @param string
   */
  static function StringToSlug($string)
  {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
  }

  /**
   * Merge arrays that contain data.
   */
  static function MergePopulated()
  {
    $args = func_get_args();
    $array_merge = [];
    foreach($args as $array)
    {
      if (!empty($array))
      {
        $array_merge = array_merge($array_merge, $array);
      }
    }
    return $array_merge;
  }

  /**
   * Merge specified data with each item in array.
   */
  static function MergeEach($array, $data)
  {
    foreach($array as $key => $row)
    {
      $array[$key] = array_merge($row, $data);
    }
    return $array;
  }

  /**
   * Merge specified data with each item in array.
   */
  static function UnsetFromEach($array, $field)
  {
    foreach($array as $key => $row)
    {
      if (is_array($row))
      {
        if (isset($row[$field]))
        {
          unset($row[$field]);
        }
      }
    }
    return $array;
  }

  /**
   * Merge specified data with each item in array.
   */
  static function CheckAnyContain($array, $field)
  {
    $matches = [];

    foreach($array as $key => $row)
    {
      if (is_array($row))
      {
        if (isset($row[$field]))
        {
          $matches[] = $row[$field];
        }
      }
    }
    return $matches;
  }
  
  /**
   * Group by array key value.
   */
  static function GroupBy($key, $array)
  {
    $return = array();

    foreach($array as $val) {
        $return[$val[$key]][] = $val;
    }

    return $return;
  }

  /**
   * Validate PHP String
   */
  static function ValidatePHP($str) {

    $temp_file = "/tmp/".(rand(0,999).time()).".php";

    // create temp file
    file_put_contents($temp_file, $str);

    // validate temp file
    exec("php -l $temp_file", $output, $result);

    // remove temp file
    unlink($temp_file);
    
    return $result == 0;

  }

  /**
   * Export PHP Associative Array to PHP File
   */
  static function ExportArrayToString($arr) {

    $output = '[';
    
    foreach($arr as $key => $node)
    {
      $output .= "'".$key."' => ";
      if (is_array($node)) {
        $output .= Utils::ExportArrayToString($node).",";
      } else {
        $output .= "'".$node."',";
      }
    }
    
    return $output.']';

  }
}