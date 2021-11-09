DB_HOST=localhost
DB_USER=admin
DB_PASS=Pass123#

WP_USER=admin
WP_PASS=Pass123#
WP_EMAIL=admin@example.com

WP_URL=""
WP_TITLE=""
WP_Installation_PATH=""
WP_DB=""

# a function
# require an argument list
function arg_parser() {

  for i in "$@"; do
    case $i in
      -dh=*|--db-host=*)
        DB_HOST="${i#*=}"
        shift # past argument=value
        ;;
      -du=*|--db-user=*)
        DB_USER="${i#*=}"
        shift # past argument=value
        ;;
      -dp=*|--db-pass=*)
        DB_PASS="${i#*=}"
        shift # past argument=value
        ;;
      -db=*|--db-name=*)
        WP_DB="${i#*=}"
        shift # past argument=value
        ;;
      -u=*|--user=*)
        WP_USER="${i#*=}"
        shift # past argument=value
        ;;
      -p=*|--pass=*)
        WP_PASS="${i#*=}"
        shift # past argument=value
        ;;
      -e=*|--email=*)
        WP_EMAIL="${i#*=}"
        shift # past argument=value
        ;;
      -b=*|--base-url=*)
        WP_URL="${i#*=}"
        shift # past argument=value
        ;;
      -t=*|--title=*)
        WP_TITLE="${i#*=}"
        shift # past argument=value
        ;;
      -i=*|--path=*)
        WP_Installation_PATH="${i#*=}"
        shift # past argument=value
        ;;
      -l=*|--plugin=*)
        PLUGIN_LIST="${i#*=}"
        shift # past argument=value
        ;;
      --default)
        DEFAULT=YES
        shift # past argument with no value
        ;;
      *)
        # unknown option
        ;;
    esac
  done
}

function download_wp() {

  # Download WordPress core
  echo "Downloading WordPress at $WP_Installation_PATH"
  wp core download --path=$WP_Installation_PATH

  echo 'Setting folder permission to 0777'
  echo "Letmein123@" | sudo -S chmod -R 0777 $WP_Installation_PATH

  echo 'Setting file ownership to www-data:www-data'
  echo "Letmein123@" | sudo -S chown www-data:www-data -R $WP_Installation_PATH
}

function setup_db() {

  echo "Creating database $WP_DB for user \"$DB_USER\" with pass \"$DB_PASS\" at host \"$DB_HOST\"";
  mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $WP_DB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci"
}

function install_wp() {

  # Change to newly installed WP
  echo "Changing directory to $WP_Installation_PATH"
  cd $WP_Installation_PATH

  echo "Creating WP config file"
  wp config create --dbname=$WP_DB --dbuser=$DB_USER --dbpass=$DB_PASS --dbhost=$DB_HOST
  # Remove sample config file
  rm wp-config-sample.php

  echo "Setting WP config values"
  wp config set DB_COLLATE utf8mb4_unicode_520_ci
  wp config set WP_DEBUG true --raw
  wp config set WP_DEBUG_DISPLAY false --raw
  wp config set WP_DEBUG_LOG true --raw

  echo "Setting new WP salts"
  wp config shuffle-salts

  # Install WordPress
  echo "Installing WordPress"
  wp core install --url=$WP_URL --title=$WP_TITLE --admin_user=$WP_USER --admin_password=$WP_PASS --admin_email=$WP_EMAIL

  wp option set timezone_string "America/Toronto"

  echo "WordPress installed $(wp core version)"
}

# function call
# $* passes the argument of the file to the function to parse
arg_parser $*

# Validation
[[
  -z "$DB_HOST" 
  ||
  -z "$DB_USER" 
  ||
  -z "$DB_PASS" 
  ||
  -z "$WP_USER" 
  ||
  -z "$WP_PASS" 
  ||
  -z "$WP_EMAIL" 
  ||
  -z "$WP_URL" 
  ||
  -z "$WP_TITLE" 
  ||
  -z "$WP_Installation_PATH" 
  ||
  -z "$WP_DB" ]] && {
    
  echo " "
  echo "DB_HOST = $DB_HOST, DB_USER = $DB_USER, DB_PASS = $DB_PASS, WP_USER = $WP_USER, WP_PASS = $WP_PASS, WP_EMAIL = $WP_EMAIL, WP_URL = $WP_URL, WP_TITLE = $WP_TITLE, WP_Installation_PATH = $WP_Installation_PATH, WP_DB = $WP_DB, PLUGIN_LIST = $PLUGIN_LIST"
  echo " "
  echo "Manual"
  echo " "
  echo 'fresh-wp [--option=value/-o="value"]'
  echo " "
  echo "--db-host, -dh"
  echo "    required, Database Host connection string"
  echo " "
  echo "--db-user, -du"
  echo "    required, Database Username for the connection"
  echo " "
  echo "--db-pass, -dp"
  echo "    required, Database Password for user to connect"
  echo " "
  echo "--db-name, -db"
  echo "    required, WP Database name"
  echo " "
  echo "--user, -u"
  echo "    required, WP admin username"
  echo " "
  echo "--pass, -p"
  echo "    required, WP admin password"
  echo " "
  echo "--email, -e"
  echo "    required, WP admin email"
  echo " "
  echo "--base-url, -b"
  echo "    required, WP base URL, like http://localhost/new-wp"
  echo " "
  echo "--title, -t"
  echo "    required, WP Title"
  echo " "
  echo "--path, -i"
  echo "    required, Physical installation path"
  exit 1;
}

# function call
download_wp

# function call
setup_db

# function call
install_wp