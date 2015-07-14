<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    jimport('joomla.html.pane');
    JHTML::_("behavior.tooltip");
    $tabs = JPane::getInstance('Tabs');
?>
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?> ">
	<input type="hidden" name="task" value="config.savesettings">
	<?php
	echo $tabs->startPane("configPane");
	foreach ($this->groups as $group){
		echo $tabs->startPanel($group->title,$group->name);
		$this->currentgroup=$group;
        echo $this->loadTemplate('fieldgroup');
		echo $tabs->endPanel();
	}
	echo $tabs->endPane();
	?>
</form>
