<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="themes.setdefault" />
<input type="hidden" name="boxchecked" value="0" />
	<table class="adminlist">
		<thead>
			<tr>
				<th width="10" colspan="2">
					<?php echo JText::_('FACTORY_NR'); ?>
				</th>
				<th class="title" width="15%">
					<?php echo JText::_('FACTORY_THEME_NAME'); ?>
				</th>
				<th >
					<?php echo JText::_('FACTORY_DESCRIPTION'); ?>
				</th>
				<th>
					<?php echo JText::_('FACTORY_FOLDER'); ?>
				</th>
				<th>
					<?php echo JText::_('FACTORY_AUTHOR'); ?>
				</th>
				<th>
					<?php echo JText::_('FACTORY_VERSION'); ?>
				</th>
                <th>
                    &nbsp;
                </th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php foreach($this->themes as $theme):?>
		    <tr>
		        <td width="10"><?php echo $i++; ?></td>
                <td width="10"><?php echo JHtml::_('grid.id', $i, basename(dirname($theme->manifest_file)),false,'theme'); ?> </td>
                <td><?php echo $theme->name;?></td>
                <td><?php echo $theme->description;?></td>
                <td><?php echo basename(dirname($theme->manifest_file));?></td>
                <td><?php echo $theme->author;?></td>
                <td><?php echo $theme->version;?></td>
                <?php if (JTheFactoryThemesHelper::isCurrentTheme($theme->manifest_file)):;?>
                    <td><?php echo JHtml::_('image','admin/tick.png', "Current Template", "title='".JText::_('FACTORY_CURRENT_THEME')."'", true); ?></td>
                <?php else:?>
                    <td>
                    <a href="#" onclick="return listItemTask('cb<?php echo $i;?>','themes.setdefault');">
                        <?php echo JHtml::_('image','admin/disabled.png', "Set Current",  "title='".JText::_('FACTORY_USE_THIS_THEME')."'", true); ?>
                    </a>
                    </td>
                <?php endif;?>
		    </tr>
		<?php endforeach;?>
    </table>
</form>
