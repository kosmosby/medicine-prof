<?php
defined('_JEXEC') or die;

?>

<?php foreach ($list as $item) :?>
	<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>
<?php endforeach;