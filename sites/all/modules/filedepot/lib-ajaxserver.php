<?php

/**
 * @file
 * lib-ajaxserver.php
 * Library functions for the ajax_server
 */


function filedepotAjaxServer_getfilelisting() {
  global $user;
  $filedepot = filedepot_filedepot();

  if (empty($filedepot->activeview)) {
    $filedepot->ajaxBackgroundMode = TRUE;
  }
  elseif ($filedepot->cid == 0 AND !in_array($filedepot->activeview, $filedepot->validReportingModes)) {
    $filedepot->activeview = 'latestfiles';
    $filedepot->ajaxBackgroundMode = FALSE;
  }

  if (db_result(db_query("SELECT COUNT(*) FROM {filedepot_categories} WHERE cid=%d", $filedepot->cid)) == 0) {
    $filedepot->cid = 0;
  }

  if ($filedepot->activeview == 'notifications') {
    $data['cid'] = $filedepot->cid;
    $data['retcode'] = 200;
    $data['cid'] = $filedepot->cid;
    $data['activefolder'] = theme('filedepot_activefolder');
    $data['displayhtml'] = theme('filedepot_notifications');
    $data['header'] = theme('filedepot_header');
    $data['moreactions'] = filedepotAjaxServer_getMoreActions($filedepot->activeview);
  }
  elseif ($filedepot->cid > 0 AND $filedepot->checkPermission($filedepot->cid, 'view')) {
    $data['retcode'] = 200;
    $data['cid'] = $filedepot->cid;
    $foldercount = db_result(db_query("SELECT count(cid) FROM {filedepot_categories} WHERE cid=%d", $filedepot->cid));
    if (user_is_logged_in() AND $filedepot->cid > 0 AND $foldercount == 1) {
      $pid = db_result(db_query("SELECT pid FROM {filedepot_categories} WHERE cid=%d", $filedepot->cid));
      if ($pid > 0) {
        $count1 = db_result(db_query("SELECT count(cid) FROM {filedepot_recentfolders} WHERE uid=%d", $user->uid));
        if ($count1 > 4) {
          db_query("DELETE FROM {filedepot_recentfolders} WHERE uid=%d ORDER BY id ASC LIMIT %d", $user->uid, $count1 - 4);
        }
        $count2 = db_result(db_query("SELECT count(cid) FROM {filedepot_recentfolders} WHERE uid=%d AND cid=%d", $user->uid, $filedepot->cid));
        if ($count2 == 0) {
          db_query("INSERT INTO {filedepot_recentfolders} (uid,cid) VALUES (%d,%d)", $user->uid, $filedepot->cid);
        }
      }
    }
    $data['displayhtml'] = filedepot_displayFolderListing($filedepot->cid);
    if (is_array($filedepot->lastRenderedFiles) AND count($filedepot->lastRenderedFiles) > 0) {
      $data['lastrenderedfiles'] = json_encode($filedepot->lastRenderedFiles);
    }
    firelogmsg("Completed generating FileListing");
    $data['activefolder'] = theme('filedepot_activefolder');
    $data['moreactions'] = filedepotAjaxServer_getMoreActions($filedepot->activeview);
    $data['header'] = theme('filedepot_header');

  }
  elseif ($filedepot->cid == 0) {
    $data['retcode'] = 200;
    $data['cid'] = $filedepot->cid;
    $data['displayhtml'] = filedepot_displayFolderListing($filedepot->cid);
    $data['activefolder'] = theme('filedepot_activefolder');
    $data['moreactions'] = filedepotAjaxServer_getMoreActions($filedepot->activeview);
    $data['header'] = theme('filedepot_header');

  }
  else {
    $data['retcode'] = 401;
    $data['error'] = 'Error: No Access to Folder';
  }

  //firelogmsg("Completed generating Header return data");
  return $data;

}


/* Generate Left Side Navigation code which is used to create the YUI menu's in the AJAX handler javascript */
function filedepotAjaxServer_generateLeftSideNavigation($data='') {
  global $user;
  $filedepot = filedepot_filedepot();

  if (empty($data))
    $data = array('retcode' => 200);

  $approvals = filedepot_getSubmissionCnt();

  $data['reports'] = array();
  $data['topfolders'] = array();
  $data['reports'][] = array(
  'name' => t('Latest Files'),
  'link' => "reportmode=latestfiles",
  'parent' => 'allitems',
  'icon' => 'icon-filelisting');
  if (user_is_logged_in()) {
    $data['reports'][] = array(
    'name' => t('Notifications'),
    'link' => "reportmode=notifications",
    'parent' => 'allitems',
    'icon' => 'icon-fileowned');
    $data['reports'][] = array(
    'name' => t('Owned by me'),
    'link' => "reportmode=myfiles",
    'parent' => 'allitems',
    'icon' => 'icon-fileowned');
    $data['reports'][] = array(
    'name' => t('Downloaded by me'),
    'link' => "reportmode=downloads",
    'parent' => 'allitems',
    'icon' => 'icon-fileowned');
    $data['reports'][] = array(
    'name' => t('Unread Files'),
    'link' => "reportmode=unread",
    'parent' => 'allitems',
    'icon' => 'icon-fileowned');
    $data['reports'][] = array(
    'name' => t('Locked by me'),
    'link' => "reportmode=lockedfiles",
    'parent' => 'allitems',
    'icon' => 'icon-filelocked');
    $data['reports'][] = array(
    'name' => t('Flagged by me'),
    'link' => "reportmode=flaggedfiles",
    'parent' => 'allitems',
    'icon' => 'icon-fileflagged');
  }
  if ($approvals > 0) {
    $approvals = "&nbsp;($approvals)";
    $data['reports'][] = array(
    'name' => t('Waiting approval') . "$approvals",
    'link' => "reportmode=approvals",
    'parent' => 'allitems',
    'icon' => 'icon-fileowned');
  }

  if (user_is_logged_in()) {
    if (user_access('administer filedepot', $user)) {
      $res = db_query("SELECT COUNT(id) as incoming FROM {filedepot_import_queue}"); ;
    }
    else {
      $res = db_query("SELECT COUNT(id) as incoming FROM {filedepot_import_queue} WHERE uid=%d", $user->uid);
    }
    $A = db_fetch_array($res);

    if ($A['incoming'] > 0) {
      $incoming_msg = "&nbsp;({$A['incoming']})";
      $data['reports'][] = array(
      'name' => t('Incoming Files') . "$incoming_msg",
      'link' => "reportmode=incoming",
      'parent' => 'allitems',
      'icon' => 'icon-fileowned');
    }
  }

  // Setup the Most Recent folders for this user
  if (user_is_logged_in()) {
    $sql  = "SELECT a.id,a.cid,b.name FROM {filedepot_recentfolders} a ";
    $sql .= "LEFT JOIN {filedepot_categories} b ON b.cid=a.cid ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
      $sql .= "WHERE a.cid in ({$filedepot->allowableGroupViewFoldersSql}) AND a.cid != {$filedepot->ogrootfolder} AND b.pid != {$filedepot->ogrootfolder} ";
    } else {
      $sql .= "WHERE 1=1 ";
    }
    $sql .= "AND uid=%d ORDER BY id";
    $res = db_query($sql, $user->uid);
    while ($A = db_fetch_array($res)) {
      $data['recentfolders'][] = array(
      'name' => filter_xss($A['name']),
      'link' => "cid={$A['cid']}",
      'icon' => 'icon-allfolders');
    }

  }

  $sql = "SELECT cid,pid,name,description from {filedepot_categories} ";
  if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
    $sql .= "WHERE pid={$filedepot->ogrootfolder} ORDER BY folderorder";
  } else {
    $sql .= "WHERE pid=0 ORDER BY folderorder";
  }
  $res = db_query($sql);
  while ($A = db_fetch_array($res)) {
    if ($filedepot->checkPermission($A['cid'], 'view')) {
      $data['topfolders'][] = array(
      'name' => filter_xss($A['name']),
      'link' => "cid={$A['cid']}",
      'parent' => 'allfolders',
      'icon' => 'icon-allfolders');
    }
  }

  if (function_exists(filedepot_customLeftsideNavigation)) {
    $data = filedepot_customLeftsideNavigation($data);
  }

  return $data;

}


