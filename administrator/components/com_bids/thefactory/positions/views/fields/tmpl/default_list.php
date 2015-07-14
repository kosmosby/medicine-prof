<?php defined('_JEXEC') or die('Restricted access'); ?>
<style type="text/css">
    table.adminlist thead th {text-align: left;}
</style>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="positions.listfields" />
<input type="hidden" name="boxchecked" value="0" />
    <div class="info_header">
        <?php echo JText::_('FACTORY_CURRENT_THEME'),': ',$this->themeheader->name ; ?>
        <div>
            <?php echo $this->themeheader->description;?>
        </div>
    </div>
    <div>
        <?php echo JText::_("FACTORY_CURRENT_PAGE"),$this->pagehtml;?>
        <?php echo JText::_("FACTORY_CURRENT_POSITION"),$this->positionshtml;?>
        <input type="button" value="<?php echo JText::_('FACTORY_ASSIGN_FIELDS'); ?>" onclick="Joomla.submitbutton('positions.assignfields')" />
    </div>    
	<table class="adminlist">
		<thead>
			<tr>
				<th width="10" colspan="2">
					<?php echo JText::_('FACTORY_'); ?>
				</th>
				<th class="title" width="15%" align="left">
					<?php echo JText::_('FACTORY_FIELD_NAME'); ?>
				</th>
				<th align="left">
					<?php echo JText::_('FACTORY_FIELD_SECTION'); ?>
				</th>
                <th align="left">
					<?php echo JText::_('FACTORY_FIELD_TYPE'); ?>
				</th>
                <th align="left">
                    &nbsp;
                </th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php foreach($this->fields as $field):?>
		    <tr>
		        <td width="10"><?php echo $i++; ?></td>
                <td width="10"><?php echo JHtml::_('grid.id', $i, $field->id,false,'id'); ?> </td>
                <td><a href="<?php echo JURI::base();?>index.php?option=<?php echo APP_EXTENSION;?>&task=fields.edit&id=<?php echo $field->id;?>">
                    <?php echo $field->name;?></a></td>
                <td><?php echo $field->page;?></td>
                <td><?php echo $field->ftype;?></td>
                <td> <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=positions.delete&field=<?php echo $field->id;?>&page=<?php echo $this->page;?>&position=<?php echo $this->position;?>">
                    <?php
                        echo JHtml::_("image.administrator","admin/publish_r.png",'/images/',null,'/images/',null,'title="'.JText::_("FACTORY_DELETE_ASSIGNMENT").'"');
                    ?>
                    </a>
                </td>
		    </tr>
		<?php endforeach;?>
    </table>
</form>
