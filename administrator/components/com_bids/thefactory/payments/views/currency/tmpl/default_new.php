<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="currencies.save" />
<table class="adminlist">
    <tr>
        <td><?php echo JText::_("FACTORY_CURRENCY"); ?>:</td>
        <td><input name="name" value=""/></td>
    </tr>
    <tr>
        <td><?php echo JText::_("FACTORY_CONVERSION_RATE"); ?>:</td>
        <td><input name="convert" value=""/></td>
    </tr>
</table>
</form>
