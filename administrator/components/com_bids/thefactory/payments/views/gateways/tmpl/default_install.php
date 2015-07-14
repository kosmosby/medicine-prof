<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="gateways.doupload" />
    <div class="info_header">
        <h2><?php echo JText::_("FACTORY_UPLOAD_PAYMENT_GATEWAY_PACK") ?></h2>
    </div>
	<table class="adminlist">
    <tr>
        <td><?php echo JText::_("FACTORY_NEW_PAYMENT_GATEWAY_ZIP");?> : </td>
        <td><input name="pack" type="file" value="" size="60"/></td>
    </tr>
    </table>
</form>
