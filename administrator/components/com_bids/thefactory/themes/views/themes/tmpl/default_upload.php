<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="themes.doupload" />
    <div class="info_header">
        <h2><?php echo JText::_("FACTORY_UPLOAD_THEME_ZIP_PACK") ?></h2>
    </div>
	<table class="adminlist">
    <tr>
        <td><?php echo JText::_("FACTORY_NEW_THEME_ZIP");?> : </td>
        <td><input name="theme" type="file" value="" size="60"/></td>
    </tr>
    </table>
</form>