/* Recursive Function to display folder listing */
function filedepot_displayFolderListing($id=0, $level=0, $folderprefix='', $rowid=1) {
  $filedepot = filedepot_filedepot();

  $retval = '';
  if ($id > 0 AND !in_array($id, $filedepot->allowableViewFolders)) {
    watchdog('filedepot', 'No view access to category @id', array('id' => $id));
    return;
  }

  if (empty($folderprefix)) {
    $q1 = db_query("SELECT cid,pid,folderorder FROM {filedepot_categories} WHERE cid=%d", $id);
    $rec = db_fetch_object($q1);
    if ($rec->pid != 0) {
      $folderprefix = $rec->folderorder / 10;
      while ($rec->pid != 0) {
        $q2 = db_query("SELECT cid,pid,folderorder FROM {filedepot_categories} WHERE cid=%d", $rec->pid);
        $rec = db_fetch_object($q2);
        if ($rec->pid == 0) break;
        $folderprefix = $rec->folderorder / 10 . '.' . $folderprefix;
      }
    }
  }

  if (empty($folderprefix)) $folderprefix = 0;

  $level++;
  if ($level == 1) {
    $retval .= nexdocsrv_generateFileListing($id, $level, $folderprefix);
  }

  $sql = '';
  if (function_exists('filedepot_customReportFoldersSQL')) {
    $sql = trim(filedepot_customReportFoldersSQL($id, $reportmode));
  }

  if ($id > 0 OR !empty($sql)) {
    // Show any subfolders and check and see if this is a custom report

    if (empty($sql)) {
      $sql  = "SELECT DISTINCT cid,pid,name,description,folderorder,last_modified_date FROM {filedepot_categories} WHERE pid=%d ";
      if (!empty($filedepot->allowableViewFoldersSql)) {
        $sql .= "AND cid in ({$filedepot->allowableViewFoldersSql}) ";
      }
      $sql .= "ORDER BY folderorder";
    }
    $qfolders = db_query($sql, $id);
    $output = '';
    $i = $rowid;
    $maxfolderorder = db_result(db_query_range("SELECT folderorder FROM {filedepot_categories} WHERE pid=%d ORDER BY folderorder DESC", array($id), 0, 1));
    while ($A = db_fetch_array($qfolders)) {
      if (empty($folderprefix)) {
        $formatted_foldernumber = $i;
      }
      else {
        $formatted_foldernumber = "{$folderprefix}.{$i}";
      }
      $subfolderlisting = nexdocsrv_generateFileListing($A['cid'], $level, $formatted_foldernumber);
      $subfolder_count = db_result(db_query("SELECT count(cid) FROM {filedepot_categories} WHERE pid=%d", $A['cid']));
      if ($subfolder_count > 0) {
        // Show any sub-subfolders - calling this function again recursively
        $subfolderlisting .= filedepot_displayFolderListing($A['cid'], $level, $formatted_foldernumber, $rowid);
      }
      $retval .= theme('filedepot_folderlisting', $A, $formatted_foldernumber, $level, $subfolderlisting, $maxfolderorder);
      $i++;
    }
    if (empty($output) AND $level == 1) {
      $retval .= "<div id=\"subfolder{$GLOBALS['lastRenderedFiles'][0][0]}_rec{$GLOBALS['lastRenderedFiles'][0][1]}_bottom\">";
    }
  }

  return $retval;
}



function nexdocsrv_generateFileListing($cid, $level=1, $folderprefix='') {
  $filedepot = filedepot_filedepot();

  $filedepot->selectedTopLevelFolder = $cid;
  $files = array();
  $sql = filedepot_getFileListingSQL($cid);
  $file_query = db_query($sql);
  $output = '';
  $break = FALSE;
  if (empty($folderprefix)) {
    $q1 = db_query("SELECT cid,pid,folderorder FROM {filedepot_categories} WHERE cid=%d", $cid);
    $rec = db_fetch_object($q1);
    if ($rec->pid != 0) {
      $folderprefix = $rec->folderorder / 10;
      while ($rec->pid != 0) {
        $q2 = db_query("SELECT cid,pid,folderorder FROM {filedepot_categories} WHERE cid=%d", $rec->pid);
        $rec2 = db_fetch_object($q2);
        if ($rec2->pid == 0) break;
        $folderprefix = $rec2->folderorder / 10 . '.' . $folderprefix;
      }
    }
  }
  $i = 0;
  while ( $A = db_fetch_array($file_query)) {
    if ($filedepot->activeview == 'approvals') {
      $A['fid'] = $A['id'];
    }
    if (!in_array($fid, $files)) {
      $i++;
      $more_records_message = '';
      if ($filedepot->ajaxBackgroundMode == TRUE AND $i >= $filedepot->recordCountPass1) {
        $break = TRUE;
        $filedepot->lastRenderedFiles[] = array($cid, $A['fid'], $folderprefix, $level);
        $more_records_message = 'moredata_msg';
      }
      elseif ($filedepot->activeview == 'getmoredata'  AND $i >= $filedepot->recordCountPass2) {
        $break = TRUE;
        // Check if there are more records - the SQL LIMIT statement allowed for one more record
        // If there are more - show the AJAX link to load more data - pass 2
        if (db_fetch_array($file_query)) {
          $more_records_message = 'loadfolder_msg';
        }
      }
      if ($break) {
        $output .= theme('filedepot_filelisting', $A, $folderprefix, $level, $more_records_message);
        break;
      }
      else {
        $output .= theme('filedepot_filelisting', $A, $folderprefix, $level);
        $files[] = $A['fid'];
      }
    }
  }
  return $output;
}

