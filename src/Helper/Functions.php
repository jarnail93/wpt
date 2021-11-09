<?php

namespace Jarnail\Wpt\Helper;

class Functions
{
  public static function form_re_pop($should_repop)
  {
    return function($field, $default) use ($should_repop) {
      return $should_repop ? $_POST[$field] : $default;
    };
  }
  public static function join_paths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
  }
}