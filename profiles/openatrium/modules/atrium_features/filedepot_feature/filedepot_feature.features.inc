<?php

/**
 * Implementation of hook_context_default_contexts().
 */
function filedepot_feature_context_default_contexts() {
  module_load_include('inc', 'filedepot_feature', 'filedepot_feature.defaults');
  $args = func_get_args();
  return call_user_func_array('_filedepot_feature_context_default_contexts', $args);
}

/**
 * Implementation of hook_ctools_plugin_api().
 */
function filedepot_feature_ctools_plugin_api() {
  module_load_include('inc', 'filedepot_feature', 'filedepot_feature.defaults');
  $args = func_get_args();
  return call_user_func_array('_filedepot_feature_ctools_plugin_api', $args);
}

/**
 * Implementation of hook_menu_default_items().
 */
function filedepot_feature_menu_default_items() {
  module_load_include('inc', 'filedepot_feature', 'filedepot_feature.defaults');
  $args = func_get_args();
  return call_user_func_array('_filedepot_feature_menu_default_items', $args);
}

/**
 * Implementation of hook_strongarm().
 */
function filedepot_feature_strongarm() {
  module_load_include('inc', 'filedepot_feature', 'filedepot_feature.defaults');
  $args = func_get_args();
  return call_user_func_array('_filedepot_feature_strongarm', $args);
}