function filedepot_displaySearchListing($query) {
  $filedepot = filedepot_filedepot();

  $query = addslashes($query);
  $sql = 'SELECT file.fid as fid,file.cid,file.title,file.fname,file.date,file.version,file.submitter,file.status,';
  $sql .= 'file.description,category.name as foldername,category.pid,category.nid ';
  $sql .= 'FROM {filedepot_files} file ';
  $sql .= 'LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ';
  $sql .= 'WHERE 1=1 ';
  if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
    $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
  } elseif (!empty($filedepot->allowableViewFoldersSql)) {
    $sql .= "AND file.cid in ({$filedepot->allowableViewFoldersSql}) ";
  }
  $sql .= 'AND (file.title LIKE "%%%s%%%" OR file.title LIKE "%%%s%%%" OR file.description LIKE "%%%s%%%" OR file.description LIKE "%%%s%%%") ';
  $sql .= 'ORDER BY file.date DESC ';

  $search_query = db_query($sql, $query, $query, $query, $query);
  $output = '';
  while ( $A = db_fetch_array($search_query)) {
    $output .= theme('filedepot_filelisting', $A);
  }
  return $output;

}

function filedepot_displayTagSearchListing($query) {
  $filedepot = filedepot_filedepot();
  $nexcloud =  filedepot_nexcloud();
  $sql = "SELECT file.fid as fid,file.cid,file.title,file.fname,file.date,file.version,file.submitter,file.status,";
  $sql .= "file.description,category.name as foldername,category.pid,category.nid ";
  $sql .= "FROM {filedepot_files} file ";
  $sql .= "LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ";
  $sql .= "WHERE 1=1 ";
  if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
    $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
  } elseif (!empty($filedepot->allowableViewFoldersSql)) {
    $sql .= "AND file.cid in ({$filedepot->allowableViewFoldersSql}) ";
  }

  $itemids = $nexcloud->search($query);
  if ($itemids !== FALSE) $itemids = implode(',', $itemids);
  if (!empty($itemids)) {
    $sql .= "AND file.fid in ($itemids) ";
  }
  else {
    $sql .= "AND 1 = 2 ";    // No tags match query - return 0 records
  }
  $sql .= "ORDER BY file.date DESC ";

  $search_query = db_query($sql);
  $output = '';
  while ( $A = db_fetch_array($search_query)) {
    $output .= theme('filedepot_filelisting', $A);
  }
  return $output;

}

function filedepot_getFileListingSQL($cid) {
  global $user;
  $filedepot = filedepot_filedepot();

  $sql = '';
  // Check and see if this is a custom report
  if (function_exists('filedepot_customReportFilesSQL')) {
    $sql = trim(filedepot_customReportFilesSQL($cid, $filedepot->activeview));
    if (!empty($sql)) return $sql;
  }

  $sql = "SELECT file.fid as fid,file.cid,file.title,file.fname,file.date,file.version,file.submitter,file.status,";
  $sql .= "file.description,category.name as foldername,category.pid,category.nid,category.last_modified_date,status_changedby_uid as changedby_uid, size ";
  $sql .= "FROM {filedepot_files} file ";
  $sql .= "LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ";
  if ($filedepot->activeview == 'lockedfiles') {
    $sql .= "WHERE file.status=2 AND status_changedby_uid={$user->uid} ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    }
    $sql .= "ORDER BY date DESC LIMIT {$filedepot->maxDefaultRecords}";
  }
  elseif ($filedepot->activeview == 'downloads') {
    // Will return multiple records for same file as we capture download records each time a user downloads it
    $sql .= "LEFT JOIN {filedepot_downloads} downloads on downloads.fid=file.fid ";
    $sql .= "WHERE uid={$user->uid} ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    }
    $sql .= "ORDER BY file.date DESC LIMIT {$filedepot->maxDefaultRecords}";
  }
  elseif ($filedepot->activeview == 'unread') {
    $sql .= "LEFT OUTER JOIN {filedepot_downloads} downloads on downloads.fid=file.fid ";
    $sql .= "WHERE downloads.fid IS NULL ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    } elseif (empty($filedepot->allowableViewFoldersSql)) {
      $sql .= "AND file.cid is NULL ";
    }
    else {
      $sql .= "AND file.cid in ({$filedepot->allowableViewFoldersSql}) ";
    }
    $sql .= "ORDER BY file.date DESC LIMIT {$filedepot->maxDefaultRecords}";

  }
  elseif ($filedepot->activeview == 'incoming') {
    $sql = "SELECT id as fid, 0 as cid, orig_filename as title,  queue_filename as fname, timestamp as date, 0 as version, ";
    $sql .= "uid as submitter, 0 as status, 'N/A' as description, 'Incoming Files' as name, 0 as pid, 0 as changedby_uid, size ";
    $sql .= "FROM {filedepot_import_queue} ";
    if (!user_access('administer filedepot', $user)) {
      $sql .= "WHERE uid={$user->uid} ";
    }
    $sql .= "ORDER BY date DESC ";

  }
  elseif ($filedepot->activeview == 'flaggedfiles') {
    $sql .= "LEFT JOIN {filedepot_favorites} favorites on favorites.fid=file.fid ";
    $sql .= "WHERE uid={$user->uid} ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    }
  }
  elseif ($filedepot->activeview == 'myfiles') {
    $sql .= "WHERE file.submitter={$user->uid} ";
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    }
    $sql .= "ORDER BY date DESC LIMIT {$filedepot->maxDefaultRecords}";
  }
  elseif ($filedepot->activeview == 'approvals') {
    // Determine if this user has any submitted files that they can approve
    $sql = "SELECT file.id,file.cid,file.title,file.fname,file.date,file.version,file.submitter,file.status,";
    $sql .= "file.description,category.name as foldername,category.pid,0 as changedby_uid, size ";
    $sql .= "FROM {filedepot_filesubmissions} file ";
    $sql .= "LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ";
    if (!user_access('administer filedepot', $user)) {
      $categories = $filedepot->getAllowableCategories(array('approval', 'admin'));
      if (empty($categories)) {
        $sql .= "WHERE file.cid is NULL ";
      }
      else {
        $sql .= "WHERE file.cid in ($categories) ";
      }
      if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "AND file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
      }
    }
    $sql .= "ORDER BY file.date DESC ";

  }
  elseif ($cid > 0) {
    $sql .= "WHERE file.cid={$cid} ORDER BY file.date DESC, file.fid DESC ";
    if ($filedepot->activeview == 'getmorefolderdata') {
      if (isset($_POST['pass2']) AND $_POST['pass2'] == 1) {
        if ($GLOBALS['db_type'] == 'pgsql') {
          $sql .= "LIMIT 100000 OFFSET {$filedepot->recordCountPass1}";
        }
        else {
          $sql .= "LIMIT {$filedepot->recordCountPass1}, 100000 ";
        }
      }
      else {
        $recordoffset = $filedepot->recordCountPass2 + $filedepot->recordCountPass1;
        $filedepot->folder_filenumoffset = $recordoffset;
        if ($GLOBALS['db_type'] == 'pgsql') {
          $sql .= "LIMIT 100000 OFFSET {$recordoffset}";
        }
        else {
          $sql .= "LIMIT {$recordoffset}, 100000 ";
        }
      }
    }
    elseif ($filedepot->activeview != 'getallfiles') {
      // Set SQL query options for amount of data to return - used by the AJAX routine getmorefiledata to populate display in the background
      if ($filedepot->lastRenderedFolder == $cid) {
        $filedepot->folder_filenumoffset = $filedepot->recordCountPass1;
        $folder_filelimit = $filedepot->recordCountPass2 + 1;
        if ($GLOBALS['db_type'] == 'pgsql') {
          $sql .= "LIMIT $folder_filelimit OFFSET {$filedepot->recordCountPass1} ";
        }
        else {
          $sql .= "LIMIT {$filedepot->recordCountPass1}, $folder_filelimit ";
        }
      }
      else {
        if ($GLOBALS['db_type'] == 'pgsql') {
          $sql .= "LIMIT $filedepot->recordCountPass1 OFFSET 0 ";
        }
        else {
          $sql .= "LIMIT 0, $filedepot->recordCountPass1 ";
        }
      }
    }

  }
  else {
    // Default view - latestfile
    if ($filedepot->ogmode_enabled AND !empty($filedepot->allowableGroupViewFoldersSql)) {
        $sql .= "WHERE file.cid in ({$filedepot->allowableGroupViewFoldersSql}) ";
    } elseif (!user_access('administer filedepot', $user)) {
      if (empty($filedepot->allowableViewFoldersSql)) {
        $sql .= "WHERE file.cid is NULL ";
      }
      else {
        $sql .= "WHERE file.cid in ({$filedepot->allowableViewFoldersSql}) ";
      }
    }
    $sql .= "ORDER BY file.date DESC LIMIT {$filedepot->maxDefaultRecords}";
  }

  return $sql;

}



