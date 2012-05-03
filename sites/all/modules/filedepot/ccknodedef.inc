<?php

/**
 * @file
 * ccknodedef.inc
 * CCK Import logic that is only called after install once - when creating the first folder (node)
 * Need to extend the Content Type definition as defined by filedepot__node_info() to
 * include the CCK filefield file. Not able to do so in the HOOK_node_info
 * and not able to do so in the install routine as the filedepot module has not yet defined the content_type.
 */
  
function _filedepot_cck_export() {

$content['type']  = array(
  'name' => 'Filedepot Folder',
  'type' => 'filedepot_folder',
  'description' => 'Folder for storing documents',
  'title_label' => 'Folder Name',
  'body_label' => 'Folder Description',
  'min_word_count' => 0,
  'help' => '',
  'node_options' => 
  array(
    'status' => TRUE,
    'promote' => TRUE,
    'sticky' => FALSE,
    'revision' => FALSE,
  ),
  'old_type' => 'filedepot_folder',
  'orig_type' => 'filedepot_folder',
  'module' => 'filedepot',
  'custom' => FALSE,
  'modified' => FALSE,
  'locked' => FALSE,
  'reset' => 'Reset to defaults',
  'comment' => 2,
  'comment_default_mode' => 4,
  'comment_default_order' => 1,
  'comment_default_per_page' => 50,
  'comment_controls' => 3,
  'comment_anonymous' => 0,
  'comment_subject_field' => 1,
  'comment_preview' => 1,
  'comment_form_location' => 0,
);
$content['fields']  = array(
  0 => 
  array(
    'label' => 'Folder File',
    'field_name' => 'field_filedepot_file',
    'type' => 'filefield',
    'widget_type' => 'filefield_widget',
    'change' => 'Change basic information',
    'weight' => '31',
    'file_extensions' => '',
    'progress_indicator' => 'bar',
    'file_path' => '',
    'max_filesize_per_file' => '',
    'max_filesize_per_node' => '',
    'description' => '',
    'required' => 0,
    'multiple' => '1',
    'list_field' => '1',
    'list_default' => 1,
    'description_field' => '0',
    'op' => 'Save field settings',
    'module' => 'filefield',
    'widget_module' => 'filefield',
    'columns' => 
    array(
      'fid' => 
      array(
        'type' => 'int',
        'not null' => FALSE,
        'views' => TRUE,
      ),
      'list' => 
      array(
        'type' => 'int',
        'size' => 'tiny',
        'not null' => FALSE,
        'views' => TRUE,
      ),
      'data' => 
      array(
        'type' => 'text',
        'serialize' => TRUE,
        'views' => TRUE,
      ),
    ),
    'display_settings' => 
    array(
      'label' => 
      array(
        'format' => 'above',
        'exclude' => 0,
      ),
      'teaser' => 
      array(
        'format' => 'default',
        'exclude' => 0,
      ),
      'full' => 
      array(
        'format' => 'default',
        'exclude' => 0,
      ),
      4 => 
      array(
        'format' => 'default',
        'exclude' => 0,
      ),
    ),
  ),
);
$content['extra']  = array(
  'title' => '-5',
  'body_field' => '0',
  'revision_information' => '20',
  'comment_settings' => '30',
  'menu' => '-2',
);


return $content;

}


function filedepot_install_cck_filefield() {

  // CCK content_copy.module may not be enabled, so make sure it is included
  require_once './' . drupal_get_path('module', 'content') .  '/modules/content_copy/content_copy.module';

  /* get the CCK node types to be created. This is where you load the 
  * file containing your function from above, if necessary, and then call
  * that function.
  */
  $content = _filedepot_cck_export();
  $form_state['values']['type_name'] = 'filedepot_folder';
  $form_state['values']['macro'] = '$content = ' . var_export($content, TRUE) . ';';
  // form provided by content_copy.module 
  drupal_execute('content_copy_import_form', $form_state);
  content_clear_type_cache();

  watchdog('filedepot', 'Completed adding CCK filefield to the Folder Content Type');

}

