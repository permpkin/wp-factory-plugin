<?php

namespace Factory;

class Dependency {

  static function install() {
    echo "Grabbing WP-Factory dependencies.";
    // Factory Utils
    Dependency::getAndPutFile("https://gist.github.com/53cf60dd16491e9c1795b5ca08d5ce3e.git", "public/wp-content/mu-plugins/", "factory-utils.php");
    // Construct "wp-config.php" for local.
    exec("/bin/sh ".__DIR__."/build-wp-config.sh");
  }

  static function getAndPutFile($fileUrl, $path, $filename) {
    if (!file_exists($path.$filename)) {
      $file = file_get_contents($fileUrl);
      if (!mkdir($path, 0777, true)) {
        echo "couldn't create path";
      };
      $filePath = fopen($path.$filename, 'w');
      fwrite($filePath, $file);
      fclose($filePath);
    }
  }

}