function filedepotAjaxServer_loadFileDetails() {
  global $user;

  $filedepot = filedepot_filedepot();
  $nexcloud =  filedepot_nexcloud();
  $reportmode = check_plain($_POST['reportmode']);
  $retval = array();
  $retval['editperm'] = FALSE;
  $retval['deleteperm'] = FALSE;
  $retval['addperm'] = FALSE;
  $retval['lockperm'] = FALSE;
  $retval['notifyperm'] = FALSE;
  $retval['broadcastperm'] = FALSE;
  $retval['tags'] = '';

  $validfile = FALSE;
  if ($reportmode == 'approvals') {
    $id = intval($_POST['id']);
    if (db_result(db_query("SELECT count(*) FROM {filedepot_filesubmissions} WHERE id=%d", $id)) == 1) {
      $validfile = TRUE;
      $sql = "SELECT file.id as fid,file.cid,file.title,file.fname,file.date,file.size,file.version,file.submitter,file.tags,u.name, ";
      $sql .= "file.status,file.description,category.pid,category.name as folder,category.nid,file.version_note,tags ";
      $sql .= "FROM {filedepot_filesubmissions} file ";
      $sql .= "LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ";
      $sql .= "LEFT JOIN {users} u ON u.uid=file.submitter ";
      $sql .= "WHERE file.id=%d ";
      $rec = db_fetch_array(db_query($sql, $id));
      $retval = array_merge($retval, $rec);
      $retval['displayhtml'] = theme('filedepot_filedetail', $id, $reportmode);
      $retval['locked'] = FALSE;
      $retval['subscribed'] = FALSE;
    }

  }
  elseif ($reportmode == 'incoming') {
    $id = intval($_POST['id']);
    if (db_result(db_query("SELECT count(*) FROM {filedepot_import_queue} WHERE id=%d", $id)) == 1) {
      $validfile = TRUE;
      $sql = "SELECT file.id as fid,file.orig_filename as title,file.description,file.version_note,u.name ";
      $sql .= "FROM {filedepot_import_queue} file ";
      $sql .= "LEFT JOIN {users} u ON u.uid=file.uid ";
      $sql .= "WHERE file.id=%d ";
      $rec = db_fetch_array(db_query($sql, $id));
      $retval = array_merge($retval, $rec);
      if (empty($retval['version_note'])) $retval['version_note'] = '';
      $retval['displayhtml'] = theme('filedepot_filedetail', $id, $reportmode);
      $retval['locked'] = FALSE;
      $retval['subscribed'] = FALSE;
      // Need to reference a valid filedepot_folder node for the filedepot_download callback to work - required for the File Details 'Download' menuitem
      $retval['nid'] = db_result(db_query_range("SELECT nid FROM {filedepot_categories} WHERE pid=0", array(), 0, 1));
    }

  }
  else {
    // Check that record exists
    $fid = intval($_POST['id']);
    $cid = db_result(db_query("SELECT cid FROM {filedepot_files} WHERE fid=%d", $fid));
    if ($filedepot->checkPermission($cid, 'view') AND db_result(db_query("SELECT count(*) FROM {filedepot_files} WHERE fid=%d", $fid)) == 1) {
      $validfile = TRUE;

      $sql = "SELECT file.fid,file.cid,file.title,file.description,file.fname,file.date,file.size,file.version,file.submitter,u.name, ";
      $sql .= "file.status,category.pid,category.name as folder,category.nid,v.notes as version_note,file.status_changedby_uid ";
      $sql .= "FROM {filedepot_files} file ";
      $sql .= "LEFT JOIN {filedepot_categories} category ON file.cid=category.cid ";
      $sql .= "LEFT JOIN {filedepot_fileversions} v ON v.fid=file.fid ";
      $sql .= "LEFT JOIN {users} u ON u.uid=file.submitter ";
      $sql .= "WHERE file.fid=%d ORDER BY v.version DESC";
      $rec = db_fetch_array(db_query($sql, $fid));
      $retval = array_merge($retval, $rec);

      $retval['tags'] = $nexcloud->get_itemtags($fid);
      $retval['displayhtml'] = theme('filedepot_filedetail', $fid, $reportmode);

      // Check if file is locked
      if (($retval['status']) == FILEDEPOT_LOCKED_STATUS) {
        $retval['locked'] = TRUE;
      }
      else {
        $retval['locked'] = FALSE;
      }

      // Check and see if user has subscribed to this file
      $direct = FALSE;
      $ignorefilechanges = FALSE;
      // Check if user has an ignore file changes record or a subscribe to changes record for this file
      $query = db_query("SELECT fid,ignore_filechanges FROM {filedepot_notifications} WHERE fid=%d and uid=%d", $fid, $user->uid);
      $A = db_fetch_array($query);
      if ($A['ignore_filechanges'] == 1) {
        $ignorefilechanges = TRUE;
      }
      elseif ($A['fid'] == $fid) {
        $direct = TRUE;
      }
      // Check and see if user has indirectly subscribed to file by subscribing to folder
      $sql = "SELECT count(*) FROM {filedepot_notifications} WHERE cid_changes=1 AND cid=%d AND uid=%d";
      $indirect = db_result(db_query($sql, $rec['cid'], $user->uid));
      if (($direct or $indirect) AND !$ignorefilechanges) {
        $retval['subscribed'] = TRUE;
      }
      else {
        $retval['subscribed'] = FALSE;
      }
    }
  }

  if ($validfile) {
    $retval['error'] = '';
    $retval['retcode'] = 200;
    if ($reportmode == 'incoming') {
      $retval['downloadperm'] = TRUE;
      $retval['editperm'] = TRUE;
      $retval['deleteperm'] = TRUE;
      $retval['addperm'] = FALSE;
      $retval['lockperm'] = FALSE;
      $retval['notifyperm'] = FALSE;
      $retval['broadcastperm'] = FALSE;
      $folderoptions = filedepot_recursiveAccessOptions('admin', 0);
      $retval['folderoptions'] = '<select name="folder" style="width:220px;">' . $folderoptions . '</select>';
    }
    else {
      $retval['dispfolder'] = $retval['folder'];
      $retval['description'] = nl2br($retval['description']);
      $retval['version_note'] = nl2br($retval['version_note']);
      $retval['date'] = strftime('%b %d %Y %I:%M %p', $retval['date']);
      $retval['size'] = filedepot_formatFileSize($retval['size']);

      // Setup the folder option select HTML options
      $cid = intval($retval['cid']);
      $folderoptions = filedepot_recursiveAccessOptions('admin', $cid, 0, 1, FALSE);
      if (!empty($folderoptions) AND $filedepot->checkPermission($retval['cid'], 'admin')) {
        $retval['folderoptions'] = '<select name="folder" style="width:220px;">' . $folderoptions . '</select>';
      }
      else {
        $retval['folderoptions'] = '<input type="text" name="folder" value="' . $retval['folder'] . '" READONLY />';
      }
      if ($filedepot->checkPermission($retval['cid'], 'admin')) {
        $retval['downloadperm'] = TRUE;
        $retval['editperm'] = TRUE;
        $retval['deleteperm'] = TRUE;
        $retval['addperm'] = TRUE;
        $retval['lockperm'] = TRUE;
        $retval['notifyperm'] = TRUE;
        $retval['broadcastperm'] = TRUE;
      }
      elseif ($retval['locked']) {
        if ($retval['status_changedby_uid'] == $user->uid) {
          $retval['lockperm'] = TRUE;
          if ($filedepot->checkPermission($retval['cid'], 'upload_ver')) {
            $retval['addperm'] = TRUE;
          }
          if ($retval['submitter'] == $user->uid) {
            $retval['deleteperm'] = TRUE;
          }
        }
        elseif ($retval['status_changedby_uid'] > 0) {
          if ($retval['submitter'] == $user->uid) {
            $retval['lockperm'] = TRUE;
          }
          else {
            $retval['downloadperm'] = FALSE;
          }
        }
        $retval['notifyperm'] = TRUE;
      }
      elseif ($user->uid > 0) {
        if ($retval['submitter'] == $user->uid) {
          $retval['deleteperm'] = TRUE;
          $retval['lockperm'] = TRUE;
        }
        if ($filedepot->checkPermission($retval['cid'], 'upload_ver')) {
          $retval['addperm'] = TRUE;
        }
        $retval['notifyperm'] = TRUE;
      }
      if ($filedepot->checkPermission($retval['cid'], 'view', 0, FALSE)) {
        $retval['tagperms'] = TRUE;   // Able to set or change tags
        if ($retval['locked']) {
          if ($retval['submitter'] == $user->uid OR $retval['status_changedby_uid'] == $user->uid) {
            $retval['downloadperm'] = TRUE;
          }
          elseif (variable_get('filedepot_locked_file_download_enabled', 0) == 1) {  // Check admin config setting
            $retval['downloadperm'] = TRUE;
          }
          else {
            $retval['downloadperm'] = FALSE;
          }
        }
        else {
          $retval['downloadperm'] = TRUE;
          if ($retval['submitter'] == $user->uid) {
            $retval['editperm'] = TRUE;
          }
        }
      }
      else {
        $retval['tagperms'] = FALSE;
        $retval['downloadperm'] = FALSE;
      }

    }

  }
  else {
    $retval['retcode'] = 400;
    $retval['error'] = t('Invalid access');
  }

  return $retval;
}


