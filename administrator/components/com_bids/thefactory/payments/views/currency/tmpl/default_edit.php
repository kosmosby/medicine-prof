<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="currencies.save" />
<input type="hidden" name="id" value="<?php echo $this->currency->id;?>" />
<table class="adminlist">
    <tr>
        <td><?php echo JText::_("FACTORY_CURRENCY"); ?>:</td>
        <td><input name="name" value="<?php echo $this->currency->name;?>"/></td>
    </tr>
    <tr>
        <td><?php echo JText::_("FACTORY_CONVERSION_RATE"); ?>:</td>
        <td><input name="convert" value="<?php echo $this->currency->convert;?>"/></td>
    </tr>
</table>
</form>
