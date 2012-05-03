<?php

/**
 * @file
 * lib-test.php
 * Test code that will only be used to create test folders and files for load testing
 */

/* Defines that are only used for testing - creating test folders and files for load testing */
/* Execute test feature to create data via {site_url}/index.php?q=filedepot_createtestrecords */

global $_validfolders, $_numfolders2create, $_numfiles2create, $_maxrecordsperfolder;

// Array of valid folders - Seed with one or more initial parent folder ids.
// will get populated with new folders as script runs so result is a random collection
$_validfolders = array();      // Comma seperated list of parent folders to use as initial seeds. If empty a new top level folder will be created.
$_numfolders2create = 25;      // Number of new folders to create
$_numfiles2create = 250;       // Number of files to create
$_maxrecordsperfolder = 20;    // Max new file records per folder, set lower to spread file population among folders
/* End of test related defines */


function filedepot_createtestrecords() {
  global $parent_folders, $folders_created, $max_records_perfolder;
  global $_numfolders2create, $_numfiles2create, $_maxrecordsperfolder, $_validfolders;

  if (empty($_validfolders) OR count($_validfolders) == 0) {
    $_validfolders[] = filedepottest_createfolder(0, 'Load Testing');
  }

  $folders_created = array();
  if ($_numfolders2create > 0) {
    for ($i = 1; $i <= $_numfolders2create; $i++) {
      $parent_folders = implode(',', $_validfolders);
      $pid = db_result(db_query_range("SELECT cid from {filedepot_categories} WHERE cid in ($parent_folders) ORDER BY RAND()", array(), 0, 1));
      $cnt = db_result(db_query("SELECT count(cid) FROM {filedepot_categories} WHERE cid=%d", $pid));
      if ($cnt != 1) {
        watchdog('filedepot', "create_testrecords abort, cid: @cid does not exist", array('@cid' => $pid));
        echo "create_testrecords abort, cid: {$pid} does not exist";
        die();
      }
      $cid = filedepottest_createfolder($pid);
      $_validfolders[] = $cid;
    }
  }
  drupal_set_message(t('Created !numfolders new folders', array('!numfolders' => $_numfolders2create)), 'status');

  $parent_folders = implode(',', $_validfolders);

  for ($filenum = 1; $filenum <= $_numfiles2create; $filenum++) {
    // Select a random folder with less then max number of files
    $cid = filedepottest_selectRandomFolder();
    $sql  = "INSERT INTO {filedepot_files} (cid,fname,title,version,description,mimetype,extension,submitter,status,date) ";
    $sql .= "VALUES (%d,'testfile.pdf','TestFile-%s','1','Phantom file created for stress testing','application/octet-stream','pdf',2,1,%d)";
    db_query($sql, $cid, $filenum, time());
    $lastrec = db_result(db_query_range("SELECT fid FROM {filedepot_files} ORDER BY fid DESC", array(), 0, 1));
    $sql = "INSERT INTO {filedepot_fileversions} (fid,fname,version,notes,date,uid,status) VALUES (%d,'%s','1','',%d,2,'1')";
    db_query($sql, $lastrec, 'testfile.pdf', time());
    $_foldersCreated[$cid]++;
  }

  drupal_set_message(t('Completed adding !numfiles new files', array('!numfiles' => $filenum)), 'status');
  drupal_goto();

}


function filedepottest_createfolder($pid, $foldername='') {
  global $user, $_foldersCreated;
  $filedepot = filedepot_filedepot();

  $node = (object) array(
  'uid' => $user->uid,
  'name' => $user->name,
  'type' => 'filedepot_folder',
  'title' => "folder - pid$pid",
  'parentfolder' => $pid,
  'folderdesc'  => '',
  'inherit'     => 1
  );

  node_save($node);

  $newcid = $filedepot->cid;
  $_foldersCreated[$newcid] = 0;   // Track the number of files created in this folder
  if (empty($foldername)) $foldername = "folder({$newcid}) - pid$pid";
  db_query("UPDATE {filedepot_categories} SET name = '%s' where cid=%d", $foldername, $newcid);
  $filedepot->updatePerms($newcid, $filedepot->defOwnerRights, $user->uid);
  if (isset($filedepot->defRoleRights) AND count($filedepot->defRoleRights) > 0) {
    foreach ($filedepot->defRoleRights as $role => $perms) {
      $rid = db_result(db_query("SELECT rid FROM {role} WHERE name='%s'", $role));
      if ($rid and $rid > 0) {
        $filedepot->updatePerms($newcid, $perms, '', array($rid));
      }
    }
  }
  return $newcid;
}

function filedepottest_selectRandomFolder($tries=0) {
  global $parent_folders, $_foldersCreated, $_maxrecordsperfolder;

  if ($tries > 100) return 0; // only try 100 times and then abort

  $cid = db_result(db_query_range("SELECT cid from {filedepot_categories} WHERE pid in ($parent_folders) ORDER BY RAND()", array(), 0, 1));

  if ($cid > 0 AND $_foldersCreated[$cid] >= $_maxrecordsperfolder) {
    watchdog('filedepot', "Max records @maxrecords for folder: @cid", array('@maxrecords' => $_maxrecordsperfolder, '@cid' => $cid));
    $tries++;
    $cid = filedepottest_selectRandomFolder($tries);
    if ($cid == 0) {
      drupal_set_message('error', 'create_testrecords: abort. Max attempts to find a folder with less then @maxrecordsperfolder exceeded.',
          array('@maxrecordsperfolder' => $_maxrecordsperfolder));
      die;
    }
  }
  return $cid;
}