<?php
/**
  * @file
  * newfolder_form.tpl.php
  */
?>

<form id="frmNewFolder" method="post">
  <input type="hidden" name="op" value="createfolder">
  <table class="formtable">
    <tr>
      <td width="25%"><label for="catname"><?php print $LANG_folder ?>:</label></td>
      <td width="70%"><input type="text" id="catname" class="form-text" name="catname" style="width:265px;" /></td>
    </tr>
    <tr>
      <td><label for="parent"><?php print $LANG_parentfolder ?>:</label></td>
      <td><select id="newcat_parent" class="form-select" name="catparent" style="width:270px">
          <?php print $folder_options ?>
        </select>
      </td>
    </tr>
    <tr>
      <td><label for="catdesc"><?php print $LANG_description ?>:</label></td>
      <td><textarea id="catdesc" class="form-textarea" name="catdesc" rows="3" style="width:265px;font-size:10pt;"></textarea></td>
    </tr>
    <tr>
      <td><label for="catinherit"><?php print $LANG_inherit ?>:</label></td>
      <td><input type="checkbox" id="catinherit" name="catinherit" value="1"></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center;padding:15px;">
        <input id="btnNewFolderSubmit" type="button" class="form-submit" value="<?php print $LANG_submit ?>">
        <span style="padding-left:10px;">
          <input id="btnNewFolderCancel" type="button" class="form-submit" value="<?php print $LANG_cancel ?>">
        </span>
      </td>
    </tr>
  </table>
 </form>
