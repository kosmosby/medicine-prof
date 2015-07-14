<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    JHtml::_('behavior.tooltip');

    $fields=$this->form->getFieldset((string)$this->currentfieldset->attributes()->name);
?>
<table class="paramlist admintable" width="100%">
<?php foreach($fields as $field): ?>
    <?php if ((string)$this->currentfieldset->attributes()->hidelabel): ?>
        <tr>
            <td colspan="2"><?php echo $field->input;?></td>
        </tr>
    <?php else: ?>
        <tr>
            <td class="paramlist_key"><?php echo $field->label;?></td>
            <td><?php echo $field->input;?></td>
        </tr>
    <?php endif; ?>
<?php endforeach;?>
</table>
