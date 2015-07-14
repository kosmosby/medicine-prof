<?php
	$widget_id = $widget->id.'-'.uniqid();
	$settings  = $widget->settings;
	$images    = $this['gallery']->images($widget);
	$imgcount  = count($images);

	$i = 0;

	if ($settings["colwidth"]=="auto" && $imgcount) {
		if ($imgcount <=6) {
			$settings["colwidth"] =  floor(100/$imgcount);
		} else {
			if ($imgcount % 2) {
				if(($imgcount % 6)==0) {
					$settings["colwidth"] = 16;
				} elseif(($imgcount % 5)==0) {
					$settings["colwidth"] = 20;
				} elseif(($imgcount % 3)==0) {
					$settings["colwidth"] = 33;
				} else {
					$settings["colwidth"] = 33;
				}
			} else {
				if(($imgcount % 5)==0) {
					$settings["colwidth"] = 20;
				} elseif(($imgcount % 4)==0) {
					$settings["colwidth"] = 25;
				} elseif(($imgcount % 2)==0) {
					$settings["colwidth"] = 50;
				} else {
					$settings["colwidth"] = 20;
				}
			}
		}
	}

	$settings["colwidth"] = "width".$settings["colwidth"];

	$i = 0;
?>

<?php if ($imgcount) : ?>
<div id="gallery-<?php echo $widget_id; ?>" class="wk-gallery wk-gallery-glass" data-options='<?php echo json_encode($settings); ?>'>
	<div>
		<ul class="grid">

			<?php foreach ($images as $image) : ?>
            	<li class="<?php echo $settings["colwidth"];?>">
					<?php
						$lightbox     = '';

						/* Prepare Lightbox */
						if ($settings['lightbox'] && !$image['link']) {
							$lightbox = 'data-lightbox="group:'.$widget_id.'"';
						}
						
						/* Prepare Image */
						$content = '<div class="glass-gallery" style="background-image: url('.$image['cache_url'].'); height:'.$image['height'].'px;" ></div>';
						
						/* Lazy Loading */				
						$content = ($i==$settings['index']) ? $content : $this['image']->prepareLazyload($content);

						/* add caption */
						$content.= (strlen($image['caption']) ? '<div class="huhucaption">'.$image['caption'].'</div>':"")
					?>

					<?php if ($settings['lightbox'] || $image['link']) : ?>
						<a class="width100" href="<?php echo $image['link'] ? $image['link'] : $image['url']; ?>" <?php echo $lightbox; ?>><?php echo $content; ?></a>
					<?php else : ?>		
						<?php echo $content; ?>
					<?php endif; ?>
				</li>

				<?php $i=$i+1;?>
			<?php endforeach; ?>
			
		</ul>
	</div>
</div>
	
<?php else : ?>
	<?php echo "No images found."; ?>
<?php endif;