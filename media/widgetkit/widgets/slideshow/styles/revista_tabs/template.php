<?php 
	$widget_id = $widget->id.'-'.uniqid();
	$settings  = $widget->settings;
	$content   = array();
	$nav       = ($settings['navigation']) ? 'nav-'.$settings['navigation'] : '';

?>

<div id="slideshow-<?php echo $widget_id; ?>" class="wk-slideshow wk-slideshow-revista-tabs" data-widgetkit="slideshow" data-options='<?php echo json_encode($settings); ?>'>
	
	<div class="nav-container <?php echo $nav; ?> clearfix">
		<ul class="nav">
			<?php foreach ($widget->items as $key => $item) : ?>
			<?php $content[] = '<li><article class="wk-content clearfix">'.$item['content'].'</article></li>'; ?>
			<li>
				<span><?php echo $item['title']; ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	
	<div class="slides-container"><?php echo (count($content)) ? '<ul class="slides">'.implode('', $content).'</ul>' : '';?></div>
	
</div>