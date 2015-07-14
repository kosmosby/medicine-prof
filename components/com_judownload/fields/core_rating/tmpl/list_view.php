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

$classHasTooltip = '';
if ($this->selectedStar > 0
	&& ($this->juparams->get('rating_statistic', '') == 'bynumberstars' || count($this->criteriaObjectList) > 0)
	&& $this->doc->total_votes >= $this->juparams->get('min_rates_to_show_rating', 0)
)
{
	$classHasTooltip = 'judl-tooltip';
}

$rating_stars = $rating_proc = 0;
if ($this->selectedStar > 0)
{
	$rating_stars = floor($this->selectedStar);
	$rating_proc  = round(($this->selectedStar - $rating_stars) * 100);
}
?>
	<div itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating">
		<meta itemprop="worstRating" content="1">
		<span itemprop="ratingValue" class="hidden"><?php echo $this->selectedStar; ?></span>
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
				      style="width:<?php echo $this->starWidth; ?>px; height:<?php echo $this->starWidth; ?>px;
					      font-size:<?php echo $this->starWidth; ?>px;">
			<?php
			if ($this->doc->total_votes >= $this->juparams->get('min_rates_to_show_rating', 0))
			{
				if ($star <= $rating_stars)
				{
					?>
					<span class="fa fa-star"></span>
				<?php
				}
				elseif ($rating_proc && $star == ceil($this->selectedStar))
				{
					?>
					<span class="fa fa-star" style="width:<?php echo $rating_proc; ?>%"></span>
				<?php
				}
			}
			?>
		</span>
			<?php
			}
			?>
		</div>
	</div>
<?php
if ($classHasTooltip)
{
	if ($this->juparams->get('rating_statistic', '') == 'bynumberstars')
	{
		echo $this->fetch('list_view_tooltip_stars.php', $className);
	}
}