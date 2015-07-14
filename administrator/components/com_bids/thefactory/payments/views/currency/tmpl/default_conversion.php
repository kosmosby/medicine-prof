<?php defined('_JEXEC') or die('Restricted access'); ?>
<h1><?php echo JText::_("FACTORY_REFRESHING__CURRENCY_CONVERSION_RATES");?></h1>
<div><h2><?php echo JText::_("FACTORY_USING_GOOGLE_CONVERSION");?></h2></div>
<div><h3><?php echo JText::_("FACTORY_DEFAULT_CURRENCY"),": $this->default_currency";?></h3></div>

<ul>
    <?php foreach($this->results as $res):?>
    <li><?php echo $res;?></li>
    <?php endforeach;?>
</ul>
<form name="adminForm" action="index.php" method="get">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="currencies.listing" />
<input type="hidden" name="boxchecked" value="" />
</form>
