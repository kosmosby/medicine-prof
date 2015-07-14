<?php 
	$widget_id = $widget->id.'-'.uniqid();
	$settings  = $widget->settings;
	$content   = array();
	$item_width = floor(100 / count($widget->items));

?>

<div id="slideshow-<?php echo $widget_id; ?>" class="wk-slideshow wk-slideshow-tabs-drive" data-widgetkit="slideshow" data-options='<?php echo json_encode($settings); ?>'>
	
	<div class="slides-container">

		<ul class="slides">
			<?php foreach ($widget->items as $key => $item) : ?>
			<?php $items[] = '<li class="width'. $item_width .'"><span>'.$item['title'].'</span></li>'; ?>
			<li>
				<article class="wk-content clearfix"><?php echo $item['content']; ?></article>
			</li>
			<?php endforeach; ?>
		</ul>

		<?php echo (count($items)) ? '<div class="nav-container clearfix"><ul class="nav">'.implode('', $items).'</ul></div>' : '';?>

	</div>

</div>