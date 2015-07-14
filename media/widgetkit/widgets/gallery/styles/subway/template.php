<?php
$widget_id = $widget->id.'-'.uniqid();
$settings  = $widget->settings;
$images    = $this['gallery']->images($widget);

$i = 0;
?>

<?php if (count($images)) : ?>
<div id="gallery-<?php echo $widget_id; ?>" class="wk-slideshow wk-slideshow-subway" data-widgetkit="slideshow" data-options='<?php echo json_encode($settings); ?>'>
	<div>
		<ul class="slides">

			<?php foreach ($images as $image) : ?>
            
				<?php
					$navigation[] = '<li><span></span></li>';
					$captions[]   = '<li>'.(strlen($image['caption']) ? $image['caption']:"").'</li>';
					$lightbox     = '';

					/* Prepare Lightbox */
					if ($settings['lightbox'] && !$image['link']) {
						$lightbox = 'data-lightbox="group:'.$widget_id.'"';
					}
					
					/* Prepare Image */
					$content = '<img src="'.$image['cache_url'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$image['filename'].'" />';
					
					/* Lazy Loading */				
					$content = ($i==$settings['index']) ? $content : $this['image']->prepareLazyload($content);
				?>

				<?php if ($settings['lightbox'] || $image['link']) : ?>
					<li><a class="" href="<?php echo $image['link'] ? $image['link'] : $image['url']; ?>" <?php echo $lightbox; ?>><?php echo $content; ?></a></li>
				<?php else : ?>		
					<li><?php echo $content; ?></li>
				<?php endif; ?>
				
				<?php $i=$i+1;?>
			<?php endforeach; ?>
			
		</ul>
        <?php if ($settings['buttons']): ?><a class="buttons" href="JavaScript:void(0);"><div class="prev"></div><div class="next"></div></a><?php endif; ?>
		<div class="caption"></div><ul class="captions"><?php echo implode('', $captions);?></ul>
	</div>
	<?php echo ($settings['navigation'] && count($navigation)) ? '<ul class="nav">'.implode('', $navigation).'</ul>' : '';?>
</div>
	
<?php else : ?>
	<?php echo "No images found."; ?>
<?php endif; ?>