function filedepotAjaxServer_getMoreActions($op) {
  $retval = '<option value="0">' . t('More Actions') . '...</option>';
  switch ($op) {
    case 'approvals':
      $retval .= '<option value="approvesubmissions">' . t('Approve selected Submissions') . '</option>';
      $retval .= '<option value="deletesubmissions">' . t('Delete selected Submissions') . '</option>';
      break;
    case 'incoming':
      $retval .= '<option value="delete">' . t('Delete selected files') . '</option>';
      $retval .= '<option value="move">' . t('Move selected files') . '</option>';
      break;
    case 'notifications':
      $retval .= '<option value="delete">' . t('Delete selected Notifications') . '</option>';
      break;
    default:
      if (!user_is_logged_in()) {
        // $retval .= '<option value="archive">'. t ('Download as an archive') . '</option>';
      }
      else {
        $retval .= '<option value="delete">' . t('Delete selected files') . '</option>';
        $retval .= '<option value="move">' . t('Move selected files') . '</option>';
        $retval .= '<option value="subscribe">' . t('Subscribe to update notifications') . '</option>';
        // $retval .= '<option value="archive">' . t('Download as an archive') . '</option>';
        $retval .= '<option value="markfavorite">' . t('Mark Favorite') . '</option>';
        $retval .= '<option value="clearfavorite">' . t('Clear Favorite') . '</option>';
      }
      break;

  }
  return $retval;
}

