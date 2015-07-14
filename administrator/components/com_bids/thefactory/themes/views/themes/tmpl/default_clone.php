<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="themes.doclone" />
<input type="hidden" name="themename" value="<?php echo $this->themename;?>" />
    <div class="info_header">
        <h2><?php echo JText::_("FACTORY_CLONE_THEME") ?></h2>
    </div>
	<table class="adminlist">
    <tr>
        <td><?php echo JText::_("FACTORY_NEW_THEME_FOLDER");?> : </td>
        <td><input name="themefolder" type="text" value=""/></td>
    </tr>
    <tr>
        <td><?php echo JText::_("FACTORY_NEW_THEME_NAME");?> : </td>
        <td><input name="name" type="text" size="40" value="<?php echo $this->theme->name;?>"/></td>
    </tr>
    <tr>
        <td><?php echo JText::_("FACTORY_NEW_THEME_DESCRIPTION");?> : </td>
        <td><textarea name="description" rows="5" cols="50"><?php echo $this->theme->description;?></textarea></td>
    </tr>
    </table>
</form>
