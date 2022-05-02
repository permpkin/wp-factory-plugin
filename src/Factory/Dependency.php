<?php

namespace Factory;

class Dependency {

  static function install() {
    echo "Grabbing WP-Factory dependencies.";
    Dependency::getAndPutFile("https://gist.github.com/53cf60dd16491e9c1795b5ca08d5ce3e.git", "public/wp-content/mu-plugins/", "factory-utils.php");
  }

  static function getAndPutFile($fileUrl, $path, $filename) {
    $file = file_get_contents($fileUrl);
    mkdir($path, 0777, true);
    $filePath = fopen($path.$filename, 'w');
    fwrite($filePath, $file);
    fclose($filePath);
  }

}