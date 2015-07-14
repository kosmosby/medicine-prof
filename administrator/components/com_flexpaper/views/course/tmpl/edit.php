<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_flexpaper&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="flexpaper-form" enctype="multipart/form-data">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_FLEXPAPER_DETAILS');?></legend>
        <ul class="adminformlist">
            <?php foreach($this->form->getFieldset() as $field): ?>
            <li><?php echo $field->label;echo $field->input;?></li>
            <?php endforeach; ?>

        </ul>
    </fieldset>
    <div>
        <input type="hidden" name="task" value="course.edit" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>