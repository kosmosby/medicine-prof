<?php defined('_JEXEC') or die('Restricted access'); ?>
<h2><?php echo JText::_('FACTORY_INSTALLED_PLUGINS');?></h2>

<form action="index.php" method="post" name="adminForm" >
  <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
  <input type="hidden" name="task" value="integrationcb.save"/>
    <table>
    <?php foreach($this->plugins as $k=>$plugin):?> 
    <tr>
        <td>
            <b><?php echo $plugin;?></b>
        </td>
    </tr>
    <?php endforeach;?>
    </table>
</form>
