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

$ratingScoreObjectList  = $this->getAllRatingScoresOfDocument($this->doc_id);
$statisticByNumberStars = $this->getStatisticByNumberStars($this->totalStars, $ratingScoreObjectList);
$totalVotingTimes       = count($ratingScoreObjectList);
?>
<div class="judl-tooltip-content" style="display:none">
	<div class="judl-document-rating-result">
		<?php
		for ($i = $this->totalStars; $i > 0; $i--)
		{
			$statisticForStar = $statisticByNumberStars[$i];
			?>
			<div class="rating-item">
				<div class="star-rating-title" style="text-align:left;">
					<?php echo JText::plural('COM_JUDOWNLOAD_N_STAR', $i); ?>
				</div>

				<div class="star-rating-full">
					<?php
					if ($totalVotingTimes > 0)
					{
						?>
						<div class="star-rating-percent"
						     style="width:<?php echo(($statisticForStar / $totalVotingTimes) * 100); ?>%;"></div>
					<?php
					}
					else
					{
						?>
						<div class="star-rating-percent" style="width:0%;"></div>
					<?php
					} ?>
				</div>

				<div class="star-rating-value" style="text-align:right;">
					<?php echo $statisticForStar; ?>
				</div>
			</div>
		<?php
		} ?>
	</div>
</div>