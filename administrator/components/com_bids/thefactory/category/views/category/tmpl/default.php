<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $doc= JFactory::getDocument();
    $doc->addScriptDeclaration(
        "
        	function delecat(nr,enable){
                el=document.getElementById('cat_new_'+nr);
                el.disabled=enable;
            }
            function showQuickAdd()
            {
                el=document.getElementById('quickadd');
                el.style.display='block';
                el=document.getElementById('quickaddbutton');
                el.style.display='none';
            }
         "
	);
    ?>
 <form action="index.php" method="post" name="adminForm">
 <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
 <input type="hidden" name="task" value="" />
 <input type="hidden" name="boxchecked" value="0" />
     <table class="adminlist" width="100%">
     <tr>
        <th width="30"><input type="checkbox" name="toggle" value="" onclick="checkAll('<?php echo count($this->categories);?>');" /></th>
        <th width="40">
            <a href="javascript:submitbutton('category.savecatorder')" class="saveorder" title="<?php echo JText::_('FACTORY_SAVE_ORDERING');?>" ></a>
        </th>
        <th width="25%"><?php echo JText::_('FACTORY_CATEGORIES'); ?></th>
        <th width="*%"><?php echo JText::_('FACTORY_DESCRIPTION'); ?></th>
        <th width="5%"><?php echo JText::_('FACTORY_STATUS'); ?></th>
     </tr>
     <?php foreach($this->categories as $row):?>
     <tr>
        <td><?php echo JHTML::_('grid.id',$row->id,$row->id,false);?></td>
        <td><input name='order_<?php echo $row->id;?>' type='text' class='inputbox' size='1' value='<?php echo $row->ordering;?>' /></td>
        <td><span style="padding-left: <?php echo $row->depth*20 ?>px;">
            <sup>|<span style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;</span></sup>
            <a href='index.php?option=<?php echo APP_EXTENSION;?>&task=category.editcat&cid=<?php echo $row->id;?>'><?php echo $row->catname;?></a>
            </span>
        </td>
        <td><?php echo $row->description;?></td>
        <td><?php 
    			if ($row->status ) {
    				$img = 'tick.png';
    				$alt = JText::_( 'FACTORY_PUBLISHED__UNPUBLISH' );
    				$task = "category.unpublish_cat";
    			} else {
    				$img = 'publish_x.png';
    				$alt = JText::_( 'FACTORY_UNPUBLISHED__PUBLISH' );
    				$task = "category.publish_cat";
    			}
    			
        
            ?>
               <span class="editlinktip hasTip" title="<?php echo JText::_( 'FACTORY_PUBLISH_INFORMATION')." ".$alt;?>" >
    		 	<a href="index.php?option=<?php echo APP_EXTENSION;?>&task=<?php echo $task;?>&cid[]=<?php echo $row->id;?>" >
                    <?php echo JHtml::_('image','admin/'.$img, null, NULL, $alt); ?>
                </span> 
            </td>
     </tr>
     <?php endforeach;?>
     </table>
 
	<div id="quickaddbutton">
		<button class="button"  onclick="showQuickAdd();" type="button"><?php echo JText::_('FACTORY_QUICKADD');?></button>
		</div>
		<div style="display:none;" id="quickadd"><br />
		<span style="font-size:14px;font-weight:bolder"><?php echo JText::_('FACTORY_TO_QUICK_ADD_CATEGORIES'); ?></span><br /><br />
		<textarea name="quickadd" cols="40" rows="10"></textarea><br />
		<button class="button" onclick="submitform('category.quickaddcat');"><?php echo JText::_('FACTORY_QUICKADD');?></button>
	</div>
</form>
