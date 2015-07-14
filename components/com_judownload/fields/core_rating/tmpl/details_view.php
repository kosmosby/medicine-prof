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
?>
	<div itemscop="" itemtype="http://schema.org/Article">
		<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
			<span itemprop="ratingValue" class="hidden"><?php echo round($this->selectedStar, 2); ?></span>
			<span itemprop="bestRating" class="hidden"><?php echo $this->totalStars; ?></span>
			<span itemprop="ratingCount" class="hidden"><?php echo $this->doc->total_votes; ?></span>
		</div>
	</div>
<?php
$classHasTooltip = '';
if ($this->selectedStar > 0
	&& ($this->juparams->get('rating_statistic', '') == 'bynumberstars' || count($this->criteriaObjectList) > 0)
	&& $this->doc->total_votes >= $this->juparams->get('min_rates_to_show_rating', 0)
)
{
	$classHasTooltip = 'judl-tooltip';
}

$this->setAttribute("class", "judl-rating " . $classHasTooltip . " clearfix", "output");
?>
	<div <?php echo $this->getAttribute(null, null, "output"); ?>>
		<?php
		
		if ($this->canRateDocument && $this->criteriaGroupId == 0)
		{
			?>
			<div class="judl-rating-action">
				<fieldset class="fieldset required radio" id="<?php echo $prefixId; ?>fieldset-criteria">
					<?php
					$inputClass    = 'judl-single-rating star {split:' . $this->starParts . '} required';
					$scorePerInput = $this->scoreIncrement;
					for ($count = 1; $count <= $this->totalInputs; $count++)
					{
						if (round($this->doc->rating) == round($scorePerInput) && $this->doc->total_votes >= $this->juparams->get('min_rates_to_show_rating', 0))
						{
							$checked = 'checked="checked"';
						}
						else
						{
							$checked = '';
						}
						?>
						<input name="<?php echo 'judl-document-rating-result-' . $this->doc_id; ?>"
						       type="radio" <?php echo $checked; ?>
						       title="<?php echo $this->ratingExplanation[$count]; ?>"
						       class="<?php echo $inputClass; ?>"
						       value="<?php echo $scorePerInput; ?>"/>
						<?php
						$scorePerInput += $this->scoreIncrement;
						if ($scorePerInput > 10)
						{
							$scorePerInput = 10;
						}
					}
					?>
				</fieldset>
				<input type="hidden" name="<?php echo $this->token; ?>" id="judl-single-rating-token" value="1"/>
			</div>
		<?php
		}
		
		else
		{
			?>
			<div class="judl-rating judl-rating-result">
				<?php
				$rating_stars = $rating_proc = 0;
				if ($this->selectedStar > 0)
				{
					$rating_stars = floor($this->selectedStar);
					$rating_proc  = round(($this->selectedStar - $rating_stars) * 100);
				}

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
		<?php
		}
		?>
	</div>

<?php

if ($classHasTooltip)
{
	if ($this->juparams->get('rating_statistic', '') == 'bynumberstars')
	{
		echo $this->fetch('details_view_tooltip_stars.php', $className);
	}
}