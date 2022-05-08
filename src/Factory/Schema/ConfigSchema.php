<?php

namespace Factory\Schema;

use Factory\Utils;

class ConfigSchema {
  
  public $EOL = "";
  public $ARS = "[";
  public $ARN = "]";

  public $hooks = [];
  public $segmentCache = [];
  public $blockCategories = [];
  public $schemaMap = [];

  function __construct($args=[])
  {
    if(!empty($args['EOL']))
    {
      $this->EOL = $args['EOL'];
    }
    if(!empty($args['ARR']))
    {
      if (sizeof($args['ARR']) !== 2)
      {
        throw new Error('INVALID_ARR_VAL');
      }
      $this->ARS = $args['ARR'][0];
      $this->ARN = $args['ARR'][1];
    }
  }

  /**
   * Check if hook is set, if not register hook.
   * @param hook wordpress hook identifier
   * @param method stringified function
   */
  public function addHook($hook, $method)
  {
    if (!isset($this->hooks[$hook]))
    {
      $this->hooks[$hook] = [];
    }
    $this->hooks[$hook][] = strval($method);
  }

  /**
   * Check if segment is set, if not add segment.
   * @param segment segment key
   * @param arr segment array
   */
  public function addSegmentCache($segment, $arr)
  {
    if (!isset($this->segmentCache[$segment]))
    {
      $this->segmentCache[$segment] = [];
    }
    $this->segmentCache[$segment][] = strval($arr);
  }

  /**
   * Get segment cache (or empty array if not set).
   * @param segment segment array
   */
  public function getSegmentCache($segment)
  {
    return (!isset($this->segmentCache[$segment]))?[]:$this->segmentCache[$segment];
  }

  /**
   * Inject code into either front, back or both head hooks.
   * @param inserts
   * @param code
   */
  public function injectScriptHooks($insert, $code)
  {
    switch ($insert)
    {
      case 'front':
        $this->addHook('wp_enqueue_scripts', $code);
      break;
      case 'back':
        $this->addHook('admin_enqueue_scripts', $code);
      break;
      case 'both':
        $this->addHook('admin_enqueue_scripts', $code);
        $this->addHook('wp_enqueue_scripts', $code);
      break;
    };
    return $this;
  }

  /**
   * Merge/Filter folder config.
   */
  public function runConfigFilter($key, $args)
  {
    // merge config fields with scanned directory config files.
    $options = array_merge($args, Utils::GetConfig(THEME_SRC.'/'.$key), $this->getSegmentCache($key));

    if (empty($options)) return [];

    return $options;
  }
  
  /**
   * Register allowed origins to filter.
   * @param args array of origin domains
   */
  public function addSettings($args)
  {
    if (empty($args)) return $this;

    SettingsSchema::compile($this, $args);

    return $this;
  }

  /**
   * Register css dependencies to header
   * @param args array of style registers
   */
  public function addStyles($args)
  {
    if (empty($args)) return $this;
    
    StyleSchema::compile($this, $args);

    return $this;
  }

  /**
   * Register javascript dependencies to header/footer
   * @param args array of script registers
   */
  public function addScripts($args)
  {
    if (empty($args)) return $this;
    
    ScriptSchema::compile($this, $args);

    return $this;
  }

  public function addFields($args)
  {
    if (empty($args)) return $this;

    CustomFieldSchema::compile($this, $args);
    
    return $this;
  }

  /**
   * WP Custom Post Types
   */
  public function addTypes($args)
  {
    if (empty($args)) return $this;

    PostTypeSchema::compile($this, $args);
    
    return $this;
  }

  /**
   * WP Custom Post Types
   */
  public function addTaxonomies($args)
  {
    if (empty($args)) return $this;

    TaxonomySchema::compile($this, $args);
    
    return $this;
  }

  /**
   * WP Custom "Options" Pages
   */
  public function addOptions($args)//, $method='acf_add_options_page')
  {
    if (empty($args)) return $this;

    OptionSchema::compile($this, $args);

    return $this;
  }

  /**
   * WP Gutenburg Blocks
   */
  public function addBlocks($args)
  {
    if (empty($args)) return $this;

    BlockSchema::compile($this, $args);

    return $this;
  }

  /**
   * WP Gutenburg Block Categories
   */
  public function addBlockCategory($args)
  {
    if (empty($args)) return $this;

    if (!in_array((is_array($args)?$args['slug']:$args), $this->blockCategories))
      $this->blockCategories[] = [$args];
    
    return $this;
  }

  public function compileBlockCategories($overrides=false)
  {
    if (empty($this->blockCategories)) return $this;
    
    $code = 'add_filter(\'block_categories_all\',function($categories,$post){';

    // check for override filters
    if (is_array($overrides))
    {
      foreach($overrides as $filter)
      {
        $code .= 'unset($categories[\''.$filter.'\']);';
      }
    }
    else if ($overrides === "*")
    {
      $code .= '$categories=[];';
    }

    $code .= 'return array_merge(';
    $code .= '$categories,';
    $code .= $this->ARS;
    $code_cache = [];
    foreach ($this->blockCategories as $category)
    {
      if (!is_array($category))
      {
        $code_cache[] = join("",[
          ($this->ARS),
          ('\'slug\'=>\''.(Utils::StringToSlug($category)).'\','),
          ('\'title\'=>\''.$category.'\''),
          ($this->ARN)
        ]);
      }
      else 
      {
        $code_cache[] = Utils::ArrayToString($category);
      }
    }
    $code .= join(',', $code_cache);
    $code .= $this->ARN;
    $code .= ');';

    $code .= '},10,2);';

    $this->addHook('init', $code);

    return $this;
  }

  /**
   * WP Admin Pages
   */
  public function addPages($args)
  {
    if (empty($args)) return $this;

    CustomPageSchema::compile($this, $args);

    return $this;
  }

  /**
   * WP Customizer settings
   */
  public function addCustomise($args)
  {
    if (empty($args)) return $this;

    CustomiseSchema::compile($this, $args);

    return $this;
  }

  /**
   * WP API Endpoints
   */
  public function addEndpoints($args)
  {
    if (empty($args)) return $this;

    EndpointSchema::compile($this, $args);

    return $this;
  }

}