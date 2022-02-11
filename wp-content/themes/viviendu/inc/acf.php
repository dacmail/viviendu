<?php

//Save JSON when ACF is saved
add_filter('acf/settings/save_json', function ($path) {
  $path = get_stylesheet_directory() . '/acf-json';
  return $path;
}, 10, 1);

//Load JSON when ACF is initialized
add_filter('acf/settings/load_json', function ($paths) {
  unset($paths[0]);
  $paths[] = get_stylesheet_directory() . '/acf-json';
  return $paths;
}, 10, 1);
