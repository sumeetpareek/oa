<?php
/**
  * @file
  * filelisting_record.tpl.php
  */
?>

<div class="listing_record" id="folder_<?php print $subfolder_id ?>_rec_<?php print $fid ?>">
    <div class="listing_record_outer_container">
        <div class="listing_record_attributes">
                <span class="listing_record_selectbox"><input id="chkfile<?php print $fid ?>" type="checkbox" name="chkfile" value="<?php print $fid ?>" onClick="updateCheckedItems(this)"></span>
                <span class="listing_record_favorite" style="display:<?php print $show_favorite; ?>">
                    <a href="?id=<?php print $fid ?>" onclick="return false;"><img id="favitem<?php print $fid ?>" class="togglefavorite" src="<?php print $favorite_status_image ?>" TITLE="<?php print $LANG_favorite_status ?>"></a>
                </span>
                <span class="listing_record_file_image" style="padding-left:<?php print $padding_left ?>px;">
                    <a class="filedetailsdialog" href="<?php print $details_link_parms ?>"><img src="<?php print $extension_icon ?>"></a>
                </span>
                <span class="listing_record_file_number"><?php print $file_number ?></span>
                <span class="filedetailslink" style="float:left;">
                    <a id="listingFilenameRec<?php print $fid ?>" class="filedetailsdialog" href="<?php print $details_link_parms ?>" TITLE="Click to view details"><?php print $file_name ?></a>
                </span>
                <span id="listingLockIconRec<?php print $fid ?>" class="listing_record_lock" style="display:<?php print $show_lock ?>"><img src="<?php print $locked_icon ?>" TITLE="File Locked"></span>
        </div>
        <div class="tags" id="listingTagsRec<?php print $fid ?>"><?php print $tags ?></div>
        <div class="listing_record_rightside_container">
            <div class="listing_record_actions_container <?php print $actionclass?>">
                <span><?php print $action1_link ?></span>
                <span><?php print $action2_link ?></span>
                <span style="display:<?php print $show_approvalsubmitter ?>;"><?php print $submitter ?></span>
            </div>
            <div class="listing_record_modified_date"><?php print $modified_date ?></div>
            <div class="listing_record_submitter"  style="display:<?php print $show_submitter ?>;"><?php print $submitter ?></div>
            <div class="listing_record_foldername" style="display:<?php print $show_foldername ?>;"><a href="#" onClick="makeAJAXGetFolderListing(<?php print $subfolder_id ?>);return false;"><?php print $folder_name ?></a></div>
        </div>
    </div>
    <div class="filedesc" id="filedesc_container_<?php print $fid ?>" style="padding-left:<?php print $file_desc_padding_left ?>px;padding-right:75px;display:none;">
        <div><span class="filedesc_span" id="listingDescriptionRec<?php print $fid ?>"><?php print $file_description ?></span>
          <div class="clearboth"></div>
        </div>
    </div>
</div>
<div id="subfolder<?php print $subfolder_id ?>_rec<?php print $fid ?>_bottom"><?php print $more_records_message ?></div>

