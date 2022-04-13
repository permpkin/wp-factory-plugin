<?php
/**
 * @package ConfigConstructor
 * - acts as root register map.
 */
namespace Factory\Schema;

use Factory\Schema\ConfigSchema;
use Factory\Utils;

class ConfigConstructor
{
  private $configSource;
  private $configSchema;
  public $EOL = "";
  public $ARR = ["[","]"];
  public $validation_errors = [];
  
  function __construct($source=false, $args=[])
  {
    // throw error if config not set.
    if (!is_array($source))
    throw new \Error('MISSING_CONFIG');
    if (isset($_ENV['EOL'])) $this->EOL = $_ENV['EOL'];
    if (isset($_ENV['ARR'])) $this->ARR = explode(',',$_ENV['ARR']);
    // set config base
    $this->configSource = $source;
    $this->configSchema = new ConfigSchema([
      'EOL' => $this->EOL,
      'ARR' => $this->ARR
    ]);
  }

  // get validation errors
  public function errors($type)
  {
    // TODO: loop keys and return validation checks
    return [];
  }

  // check if config source is valid
  public function isValid($key)
  {
    // return false if key is not set
    if (!isset($this->configSource[$key])) return false;

    // return false if key pair is empty
    if (empty($this->configSource[$key])) return false;

    // continue
    return true;
  }

  /**
   * - build action sets from each config setting.
   */
  public function build()
  {
    $this->configSchema
      ->addOrigins($this->isValid('origins')?$this->configSource['origins']:[])
      ->addSupport($this->isValid('supports')?$this->configSource['supports']:[])
      ->addTypes(array_merge(
        $this->isValid('types')?$this->configSource['types']:[],
        Utils::GetConfig(Utils::GetThemeSrc().'/types')
      ))
      ->addBlocks(array_merge(
        $this->isValid('blocks')?$this->configSource['blocks']:[],
        Utils::GetEachConfig(Utils::GetThemeSrc().'/blocks')
      ))
      ->addSettings(array_merge(
        $this->isValid('settings')?$this->configSource['settings']:[],
        Utils::GetConfig(Utils::GetThemeSrc().'/settings')
      ))
      ->addOptions(array_merge(
        $this->isValid('options')?$this->configSource['options']:[],
        Utils::GetConfig(Utils::GetThemeSrc().'/options')
      ))
      ->addFields(array_merge(
        $this->isValid('fields')?$this->configSource['fields']:[],
        Utils::GetEachConfig(Utils::GetThemeSrc().'/fields')
      ))
      ->addPages(array_merge(
        $this->isValid('pages')?$this->configSource['pages']:[],
        Utils::GetConfig(Utils::GetThemeSrc().'/pages')
      ))
      ->addStyles($this->isValid('styles')?$this->configSource['styles']:[])
      ->addScripts($this->isValid('scripts')?$this->configSource['scripts']:[])
      ->compileBlockCategories($this->overrides('hide_block_categories'));
  }

  public function overrides($category)
  {
    $overrides = $this->isValid('overrides')?$this->configSource['overrides']:[];
    if (isset($overrides[$category])){
      return $overrides[$category];
    }
    else
    {
      return false;
    }
  }

  /**
   * - export action set to php file.
   */
  public function compile()
  {
    $output = [
      "<?php ".$this->EOL,
      "if(!defined('ABSPATH'))exit;".$this->EOL,
    ];
    foreach($this->configSchema->hooks as $hook=>$actions)
    {
      $output[] = "add_action('".$hook."',function(){".$this->EOL;
      foreach($actions as $action)
      {
        $output[] = $action.$this->EOL;
      };
      $output[] = "});".$this->EOL;
    };
    return join('', $output);
  }
}