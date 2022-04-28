<?php

require_once '../../autoload.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Request;
use Pecee\Http\Response;
use Factory\Config;

/**
 * @return \Pecee\Http\Request
 */
function request(): Request
{
    return SimpleRouter::request();
}

/**
 * Get input class
 * @param string|null $index Parameter index name
 * @param string|mixed|null $defaultValue Default return value
 * @param array ...$methods Default methods
 * @return \Pecee\Http\Input\InputHandler|array|string|null
 */
function input($index = null, $defaultValue = null, ...$methods)
{
    if ($index !== null) {
        return request()->getInputHandler()->value($index, $defaultValue, ...$methods);
    }

    return request()->getInputHandler();
}

SimpleRouter::group(['prefix' => '/wp/factory/api/config'], function () {
  
  // return current config file.
  SimpleRouter::get('/', [Config::class, 'get']);
  
  // update config file.
  SimpleRouter::put('/', [Config::class, 'update']);

  // replace config file.
  SimpleRouter::post('/', [Config::class, 'replace']);
  
  // return schema type settings.
  SimpleRouter::get('/schema/{type}', [Config::class, 'schema']);
  
  // validate specified schema type
  SimpleRouter::get('/validate/{type}', [Config::class, 'validate']);

  // validate entire json against all schema types.
  SimpleRouter::get('/validate', [Config::class, 'validate']);

  // compile config to php.
  SimpleRouter::post('/deploy', [Config::class, 'deploy']);

  // check missing fields not present in config or non-deployed blocks.
  SimpleRouter::post('/verify', [Config::class, 'verify']);

});

SimpleRouter::setDefaultNamespace('\Factory');

SimpleRouter::start();
