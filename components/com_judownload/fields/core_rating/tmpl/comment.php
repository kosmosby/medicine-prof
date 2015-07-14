<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$commentObject   = $options->get('comment_object');
$classHasTooltip = '';
if (count($this->criteriaObjectList) > 0)
{
	$classHasTooltip = 'judl-tooltip';
}

$commentSelectedStar = round($commentObject->score * $this->totalStars / 10, 2);
$rating_stars        = $rating_proc = 0;
if ($commentSelectedStar > 0)
{
	$rating_stars = floor($commentSelectedStar);
	$rating_proc  = round(($commentSelectedStar - $rating_stars) * 100);
	?>
	<div itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
		<meta itemprop="worstRating" content="1">
		<span itemprop="ratingValue" class="hidden"><?php echo $commentSelectedStar; ?></span>
		<span itemprop="bestRating" class="hidden"><?php echo $this->totalStars; ?></span>
	</div>

	<?php
	$this->setAttribute("class", "judl-rating " . $classHasTooltip . " clearfix", "output");
	?>
	<div <?php echo $this->getAttribute(null, null, "output"); ?>>
		<div class="judl-rating judl-rating-result">
			<?php
			for ($star = 1; $star <= $this->totalStars; $star++)
			{
				$star_class = "";
				if ($star <= $rating_stars + 1)
				{
					$star_class = " star-rating-on";
				}
				?>
				<span class="star-rating<?php echo $star_class; ?> fa fa-star"
				      style="width:<?php echo $this->starWidth; ?>px; height:<?php echo $this->starWidth; ?>px; font-size:<?php echo $this->starWidth; ?>px;">
					<?php
					if ($star <= $rating_stars)
					{
						?>
						<span class="fa fa-star"></span>
					<?php
					}
					else
					{
						if ($rating_proc && $star == ceil($commentSelectedStar))
						{
							?>
							<span class="fa fa-star" style="width:<?php echo $rating_proc; ?>%"></span>
						<?php
						}
					} ?>
				</span>
			<?php
			} ?>
		</div>
	</div>
	<?php
	if (count($this->criteriaObjectList) > 0)
	{
		echo $this->fetch('comment_tooltip.php', $className);
	}
}
?>
