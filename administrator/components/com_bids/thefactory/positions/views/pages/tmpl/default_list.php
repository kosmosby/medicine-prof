<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
    <div class="info_header">
        <h3><strong><?php echo JText::_('FACTORY_CURRENT_THEME'),': ',$this->themeheader->name ; ?></strong></h3>
        <div style="font-size: 110%;">
            <?php echo $this->themeheader->description;?>
        </div>
    </div>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="10">
					#
				</th>
				<th width="10">
                    &nbsp;
				</th>
                <th width="500">
                    &nbsp;
                </th>
				<th class="title" width="150">
					<?php echo JText::_('FACTORY_PAGE_NAME'); ?>
				</th>
				<th >
					<?php echo JText::_('FACTORY_DESCRIPTION'); ?>
				</th>
				<th width="240">
					<?php echo JText::_('FACTORY_ASSIGNED_FIELDS'); ?>
				</th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php foreach($this->pages as $page):?>
		    <tr>
		        <td valign="top"><?php echo $i++; ?></td>
                <td valign="top"><?php echo JHtml::_('grid.id', $i, $page->name,false,'page'); ?> </td>
                <td valign="top">
                    <img src="<?php echo JURI::root(),'/components/'.APP_EXTENSION.'/templates/'.$this->themeheader->folder.'/',$page->thumbnail;?>" 
                        border="0" width="500" title="<?php echo JText::_('FACTORY_PAGE_POSITIONS_FOR')," ",$page->description;?>"/>
                </td>
                <td valign="top"><a href="<?php echo JURI::base();?>index.php?option=<?php echo APP_EXTENSION;?>&task=positions.listpositions&page=<?php echo $page->name;?>"><?php echo $page->name;?></a></td>
                <td valign="top"><?php echo $page->description;?></td>
                <td valign="top"><?php echo JText::_("FACTORY_ASSIGNED_FIELDS"),count($page->fields),"<br>";
                        echo "<ul>";
                        for($j=0;$j<10 && $j<count($page->fields);$j++)
                            echo "<li>",$page->fields[$j]->name,"</li>";
                        echo "</ul>";
                ?>
                </td>

		    </tr>
		<?php endforeach;?>
    </table>
</form>
