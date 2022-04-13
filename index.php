<?php

require_once 'vendor/autoload.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Response;
use Factory\Config;

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
  SimpleRouter::get('/deploy', [Config::class, 'deploy']);

  // check missing fields not present in config or non-deployed blocks.
  SimpleRouter::get('/verify', [Config::class, 'verify']);

});

SimpleRouter::setDefaultNamespace('\Factory');

SimpleRouter::start();