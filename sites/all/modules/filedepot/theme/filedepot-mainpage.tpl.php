<?php
/**
  * @file
  * page.tpl.php
  */
?>

<?php
  // Initialize variable id unknown to solve any PHP Notice level error messages
  if (!isset($search_query)) $search_query = 0;
?>

<!-- On-Demand loading the Module Javascript using YUI Loader -->


<script type="text/javascript">
  var useYuiLoader = true;              // Set to false if you have manually loaded all the needed YUI libraries else they will dynamically be loaded
  var pagewidth = 0;                    // Integer value: Use 0 for 100% width with auto-resizing of layout, or a fixed width in pixels
                                        // Height of main display is set by the height of #filedepot div - default set in CSS
  var numGetFileThreads = 5;            // Max number of concurrent AJAX threads to spawn in the background to retrieve & render record details for subfolders
  var useModalDialogs = true;           // Default of true is preferred but there is an IE7 bug that makes them un-useable so in this case set to false

  // Do not modify any variables below
  var filedepotfolders = '';
  var filedepotdetail = '';
  var folderstack = new Array;  // will contain list of folders being processed by AJAX YAHOO.filedepot.getmorefiledata function
  var fileID;
  var initialfid = <?php print $initialfid; ?>;
  var initialcid = <?php print $initialcid; ?>;
  var activefolder = <?php print $initialcid; ?>;
  var initialop = '<?php print $initialop; ?>';
  var initialparm = '<?php print $initialparm; ?>';
  var siteurl = '<?php print $site_url; ?>';
  var ajax_post_handler_url = '<?php print $ajax_server_url; ?>';
  var actionurl_dir = '<?php print $actionurl_dir; ?>';
  var imgset = '<?php print $layout_url; ?>/css/images';
  var yui_uploader_url = '<?php print $yui_uploader_url; ?>';
  var ajaxactive = false;
  var clear_ajaxactivity = false;
  var timerArray = new Array();
  var lastfiledata = new Array();
  var expandedfolders = new Array();
  var searchprompt = '<?php print $LANG_searchprompt; ?>';
  var show_upload = <?php print $show_upload; ?>;
  var show_newfolder = <?php print $show_newfolder; ?>;
  var blockui = false;
  var blockui_options = { message: '<h1><?php print t('Please wait ...'); ?></h1>' };

</script>

<script type="text/javascript">
  var YUIBaseURL  = "<?php print $yui_base_url ?>";
</script>

<script type="text/javascript">
   if (useYuiLoader == true) {
    // Instantiate and configure Loader:
    var loader = new YAHOO.util.YUILoader({

      base: YUIBaseURL,
      // Identify the components you want to load.  Loader will automatically identify
      // any additional dependencies required for the specified components.
      require: ["container","layout","resize","connection","dragdrop","menu","button","tabview","autocomplete","treeview","element","cookie","uploader","logger","animation"],

      // Configure loader to pull in optional dependencies.  For example, animation
      // is an optional dependency for slider.
      loadOptional: true,

      // The function to call when all script/css resources have been loaded
      onSuccess: function() {
        filedepotPleaseWait('show')
        timeDiff.setStartTime();
        Dom = YAHOO.util.Dom;
        Event = YAHOO.util.Event;
        Event.onDOMReady(function() {
          setTimeout('init_filedepot()',1000);
        });
      },
      onFailure: function(o) {
        alert("The required javascript libraries could not be loaded.  Please refresh your page and try again.");
      },

      allowRollup: true,

      // Configure the Get utility to timeout after 10 seconds for any given node insert
      timeout: 10000,

      // Combine YUI files into a single request (per file type) by using the Yahoo! CDN combo service.
      combine: false
    });

    // Load the files using the insert() method. The insert method takes an optional
    // configuration object, and in this case we have configured everything in
    // the constructor, so we don't need to pass anything to insert().
    loader.insert();

  } else {
    filedepotPleaseWait('hide')
    timeDiff.setStartTime();
    Dom = YAHOO.util.Dom;
    Event = YAHOO.util.Event;
    Event.onDOMReady(function() {
      setTimeout('init_filedepot()',1000);
    });
  }

</script>



