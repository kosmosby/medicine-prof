<?php
defined('_JEXEC') or die;

?>

<?php if (count($list) > 0) : ?>
	<ul class="newsflash line horizontal">
		<?php for ($i = 0, $n = count($list); $i < $n; $i ++) : ?>
		<?php $item = $list[$i]; ?>
		<li class="item <?php if ($i == $n - 1) echo 'last'; ?>">
			<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>
		</li>
		<?php endfor; ?>
	</ul>
<?php endif;