function filedepotAjaxServer_deleteCheckedFiles() {
  global $user;
  $filedepot = filedepot_filedepot();

  $retval = array();

  $cid = intval($_POST['cid']);
  $reportmode = check_plain($_POST['reportmode']);
  $fileitems = check_plain($_POST['checkeditems']);
  $files = explode(',', $fileitems);
  $delerror = FALSE;

  if (!empty($_POST['checkedfolders'])) {
    $folderitems = check_plain($_POST['checkedfolders']);
    $folders = explode(',', $folderitems);
    foreach ($folders as $id) {
      if ($reportmode == 'notifications') {
        if ($id > 0 AND db_result(db_query("SELECT uid FROM {filedepot_notifications} WHERE id=%d", $id)) > 0) {
          db_query("DELETE FROM {filedepot_notifications} WHERE id=%d", $id);
        }
      }
      elseif ($id > 0 AND $_POST['multiaction'] == 'delete' AND $filedepot->checkPermission($id, 'admin')) {
        $nid = db_result(db_query("SELECT nid FROM {filedepot_categories} WHERE cid=%d", $id));
        if ($filedepot->deleteFolder($nid)) {
          // Remove any recent folder records for this category
          db_query("DELETE FROM {filedepot_recentfolders} WHERE cid=%d", $id);
        }
        else {
          $delerror = TRUE;
        }
      }
    }
  }

  if ($reportmode == 'incoming') {
    foreach ($files as $id) {
      if (db_result(db_query("SELECT COUNT(*) FROM {filedepot_import_queue} WHERE id=%d", $id)) == 1) {
        $query = db_query("SELECT cckfid,filepath,uid FROM {filedepot_import_queue} WHERE id=%d", $id);
        $file = db_fetch_object($query);
        if ($file->uid == $user->uid OR user_access('administer filedepot', $user)) {
          if (!empty($file->filepath) AND file_exists($file->filepath)) {
            @unlink($file->filepath);
          }
          db_query("DELETE FROM {files} WHERE fid=%d", $file->cckfid);
          db_query("DELETE FROM {filedepot_import_queue} WHERE id=%d", $id);
        }
      }
    }
  }
  elseif ($reportmode == 'notifications') {
    foreach ($files as $id) {
      $uid = db_result(db_query("SELECT uid FROM {filedepot_notifications} WHERE id=%d", $id));
      if ($id > 0 AND  $uid == $user->uid) {
        db_query("DELETE FROM {filedepot_notifications} WHERE id=%d", $id);
      }
    }
  }
  else {
    foreach ($files as $id) {
      if ($id > 0 ) {
        if ($filedepot->deleteFile($id) === FALSE) {
          $delerror = TRUE;
        }
      }
    }
  }

  if (!in_array($reportmode, $filedepot->validReportingModes))  $filedepot->ajaxBackgroundMode = TRUE;
  $filedepot->cid = $cid;
  $filedepot->activeview = $reportmode;
  $retval['retcode'] = 200;
  $retval['errmsg'] = '';
  if ($reportmode == 'notifications') {
    $retval['displayhtml'] = theme('filedepot_notifications');
  }
  else {
    $retval['displayhtml'] = filedepot_displayFolderListing($filedepot->cid);
  }
  $retval['activefolder'] = theme('filedepot_activefolder');
  if ($delerror == TRUE) {
    $retval['errmsg'] = t('Error deleting one or more items - invalid permissions');
  }

  if (is_array($filedepot->lastRenderedFiles) AND count($filedepot->lastRenderedFiles) > 0) {
    $retval['lastrenderedfiles'] = json_encode($filedepot->lastRenderedFiles);
  }
  return $retval;
}

function filedepotAjaxServer_deleteFile($fid) {
  $filedepot = filedepot_filedepot();
  $retval = array();
  $reportmode = check_plain($_POST['reportmode']);
  $listing_folder = intval($_POST['listingcid']);
  $filedepot->cid = $listing_folder;
  $filedepot->activeview = $reportmode;

  $retval['fid'] = $fid;
  if ($reportmode == 'approvals') {
    $retval['cid'] = db_result(db_query("SELECT cid FROM {filedepot_filesubmissions} WHERE id=%d", $fid));
  }
  elseif ($reportmode == 'incoming') {
    $cckfid = db_result(db_query("SELECT cckfid FROM {filedepot_import_queue} WHERE id=%d", $fid));
  }
  else {
    $retval['cid'] = db_result(db_query("SELECT cid FROM {filedepot_files} WHERE fid=%d", $fid));
  }
  $message = '';
  if ($reportmode == 'approvals' AND $filedepot->checkPermission($retval['cid'], 'approval')) {
    if ($filedepot->deleteSubmission($fid)) {
      $retval['retcode'] = 200;
      $message = '<div class="pluginInfo aligncenter" style="width:100%;height:60px;padding-top:30px;">';
      $message .= t('File was sucessfully deleted. This message will clear in a couple seconds');
      $message .= '</div>';
      $retval['displayhtml'] = filedepot_displayFolderListing($listing_folder);
    }
    else {
      $retval['retcode'] = 500;
    }
  }
  elseif ($reportmode == 'incoming') {
    if ( $cckfid > 0) {
      $filepath = db_result(db_query("SELECT filepath FROM {files} WHERE fid=%d", $cckfid));
      if (!empty($filepath) AND file_exists($filepath)) {
        @unlink($filepath);
      }
      db_query("DELETE FROM {files} WHERE fid=%d", $cckfid);
      db_query("DELETE FROM {filedepot_import_queue} WHERE id=%d", $fid);
      $retval['retcode'] = 200;
      $filedepot->activeview = 'incoming';
      $retval['displayhtml'] = filedepot_displayFolderListing();
      $retval = filedepotAjaxServer_generateLeftSideNavigation($retval);
    }
    else {
      $retval['retcode'] = 500;
    }
  }
  elseif ($filedepot->deleteFile($fid)) {   /* Includes security tests that user can delete this file */
    if (!in_array($reportmode, $filedepot->validReportingModes))  $filedepot->ajaxBackgroundMode = TRUE;
    $retval['retcode'] = 200;
    $message = '<div class="pluginInfo aligncenter" style="height:60px;padding-top:30px;">';
    $message .= t('File was sucessfully deleted. This message will clear in a couple seconds');
    $message .= '</div>';
    $retval['displayhtml'] = filedepot_displayFolderListing($listing_folder);
    if (is_array($filedepot->lastRenderedFiles) AND count($filedepot->lastRenderedFiles) > 0) {
      $retval['lastrenderedfiles'] = json_encode($filedepot->lastRenderedFiles);
    }
  }
  else {
    $retval['retcode'] = 404;
  }

  $retval['message'] = $message;
  $retval['title'] = t('Delete Confirmation');
  return $retval;

}


