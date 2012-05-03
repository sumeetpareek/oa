<?php
/**
  * @file
  * filedetail.tpl.php
  */
?>  

<!-- File Details Panel content - used by the AJAX server.php function nexdocsrv_filedetails() -->
<div class="clearboth">
<table class="plugin" width="100%" border="0" cellspacing="0" cellpadding="2" style="padding-bottom:10px;">
    <tr>
        <td class="aligntop" style="padding:15px 10px 5px 10px;">
            <div style="padding-bottom:10px;"><img src="<?php print $fileicon; ?>">&nbsp;<a href="<?php print url('filedepot', array('query' => drupal_query_string_encode(array('cid' => $cid, 'fid' => $fid)), 'absolute' => true)); ?>" title="<?php print $LANG_link_message ?>" <?php print $disable_download ?>><strong><?php print $filetitle ?></strong></a>&nbsp;<span style="font-size:8pt;"><?php print $current_version ?></span></div>
            <div class="floatleft" style="width:100px;"><strong><?php print $LANG_folder ?>:</strong></div>
            <div class="floatleft"><?php print $foldername ?></div>
            <div class="clearboth"></div>
            <div class="floatleft" style="width:100px;"><strong><?php print $LANG_author ?>:</strong></div>
            <div class="floatleft" style="font-size:9pt;"><?php print $author ?></div>
            <div class="clearboth"></div>
            <div class="clearboth floatleft" style="width:100px;"><strong><?php print $LANG_tags ?>:</strong></div>
            <div class="floatleft" style="font-size:9pt;"><?php print $tags ?></div>
        </td>
        <td width="15%" class="alignright" style="padding:15px 10px 5px 10px;font-size:8pt;" nowrap><?php print $shortdate ?><br /><strong><?php print $LANG_size ?>:</strong>&nbsp;<?php print $size ?>
        <div id="lockedalertmsg" class="pluginAlert" style="display:<?php print $show_statusmsg ?>;"><?php print $statusmessage ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:5px 10px;"><strong><?php print $LANG_description ?>:</strong><br /><?php print $description ?></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:5px 10px;"><strong><?php print $LANG_version_note ?>:</strong><br /><?php print $current_ver_note ?></td>
    </tr>
</table>
<?php print $version_records ?>
</div>