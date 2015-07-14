<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="positions.listpositions" />
<input type="hidden" name="boxchecked" value="0" />
    <div class="info_header">
        <?php echo JText::_('FACTORY_CURRENT_THEME'),': ',$this->themeheader->name ; ?>
        <div>
            <?php echo $this->themeheader->description;?>
        </div>
    </div>
    <div>
        <?php echo JText::_("FACTORY_CURRENT_PAGE"),$this->pagehtml;?>
        <?php echo 't_'.$this->page.'.tpl' ?>
    </div>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="10" colspan="2">
					<?php echo JText::_('FACTORY_'); ?>
				</th>
				<th class="title" width="15%">
					<?php echo JText::_('FACTORY_POSITION_NAME'); ?>
				</th>
				<th width="*%">
					<?php echo JText::_('FACTORY_ASSIGNED_FIELDS'); ?>
				</th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php foreach($this->positions as $position):?>
		    <tr>
		        <td width="10"><?php echo $i++; ?></td>
                <td width="10"><?php echo JHtml::_('grid.id', $i, $position->name,false,'positon'); ?> </td>
                <td><a href="<?php echo JURI::base();?>index.php?option=<?php echo APP_EXTENSION;?>&task=positions.listfields&page=<?php echo $position->pagename;?>&position=<?php echo $position->name;?>">
                        <?php echo $position->name;?></a></td>
                <td><?php echo JText::_("FACTORY_ASSIGNED_FIELDS"),count($position->fields),"<br>";
                        echo "<ul>";
                        for($j=0;$j<10 && $j<count($position->fields);$j++)
                            echo "<li>",$position->fields[$j]->name,"</li>";
                        echo "</ul>";
                ?></td>

		    </tr>
		<?php endforeach;?>
    </table>
</form>
