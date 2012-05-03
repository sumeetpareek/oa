<?php
/**
  * @file
  * notifications_folder_record.tpl.php
  */
?>  

<div class="listing_record" style="padding-left:0px">
  <div class="floatleft">
    <input id="chkfolder<?php print $folderid ?>" type="checkbox" name="chkfolder" value="<?php print $recid ?>" onClick="updateCheckedItems(this,'folder')">
  </div>
  <div class="floatleft" style="width:82%;padding-left:5px;">
    <div class="floatleft" style="width:60%"><a href="#" onClick="makeAJAXGetFolderListing(<?php print $folderid ?>);return false;"><?php print $foldername ?></a></div>
    <div class="floatleft" style="width:15%"><?php print $date ?></div>
    <div class="floatright" style="width:5%;"><input type="checkbox" disabled=disabled <?php print $chk_newfiles ?>></div>
    <div class="floatright" style="width:10%;"><input type="checkbox" disabled=disabled <?php print $chk_filechanges ?>></div>
  </div>
  <div class="floatright" style="width:5%;padding-right:10px;"><a href="#" onclick="doAJAXDeleteNotification('folder',<?php print $recid ?>);return false;"><?php print $LANG_delete ?></a></div>
</div>