function filedepotAjaxServer_updateFolder() {
  global $user;
  $filedepot    = filedepot_filedepot();
  $cid          = intval($_POST['cid']);
  $catpid       = intval($_POST['catpid']);
  $folderorder  = intval($_POST['folderorder']);
  $fileadded    = intval($_POST['fileadded_notify']);
  $filechanged  = intval($_POST['filechanged_notify']);
  $catname      = check_plain($_POST['categoryname']);
  $catdesc      = check_plain($_POST['catdesc']);

  $retval = array();

  if ($cid > 0 AND $filedepot->checkPermission($cid, 'admin')) {
    $retval['retcode'] =  200;
    $retval['cid'] = $cid;
    db_query("UPDATE {filedepot_categories} SET name='%s', description='%s' WHERE cid=%d", $catname, $catdesc, $cid);
    $nid = db_result(db_query("SELECT nid FROM {filedepot_categories} WHERE cid=%d", $cid));
    db_query("UPDATE {node} SET title='%s' WHERE nid=%d", $catname, $nid);
    db_query("UPDATE {node_revisions} SET title='%s' WHERE nid=%d", $catname, $nid);
    if (db_result(db_query("SELECT folderorder FROM {filedepot_categories} WHERE cid=%d", $cid)) != $folderorder) {
      db_query("UPDATE {filedepot_categories} SET folderorder=%d WHERE cid=%d", $folderorder, $cid);
      /* Re-order any folders that may have just been moved */
      $query = db_query("SELECT cid,folderorder from {filedepot_categories} WHERE pid=%d ORDER BY folderorder", $catpid);
      $folderorder = 10;
      $stepnumber = 10;
      while ( $A = db_fetch_array($query)) {
        if ($A['folderorder'] != $folderorder) {
          DB_query("UPDATE {filedepot_categories} SET folderorder=%d WHERE cid=%d", $folderorder, $A['cid']);
        }
        $folderorder += $stepnumber;
      }
    }

    // Update the personal folder notifications for user
    if ($filechanged == 1 OR $fileadded == 1) {
      if (db_result(db_query("SELECT count(*) FROM {filedepot_notifications} WHERE cid=%d AND uid=%d", $cid, $user->uid)) == 0) {
        $sql  = "INSERT INTO {filedepot_notifications} (cid,cid_newfiles,cid_changes,uid,date) ";
        $sql .= "VALUES (%d,%d,%d,%d,%d)";
        db_query($sql, $cid, $fileadded, $filechanged, $user->uid, time());
      }
      else {
        $sql  = "UPDATE {filedepot_notifications} set cid_newfiles=%d, ";
        $sql .= "cid_changes=%d, date=%d ";
        $sql .= "WHERE uid=%d and cid=%d";
        db_query($sql, $fileadded, $filechanged, time(), $user->uid, $cid);
      }
    }
    else {
      db_query("DELETE FROM {filedepot_notifications} WHERE uid=%d AND cid=%d", $user->uid, $cid);
    }

    // Now test if user has requested to change the folder's parent and if they have permission to this folder
    $pid = db_result(db_query("SELECT pid FROM {filedepot_categories} WHERE cid=%d", $cid));
    if ( $pid != $catpid) {
      if ($filedepot->checkPermission($catpid, 'admin') OR user_access('administer filedepot')) {
        db_query("UPDATE {filedepot_categories} SET pid=%d WHERE cid=%d", $catpid, $cid);
      }
    }

  }
  else {
    $retval['retcode'] = 500;
  }
  return $retval;
}


function filedepotAjaxServer_moveCheckedFiles() {
  global $user;
  $filedepot = filedepot_filedepot();
  $message = '';
  $retval = array();
  $cid = intval($_POST['cid']);
  $newcid = intval($_POST['newcid']);
  $reportmode = check_plain($_POST['reportmode']);
  $fileitems = check_plain($_POST['checkeditems']);
  $files = explode(',', $fileitems);

  $filedepot->cid = $cid;
  $filedepot->activeview = $reportmode;

  $duplicates = 0;
  $movedfiles = 0;
  if ($newcid > 0 AND $user->uid > 0 ) {
    foreach ($files as $id) {
      if ($id > 0 ) {
        if ($reportmode == 'incoming') {
          if ($filedepot->moveIncomingFile($id, $newcid)) {
            $movedfiles++;
          }
        }
        else {
          $fname = db_result(db_query("SELECT fname FROM {filedepot_files} WHERE fid=%d", $id));
          if (db_result(db_query("SELECT fid from {filedepot_files} WHERE cid=%d AND fname='%s'", $newcid, $fname)) > 0) {
            $duplicates++;
          }
          elseif ($filedepot->moveFile($id, $newcid)) {
            $movedfiles++;
          }
        }
      }
    }
  }

  if ($movedfiles > 0) {
    $message = "Successfully moved $movedfiles files to this folder.";
    if ($reportmode == 'incoming') {
      // Send out email notifications of new file added to all users subscribed  -  Get fileid for the new file record
      $args = array($newcid, $user->uid);
      $fid = db_result(db_query_range("SELECT fid FROM {filedepot_files} WHERE cid=%d AND submitter=%d ORDER BY fid DESC", $args, 0, 1));
      filedepot_sendNotification($fid, FILEDEPOT_NOTIFY_NEWFILE);
    }
    if ($duplicates > 0) {
      if ($duplicates == 1) {
        $message .= "&nbsp;File could not be moved as it is a duplicate.";
      }
      else {
        $message .= "&nbsp;$duplicates files could not be moved as they are duplicates.";
      }
    }
    $cid = $newcid;
  }
  elseif ($newcid == 0) {
    $message = 'Unable to move any files - Invalid new folder selected';
  }
  elseif ($duplicates > 0) {
    if ($duplicates == 1) {
      $message = "File could not be moved as it is a duplicate.";
    }
    else {
      $message = "$duplicates files could not be moved as they are duplicates.";
    }
  }
  else {
    $message = 'Unable to move any files - invalid folder or insufficient rights';
  }

  $retval['retcode'] = 200;
  $retval['cid'] = $cid;
  $retval['movedfiles'] = $movedfiles;
  $retval['message'] = $message;
  $retval['activefolder'] = theme('filedepot_activefolder');
  $retval['displayhtml'] = filedepot_displayFolderListing($cid);

  return $retval;


}

