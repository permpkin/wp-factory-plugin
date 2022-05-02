#!/bin/sh

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

if [ ! -f "public/wp-config.php" ]; then
echo "${BLUE}WP Config → Building...$NC";
## wp config variable definitions
WP_DEFINE_LIST=(
  "DB_NAME=getenv(\"MYSQL_DATABASE\")"
  "DB_USER=getenv(\"MYSQL_USER\")"
  "DB_PASSWORD=getenv(\"MYSQL_PASSWORD\")"
  "DB_HOST=empty(getenv(\"MYSQL_HOST\"))?\"mysql\":getenv(\"MYSQL_HOST\")"
  "DB_CHARSET=\"utf8mb4\""
  "WP_REDIS_BACKEND_HOST=empty(getenv(\"WP_REDIS_BACKEND_HOST\"))?\"redis\":getenv(\"WP_REDIS_BACKEND_HOST\")"
  "WP_REDIS_BACKEND_PORT=empty(getenv(\"WP_REDIS_BACKEND_PORT\"))?6379:getenv(\"WP_REDIS_BACKEND_PORT\")"
  "WP_REDIS_BACKEND_DB=empty(getenv(\"WP_REDIS_BACKEND_DB\"))?0:getenv(\"WP_REDIS_BACKEND_DB\")"
);

WP_MULTISITE=""
read -r -p "WP Config → Add Multisite Settings? [y/N]" WP_MULTISITE

# create wp-config
cat <<EOF > public/wp-config.php
<?php
/**
* Do not edit this file. Edit the config files found in the config/ dir instead.
* This file is required in the root directory so WordPress can find it.
*/
\$table_prefix = getenv(\"MYSQL_TABLE_PREFIX\");
EOF

echo "${BLUE}WP Config → importing wordpress salts...$NC";

# generate wp-config.php with salts
WPSaltInput=$(curl -s https://api.wordpress.org/secret-key/1.1/salt/)
WPSaltGen="$(echo "${WPSaltInput}" | tr -d '[:space:]')"
cat <<EOF >> public/wp-config.php
// Wordpress Salts:
// https://api.wordpress.org/secret-key/1.1/salt/
$WPSaltGen
EOF

cat <<EOF >> public/wp-config.php
// Enable Debugging
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define('DISABLE_WP_CRON', true);
define('SAVEQUERIES', true);
EOF

## Append debug flags
if [ "$WP_MULTISITE" == "y" ]; then
WP_DEFINE_LIST+=(
  "MULTISITE"
  "SUBDOMAIN_INSTALL"
  "DOMAIN_CURRENT_SITE"
  "PATH_CURRENT_SITE"
  "SITE_ID_CURRENT_SITE"
  "BLOG_ID_CURRENT_SITE"
  "WP_ALLOW_MULTISITE"
)
fi

echo "// Common Params" >> public/wp-config.php
for value in "${WP_DEFINE_LIST[@]}"
do
  if [[ $value == *"="* ]]; then
    valueArray=(${value//\=/ })
    echo "define(\"${valueArray[0]}\",${valueArray[1]});" >> public/wp-config.php
  else
    echo "define(\"$value\",getenv(\"$value\"));" >> public/wp-config.php
  fi
done

cat <<EOF >> public/wp-config.php
define('DISALLOW_FILE_EDIT', true);
// Reference Params
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');
define('WP_CONTENT_FOLDERNAME', 'wp-content');
define('WP_CONTENT_DIR', dirname(__FILE__) . '/' . WP_CONTENT_FOLDERNAME);
define('WP_HOME', isset(\$_SERVER['HTTPS']) && !empty(\$_SERVER['HTTPS']) ? 'https' : 'http' . '://' . \$_SERVER['HTTP_HOST'] );
define('WP_SITEURL', WP_HOME . '/wp');
define('WP_CONTENT_URL', WP_HOME . '/' . WP_CONTENT_FOLDERNAME);
define("THEME_NAME", "$THEME_NAME");
define("WP_DEFAULT_THEME", "$THEME_NAME");
require_once(ABSPATH . 'wp-settings.php');
EOF
echo "${GREEN}WP Config → ✅$NC"
fi; # End WP Config

#
# Save WP Utils for Factory.
#
if [ ! -f "public/wp-content/mu-plugins/factory-utils.php" ]; then
echo "${BLUE}Saving Factory Utils$NC"
mkdir -p public/wp-content/mu-plugins
curl -o public/wp-content/mu-plugins/factory-utils.php https://gist.githubusercontent.com/permpkin/53cf60dd16491e9c1795b5ca08d5ce3e/raw/fbfdc022d0cf6b7f99ee43661a1719dea7654c0a/factory-utils.php 2>/dev/null
echo "${GREEN}Saved Factory Utils$NC"
fi;

#
# Save Valet Driver for Factory.
#
if [ ! -f "$HOME/.config/valet/drivers/WordpressFactoryValetDriver.php" ]; then
echo "${BLUE}Saving Factory Valet Driver$NC"
mkdir -p "$HOME/.config/valet/drivers/"
curl -o "$HOME/.config/valet/drivers/WordpressFactoryValetDriver.php" "https://gist.githubusercontent.com/permpkin/f1c0434796c3c9230f39a1704637a3f4/raw/d09524bc99067b50644abcd4311c0a9d4b169123/WordpressFactoryValetDriver.php" 2>/dev/null
echo "${GREEN}Saved Factory Valet Driver$NC"
fi;
