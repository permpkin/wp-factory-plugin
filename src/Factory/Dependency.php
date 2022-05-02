<?php

namespace Factory;

class Dependency {

  static function install() {
    exec("/bin/sh ".__DIR__."/install.sh");
  }

}