<!-- filedepot module wrapper div -->
<div id="filedepotmodule">


  <div id="filedepot">

      <div id="filedepottoolbar" class="filedepottoolbar" style="margin-right:0px;padding:5px;display:none;margin-bottom:1px;">
      <div style="float:left;width:250px;height:20px;">
        <span id="newfolderlink" class="yui-button yui-link-button" style="display:none;">
          <span class="first-child">
            <a href="#"><?php print $LANG_newfolder ?></a>
          </span>
        </span>
        <span id="newfilelink" class="yui-button yui-link-button" style="display:none;">
          <span class="first-child">
            <a href="#"><?php print $LANG_upload ?></a>
          </span>
        </span>
      </div>
      <?php print $toolbarform ?>
      <div class="filedepottoolbar_searchbox">
        <div class="filedepottoolbar_searchform">
          <form name="fsearch" onSubmit="makeAJAXSearch();return false;">
            <input type="hidden" name="tags" value="">
            <table>
              <tr>
                <td width="50%"><input type="text" size="20" name="query" id="searchquery" class="form-text" style="height:12px;padding:3px 3px 5px 3px;" value="<?php print $search_query ?>" onClick="this.value='';"></td>
                <td width="50%" style="text-align:right;"><input type="button" id="searchbutton" value="<?php print t('Search') ?>"></td>
              </tr>
            </table>
          </form>
        </div>
        <div class="tagsearchboxcontainer" style=";width:10%;padding:5px;">
          <div><a id="showsearchtags" href="#">Tags</a></div>
        </div>
      </div>
    </div>
    <div class="tagsearchboxcontainer">
      <div id="tagspanel" style="display:none;">
        <div class="hd"><?php print $LANG_searchtags ?></div>
        <div id="tagcloud" class="bd tagcloud">
          <?php print $tagcloud ?>
        </div>
      </div>
    </div>

    <div id="filedepot_sidecol">
      <!-- Leftside Folder Navigation generated onload by page javascript -->

      <div id="filedepotNavTreeDiv"></div>
      <div class="clearboth"></div>
    </div>
    <div id="filedepot_centercol">
      <div id="filedepot_alert" class="filedepot_alert" style="display: <?php print $show_alert ?>;overflow:hidden;">
        <div id="filedepot_alert_content" class="floatleft"><?php print $alert_message ?></div>
        <div id="cancelalert" class="floatright" style="position:relative;top:4px;padding-right:10px;">
          <a class="cancelbutton" href="#">&nbsp;</a>
        </div>
        <div class="clearboth"></div>
      </div>
      <div id="activefolder_container"></div>
      <div class="clearboth" id="showactivetags" style="display:none;">
        <div id="tagsearchbox" style="padding-bottom:5px;">Search Tags:&nbsp;<span id="activesearchtags"></span></div>
      </div>
      <div class="clearboth"></div>
      <div style="margin-right:0px;">
        <div id="filelistingheader" style="margin-bottom:10px;"></div>
        <div class="clearboth"></div>
        <form name="frmfilelisting" action="<?php print $actionurl_dir; ?>" method="post" style="margin:0px;">
          <div id="filelisting_container"></div>
        </form>
      </div>
      <div class="clearboth"></div>

    </div> <!-- end of filedepot_centercol div -->

    <div class="clearboth"></div>
  </div>   <!-- end of filedepot div -->

  <div class="clearboth"></div>

  <!-- Supporting Panels - initially hidden -->

    <div id="filedetails" style="display:none;">
      <div id="filedetails_titlebar" class="hd"><?php print $LANG_filedetails ?></div>
      <div id="filedetailsmenubar" class="yuimenubar" style="border:0px;">
        <div class="bd" style="margin:0px;padding:0px 2px 2px 2px;border:0px;font-size:11pt;">
          <ul class="first-of-type">
            <li id="displaymenubaritem" class="yuimenubaritem first-of-type">
              <a id="menubar_downloadlink" href="" TITLE="<?php print $LANG_downloadmsg ?>"><?php print $LANG_download_menuitem ?></a>
            </li>
            <li id="editmenubaritem" class="yuimenubaritem first-of-type">
              <a id="editfiledetailslink" href="#" TITLE="<?php print $LANG_editmsg ?>"> <?php print $LANG_edit_menuitem ?> </a>
            </li>
            <li id="addmenubaritem" class="yuimenubaritem first-of-type">
              <a id="newversionlink" href="#" TITLE="<?php print $LANG_versionmsg ?>"><?php print $LANG_version_menuitem ?></a>
            </li>
            <li id="approvemenubaritem" class="yuimenubaritem first-of-type">
              <a id="approvefiledetailslink" href="#" TITLE="<?php print $LANG_approvemsg ?>"><?php print $LANG_approve_menuitem ?></a>
            </li>
            <li id="deletemenubaritem" class="yuimenubaritem first-of-type">
              <a id="deletefiledetailslink" href="#" TITLE="<?php print $LANG_deletemsg ?>"><?php print $LANG_delete_menuitem ?></a>
            </li>
            <li id="lockmenubaritem" class="yuimenubaritem first-of-type">
              <a id="lockfiledetailslink" href="#" TITLE="<?php print $LANG_lockmsg ?>"><?php print $LANG_lock_menuitem ?></a>
            </li>
            <li id="notifymenubaritem" class="yuimenubaritem first-of-type">
              <a id="notifyfiledetailslink" href="#" TITLE="<?php print $LANG_subscribemsg ?>"><?php print $LANG_subscribe_menuitem ?></a>
            </li>
            <li id="broadcastmenubaritem" class="yuimenubaritem first-of-type">
              <a id="broadcastnotificationlink" href="#" TITLE="<?php print $LANG_broadcasttmsg ?>"><?php print $LANG_broadcast_menuitem ?></a>
            </li>
          </ul>
        </div>
      </div>
      <div id="filedetails_statusmsg" class="pluginInfo alignleft" style="display:none;"></div>
      <div id="displayfiledetails" class="alignleft" style="display:block;">

      </div>

      <div id="editfiledetails" class="alignleft" style="display:none;">
        <form id="frmFileDetails" name="frmFileDetails" method="POST">
          <input type="hidden" name="cid" value="">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="version" value="">
          <input type="hidden" name="tagstore" value="">
          <input type="hidden" name="approved" value="">

          <table width="100%" style="margin:10px;">
            <tr>
              <td width="100"><label><?php print $LANG_filename ?></label></td>
              <td width="225"><input type="text" class="form-text" name="filetitle" size="29" value="" style="width:195px;" /></td>
              <td width="80"><label><?php print $LANG_folder ?></label></td>
              <td width="255" id="folderoptions"></td>
            </tr>
            <tr style="vertical-align:top;">
              <td rowspan="3"><label><?php print $LANG_description ?></label></td>
              <td rowspan="3"><textarea rows="6" cols="30" name="description" style="width:195px;"></textarea></td>
              <td><label><?php print $LANG_owner ?></label></td>
              <td><span id="disp_owner"></span></td>
            </tr>
            <tr style="vertical-align:top;">
              <td><label><?php print $LANG_date ?></label></td>
              <td><span id="disp_date"></span></td>
            </tr>
            <tr>
              <td><label><?php print $LANG_size ?></label></td>
              <td><span id="disp_size"></span></td>
            </tr>
            <tr style="vertical-align:top;">
              <td><label><?php print $LANG_versionnote ?></label></td>
              <td><textarea rows="3" cols="30" name="version_note" style="width:195px;"></textarea></td>
              <td><label><?php print $LANG_tags ?></label></td>
              <td>
                <div id="tagsfield" style="padding-bottom:15px;">
                  <input id="editfile_tags" class="form-text" name="tags" type="text" size="30" style="width:210px" />
                  <div id="editfile_autocomplete" style="width:210px;"></div>
                </div>
                <div id="tagswarning" class="pluginAlert" style="width:180px;display:none;"><?php print $LANG_folderpermsmsg ?></div>
              </td>
            </tr>
            <tr>
              <td colspan="4" style="padding-top:10px;text-align:center;">
                <input type="button" value="<?php print t('Submit'); ?>" class="form-submit" onClick="makeAJAXUpdateFileDetails(this.form)"/>
                <span style="padding-left:10px;"><input id="filedetails_cancel" class="form-submit" type="button" value="<?php print $LANG_cancel ?>"></span>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>

    <div id="folderperms" style="display:none;">
      <div class="hd">Folder Permissions</div>
      <div id="folderperms_content" class="bd alignleft"></div>
    </div>

    <div id="newfolderdialog" style="display:none;">
      <div class="hd"><?php print $LANG_addfolder ?></div>
      <div id="newfolderdialog_form" class="bd" style="text-align:left;">

      </div>
    </div>

    <div id="moveIncomingFileDialog" style="display:none;">
      <div class="hd"><?php print $LANG_moveselected ?></div>
      <div class="pluginInfo alignleft" style="color:#000;font-size:90%"><?php print $LANG_selectdestination ?></div>
      <div id="movebatchfiledialog_form" class="bd" style="text-align:left;">

      </div>
    </div>

    <div id="movebatchfilesdialog" style="display:none;">
      <div class="hd"><?php print $LANG_moveselected ?></div>
      <div class="pluginInfo alignleft" style="color:#000;font-size:90%"><?php print $LANG_movepermsmsg ?></div>
      <div id="movebatchfilesdialog_form" class="bd" style="text-align:left;">

      </div>
    </div>

    <div id="newfiledialog" style="display:none;">
      <div id="newfiledialog_heading" class="hd"></div>
      <div class="bd" style="text-align:left;">
        <form name="frmNewFile" method="post" enctype="multipart/form-data">
          <input type="hidden" id="newfile_op" name="op" value="savefile">
          <input type="hidden" name="tagstore" value="">
          <input type="hidden" id="newfile_fid" name="fid" value="">
          <input type="hidden" id="cookie_session" name="cookie_session" value="<?php print $session_id ?>">
          <!-- This is where the file ID is stored after SWFUpload uploads the file and gets the ID back from upload.php -->
          <table class="formtable">
            <tr>
              <td width="30%" style="padding-top:10px;"><label for="filename">File:</label><span class="required">*</span></td>
              <td width="70%">
                <div id="fileProgress">
                  <div id="fileName"></div>
                  <div id="progressBar" class="uploaderprogress"></div>
                </div>
                <div id="uploaderUI" style="width:65px;height:25px;margin-left:5px;float:left"></div>
                <div class="uploadButton" style="float:left">
                  <a class="rolloverButton" href="#" onClick="upload(); return false;"></a>
                </div>
                <div id="btnClearUpload" style="padding-left:10px;padding-top:10px;float:left;visibility:hidden;">
                  <a href="#" onClick="uploaderInit(); return false;">Clear</a>
                </div>
              </td>
            </tr>
            <tr id="newfiledialog_filename">
              <td width="30%"><label for="filename"><?php print $LANG_displayname ?>:</label></td>
              <td width="70%"><input type="text" id="newfile_displayname" class="form-text" style="width:290px" /></td>
            </tr>
            <tr id="newfiledialog_folderrow">
              <td><label for="category"><?php print $LANG_parentfolder ?>:</label><span class="required">*</span></td>
              <td id="newfile_selcategory"><select id="newfile_category" name="category" style="width:290px" onChange="onCategorySelect(this);">
                </select>
              </td>
            </tr>
            <tr>
              <td><label for="tags"><?php print $LANG_tags ?>:</label></td>
              <td style="padding-top:0px;margin-top:0px;margin-bottom:10px;"><div style="padding-top:0px;margin-top:0px;padding-bottom:10px;">
                  <input id="newfile_tags" class="form-text" type="text" size="40" style="width:290px" />
                  <div id="newfile_autocomplete"></div>
                </div>
              </td>
            </tr>
            <tr id="newfiledialog_filedesc">
              <td style="padding-top:10px;"><label for="filedesc"><?php print $LANG_description ?>:</label></td>
              <td style="padding-top:10px;"><textarea id="newfile_desc" class="form-textarea" name="filedesc" rows="3" style="font-size:10pt;width:290px"></textarea></td>
            </tr>
            <tr>
              <td><label for="versionnote"><?php print $LANG_versionnote ?>:</label></td>
              <td><textarea id="newfile_notes" class="form-textarea" name="versionnote" rows="2" style="font-size:10pt;width:290px"></textarea></td>
            </tr>
            <tr>
              <td><label for="filedepot_notify"><?php print $LANG_emailnotify ?>:</label></td>
              <td><input id="filedepot_notify" name="notify" type="checkbox" value="1">&nbsp;<?php print $LANG_yes ?></td>
            </tr>
            <tr>
              <td colspan="2" style="padding:15px 0px;">
                <div class="floatleft required">*&nbsp;<?php print $LANG_required ?></div>
                <div class="floatleft" style="width:80%;text-align:center;">
                  <input id="btnNewFileSubmit" class="form-submit" type="button" value="<?php print t('Submit'); ?>" onClick="upload(); return false;">
                  <span style="padding-left:10px;">
                    <input id="btnNewFileCancel" class="form-submit" type="button" value="<?php print t('Cancel'); ?>">
                  </span>
                </div>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>

    <div id="broadcastDialog" style="display:none;">
      <div class="hd"><?php print $LANG_broadcast ?></div>
      <div class="pluginInfo alignleft" style="color:#000;font-size:90%"><?php print $LANG_broadcastmsg ?></div>
      <div class="bd" style="text-align:left;">
        <form id="frmBroadcast" name="frmBroadcast" method="post">
          <input type="hidden" name="fid" value="">
          <input type="hidden" name="cid" value="">
          <table class="formtable">
            <tr>
              <td><label for="parent"><?php print $LANG_message ?>:</label>&nbsp;</td>
              <td><textarea name="message" rows="4" class="form-textarea" style="width:300px;font-size:10pt;"></textarea></td>
            </tr>
            <tr>
              <td colspan="2" style="text-align:center;padding:15px;">
                <input id="btnBroadcastSubmit" type="button" class="form-submit" value="<?php print t('Send'); ?>">
                <span style="padding-left:10px;">
                  <input id="btnBroadcastCancel" type="button" class="form-submit" value="<?php print t('cancel'); ?>">
                </span>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>


</div>   <!-- end of filedepotmodule wrapper div -->

<div class="clearboth"></div>
