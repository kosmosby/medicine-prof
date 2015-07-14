<?php
defined('_JEXEC') or die;

?>

<?php if (!empty($list)) :?>
<ul class="line line-icon">
<?php foreach ($list as $item) : ?>
	<li><a href="<?php echo $item->link; ?>"><?php echo $item->text; ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif;