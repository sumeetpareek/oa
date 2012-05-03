<?php

/**
 * Helper to implementation of hook_context_default_contexts().
 */
function _filedepot_feature_context_default_contexts() {
  $export = array();
  $context = new stdClass;
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'filedepot';
  $context->description = '';
  $context->tag = 'filedepot';
  $context->conditions = array();
  $context->reactions = array();
  $context->condition_mode = 0;

  $translatables['filedepot'] = array(
    t('filedepot'),
  );

  $export['filedepot'] = $context;
  return $export;
}

/**
 * Helper to implementation of hook_ctools_plugin_api().
 */
function _filedepot_feature_ctools_plugin_api() {
  $args = func_get_args();
  $module = array_shift($args);
  $api = array_shift($args);
  if ($module == "context" && $api == "context") {
    return array("version" => 3);
  }
  else if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => 1);
  }
}

/**
 * Helper to implementation of hook_menu_default_items().
 */
function _filedepot_feature_menu_default_items() {
  $items = array();

  $items[] = array(
    'title' => 'Documents',
    'path' => 'filedepot',
    'weight' => '0',
  );
  // Translatables
  array(
    t('Documents'),
  );


  return $items;
}

/**
 * Helper to implementation of hook_strongarm().
 */
function _filedepot_feature_strongarm() {
  $export = array();
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_allow_folder_duplicates';
  $strongarm->value = 1;

  $export['filedepot_allow_folder_duplicates'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_allow_broadcasts';
  $strongarm->value = 1;

  $export['filedepot_default_allow_broadcasts'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_notify_filechange';
  $strongarm->value = 0;

  $export['filedepot_default_notify_filechange'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_notify_newfile';
  $strongarm->value = 0;

  $export['filedepot_default_notify_newfile'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_owner';
  $strongarm->value = 'view, admin, upload_dir, upload_ver, approval';

  $export['filedepot_default_owner'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_perms_data';
  $strongarm->value = 'a:2:{s:5:"owner";a:5:{i:0;s:4:"view";i:1;s:5:"admin";i:2;s:10:"upload_dir";i:3;s:10:"upload_ver";i:4;s:8:"approval";}s:18:"authenticated user";a:2:{i:0;s:4:"view";i:1;s:6:"upload";}}';

  $export['filedepot_default_perms_data'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_default_roles';
  $strongarm->value = 'authenticated user: view, upload';

  $export['filedepot_default_roles'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_extension_data';
  $strongarm->value = 'a:13:{s:3:"jpg";s:7:"jpg.gif";s:3:"gif";s:7:"gif.gif";s:3:"bmp";s:7:"bmp.gif";s:3:"doc";s:7:"doc.gif";s:4:"docx";s:7:"doc.gif";s:3:"xls";s:7:"xls.gif";s:4:"xlsx";s:7:"xls.gif";s:3:"ppt";s:7:"ppt.gif";s:4:"pptx";s:7:"ppt.gif";s:3:"mpp";s:7:"mpp.gif";s:3:"pdf";s:7:"pdf.gif";s:3:"txt";s:7:"txt.gif";s:3:"zip";s:7:"zip.gif";}';

  $export['filedepot_extension_data'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_filetype_filter';
  $strongarm->value = '';

  $export['filedepot_filetype_filter'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_filetype_filterdata';
  $strongarm->value = 'a:0:{}';

  $export['filedepot_filetype_filterdata'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_filter_mode';
  $strongarm->value = '0';

  $export['filedepot_filter_mode'] = $strongarm;
  $strongarm = new stdClass;
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'filedepot_notifications_enabled';
  $strongarm->value = 1;

  $export['filedepot_notifications_enabled'] = $strongarm;
  return $export;
}
