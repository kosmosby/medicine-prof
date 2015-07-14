<?php 
	$widget_id  = $widget->id.'-'.uniqid();
	$settings   = $widget->settings;
	$content    = array();
	$item_width = floor(100 / count($widget->items));

?>

<div id="slideshow-<?php echo $widget_id; ?>" class="wk-slideshow wk-slideshow-pacetabs" data-widgetkit="slideshow" data-options='<?php echo json_encode($settings); ?>'>
	
	<div class="slides-container">

		<ul class="slides">
			<?php foreach ($widget->items as $key => $item) : ?>
			<?php $title = $item['navigation'] ? $item['navigation'] : $item['title']; ?>
			<?php $items[] = '<li class="width'. $item_width .'"><span>'. $title .'</span></li>'; ?>
			<li>
				<article class="wk-content clearfix"><?php echo $item['content']; ?></article>
			</li>
			<?php endforeach; ?>
		</ul>

		<?php echo (count($items)) ? '<div class="nav-container"><ul class="nav clearfix">'.implode('', $items).'</ul></div>' : '';?>

	</div>

</div>