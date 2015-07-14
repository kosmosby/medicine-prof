<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG, Valérie Isaksen
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


if($this->manager('report')){



}

?>
<div id="cpanel">

	<?php if($this->manager('report')){
		?><div id="vm_stats_chart" style="width: 100%; height: 300px;"></div><?php
	} ?>

	<div class="clear"></div>
		<?php
		$totalItems=5;
		if ( $this->virtuemartFeed) {
			?>
			<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_LATEST_NEWS')?></h2>
			<ul class="newsfeed">
				<?php
				foreach ($this->virtuemartFeed as $item) {
					if (!empty($item->link)) {
						$description=strip_tags($item->description);
						$description=substr($description, 0,200)."...";
						?>
						<li class="newsfeed-item">
							<a href="<?php echo $item->link; ?>" target="_blank" title=" <?php echo $description; ?>"> <?php echo $item->title; ?></a>
						</li>
					<?php
					}
				}
					?>
				<li class="newsfeed-item" style="font-size: 100%;font-style: italic;">
					<button class="btn btn-small">
					<a href="http://virtuemart.net/news/list-all-news" target="_blank" title="<?php echo vmText::_('COM_VIRTUEMART_ALL_NEWS'); ?>"><?php echo vmText::_('COM_VIRTUEMART_ALL_NEWS'); ?></a>
					</button>
				</li>


			</ul>
			<a class="cpanel" style="display: block;" href="http://extensions.joomla.org/extensions/e-commerce/shopping-cart/129" target="_blank" title=" <?php echo vmText::_('COM_VIRTUEMART_VOTE_JED_DESC') ?>"> <?php echo vmText::_('COM_VIRTUEMART_VOTE_JED_DESC') ?></a>

		<?php
		}
		?>

	<?php
	if ( $this->extensionsFeed ) {
		$j=0;
		foreach ($this->extensionsFeed as $item){
			// This is directly related to extensions.virtuemart.net
			if (($j / 5) == 0) { ?>
				<div class="clear"></div>

				<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_LATEST_EXTENSION')?></h2>
				<?php
			} elseif (($j / 5) == 1) { ?>
				<div class="clear"></div>

				<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_FEATURED_EXTENSION')?></h2>
			<?php
			} elseif (($j / 5) == 2) { ?>
				<div class="clear"></div>
				<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_POPULAR_EXTENSION')?></h2>
			<?php
			}
			$image="";
			if (!empty($item->link)) {
				 $description = $item->description;
				preg_match('/<img[^>]+>/i',$description, $result);
				if (is_array($result) and isset($result[0])){
					$image=$result[0];
					$description=str_replace($image,"",$description);
					$description=strip_tags($description);
					$description=str_replace(vmText::_ ('COM_VIRTUEMART_FEED_READMORE') ,"",$description);
				} else {
					$description="";
				}
				?>
				<div class="icon vmextimg" >
					<a href="<?php echo $item->link; ?>" target="_blank" title="<?php echo $description ?>">
						<?php
						if ($image){
							echo  $image."<br />" ;
						}
						echo $item->title;
						?>
					</a>
				</div>
			<?php
			}
			$j++;
		} ?>

	<?php
	}
	?>
		<div class="clear"></div>
		<h2 class="cpanel" >
			<a href="http://extensions.virtuemart.net" target="_blank" title="<?php echo vmText::_('COM_VIRTUEMART_ALL_EXTENSIONS') ?>"> <?php echo vmText::_('COM_VIRTUEMART_ALL_EXTENSIONS') ?></a>
		</h2>

</div>
<div class="clear"></div>


