<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_flexpaper&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="flexpaper-form" enctype="multipart/form-data">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_FLEXPAPER_DETAILS');?></legend>
            <div style="float: left;">
                <ul class="adminformlist">
                    <li>
                        <label title="" class="hasTip" id="membership_list_id-lbl">
                            <?php echo JText::_('COM_FLEXPAPER_BUNDLE_NAME');?>
                        </label>

                         <?php echo $this->bundleslist;?>
                    </li>
                </ul>
            </div>

            <div style="float: left; padding-left: 100px;">
                <ul class="adminformlist">
                    <li>
                        <label title="" class="hasTip" id="flexpaper_id-lbl">
                            <?php echo JText::_('COM_FLEXPAPER_COURSES_IN_BUNDLE');?>
                        </label>

                        <?php echo $this->membershiplist;?>
                    </li>
                </ul>
            </div>


    </fieldset>
	<div>
		<input type="hidden" name="task" value="bundle.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>