function filedepotAjaxServer_updateFileSubscription($fid, $op='toggle') {
  global $user;

  $retval = array('retcode' => '', 'subscribed' => '');
  if ($user->uid > 0) {
    $uid = $user->uid;
  }
  else {
    $retval['retcode'] = FALSE;
    return $retval;
  }

  if (db_result(db_query("SELECT count(fid) FROM {filedepot_files} WHERE fid=%d", $fid)) == 1) {    // Valid file and user
    $cid = db_result(db_query("SELECT cid FROM {filedepot_files} WHERE fid=%d", $fid));
    // Check if user has an ignore file changes record or a subscribe to changes record for this file
    $direct = FALSE;
    $ignorefilechanges = FALSE;
    $query = db_query("SELECT fid,ignore_filechanges FROM {filedepot_notifications} WHERE fid=%d and uid=%d", $fid, $uid);
    if ($A = db_fetch_array($query)) {
      if ($A['ignore_filechanges'] == 1) {
        $ignorefilechanges = TRUE;
      }
      else {
        $direct = TRUE;
      }
    }
    $indirect = db_result(db_query("SELECT cid_changes FROM {filedepot_notifications} WHERE cid=%d AND uid=%d", $cid, $uid));
    if ($indirect AND $direct) {    // User may have subscribed to single file and the folder option was also set
      if ($op == 'toggle' or $op == 'remove') {
        db_query("UPDATE {filedepot_notifications} set ignore_filechanges = 1 WHERE fid=%d AND uid=%d", $fid, $uid);
        $retval['subscribed'] = FALSE;
      }
    }
    elseif (($direct OR $indirect) AND !$ignorefilechanges) { // User is subscribed - so un-subscribe
      if ($op == 'toggle' or $op == 'remove') {
        $retval['subscribed'] = FALSE;
        if ($direct > 0) {
          db_query("DELETE FROM {filedepot_notifications} WHERE fid=%d AND uid=%d", $fid, $uid);
        }
        elseif ($indirect > 0) {
          db_query("INSERT INTO {filedepot_notifications} (fid,ignore_filechanges,uid,date) VALUES (%d,1,%d,%d)", $fid, $uid, time());
        }
      }

    }
    else {    // User is not subscribed
      if ($op == 'toggle' OR $op == 'add') {
        $retval['subscribed'] = TRUE;
        if ($ignorefilechanges) {
          //delete the exception record
          db_query("UPDATE {filedepot_notifications} set ignore_filechanges = 0 WHERE fid=%d AND uid=%d", $fid, $uid);
        }
        elseif (!$direct AND !$indirect) {
          db_query("INSERT INTO {filedepot_notifications} (fid,cid,uid,date) VALUES (%d,%d,%d,%d)", $fid, $cid, $uid, time());
        }
      }

    }
    $retval['retcode'] = TRUE;

  }
  else {
    $retval['retcode'] = FALSE;
  }

  return $retval;

}


function filedepotAjaxServer_broadcastAlert($fid, $comment) {
  global $user;
  $filedepot = filedepot_filedepot();
  $retval = '';
  $target_users = array();
  if (variable_get('filedepot_default_allow_broadcasts', 1) == 1) {  // Site default set to allow broadcast enabled
    $uquery = db_query("SELECT uid FROM {users} WHERE uid > 0 AND status = 1");
    while ( $A = db_fetch_object($uquery)) {
      if ($A->uid != $user->uid) {
        if (db_result(db_query("SELECT allow_broadcasts FROM {filedepot_usersettings} WHERE uid=%d", $A->uid)) == 0)  {
          $personal_setting = FALSE;   // Found user setting to not be notified
        }
        else {
          $personal_setting = TRUE;
        }
        // Only want to notify users that don't have setting disabled or exception record
        if ($personal_setting == TRUE) {
          $target_users[] = $A->uid;
        }
      }
    }

  }
  else {
    $sql = "SELECT a.uid FROM {filedepot_usersettings} a "
    . "LEFT JOIN {users} b on b.uid=a.uid "
    . "WHERE a.allow_broadcasts=1 and b.status=1";
    $uquery = db_query($sql);
    while ($B  = db_fetch_object($uquery)) {
      if ($user->uid != $B->uid) {
        $target_users[] = $B->uid;
      }
    }
  }

  if (count($target_users) > 0) {

    /* Send out Notifications to all users on distribution
    * Use the Bcc feature of COM_mail (added June/2009)
    * To send to complete distribution as one email and not loop thru distribution sending individual emails
    */
    $distribution = array();
    $lastuser = 0;
    $type = FILEDEPOT_BROADCAST_MESSAGE;
    $sql = "SELECT file.title,file.cid FROM {filedepot_files} file WHERE file.fid=%d";
    $query = db_query($sql, $fid);
    $frec = db_fetch_object($query);
    foreach ($target_users as $target_uid) {
      // Check that user has view access to this folder
      if ($target_uid != $lastuser AND $filedepot->checkPermission($frec->cid, 'view', $target_uid)) {
        $query = db_query("SELECT name,mail FROM {users} WHERE uid=%d", $target_uid);
        $urec = db_fetch_object($query);
        if (!empty($urec->mail)) {
          $distribution[] = $urec->mail;
          $sql = "INSERT INTO {filedepot_notificationlog} (target_uid,submitter_uid,notification_type,fid,cid,datetime) "
          . "VALUES (%d,%d,%d,%d,%d,%d)";
          db_query($sql, $target_uid, $user->uid, $type, $fid, $frec->cid, time());
        }
        $lastuser = $target_uid;
      }
    }
    if (count($distribution) > 0) {
      $message['subject'] = variable_get('site_name', '') . ' - ' . t('Broadcast Notification');
      $message['body'] = $comment . "\n\n";
      $link = url('filedepot', array('query' => drupal_query_string_encode(array('cid' => $frec->cid)), 'absolute' => true));
      $message['body'] .= t('The file: !filename can be accessed at !link',
      array('!filename' => $frec->title, '!link' => $link)) . "\n\n";
      $message['body'] .= t('You are receiving this broadcast alert, because your notification setting is enabled.');
      $message['to'] = 'Filedepot Distribution';
      $message['headers']['Bcc'] = implode(',', $distribution);
      drupal_mail_send($message);
      $retval['retcode'] = 200;
      $retval['count'] = count($distribution);
    }
    else {
      $retval['retcode'] = 205;
    }

  }
  else {
    $retval['retcode'] = 205;
  }
  return $retval;
}
