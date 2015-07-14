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

$this->col_counter++;

$item_class_arr = array();
$item_class_arr[] = "judl-collection judl-collection-column" . $this->col_counter;
if ($this->item->featured)
{
	$item_class_arr[] .= "featured";
}
$item_class_arr[] .= $this->collection_column_class ? $this->collection_column_class : "";
$item_class_arr[] .= $this->view_mode == 2 ? "col-md-" . $this->collection_bootstrap_columns[$this->col_counter - 1] : "col-md-12";

$item_class = implode(" ", $item_class_arr);

$collection_grid_col = $this->collection_bootstrap_columns[$this->col_counter - 1];
?>
	<div class="<?php echo $item_class; ?>" data-list-class="col-md-12" data-grid-class="col-md-<?php echo $collection_grid_col; ?>">
		<?php

		if ($this->item->icon_url)
		{
			?>
			<div class="collection-icon">
				<a href="<?php echo $this->item->collection_link; ?>">
					<img src="<?php echo $this->item->icon_url; ?>"
					     alt="<?php echo $this->item->title; ?>"
					     style="max-width:<?php echo $this->width; ?>px; max-height:<?php echo $this->height; ?>px;">
				</a>
			</div>
		<?php
		}
		?>

		<!-- show collection meta -->
		<ul class="collection-meta">
			<li class="meta-date">
				<div class="caption"><span class="fa fa-calendar"></span></div>
				<div class="value">
					<?php echo $this->item->created; ?>
				</div>
			</li>
			<li class="meta-created-by">
				<div class="caption"><span class="fa fa-user"></span></div>
				<div class="value">
					<?php echo $this->item->user_name; ?>
				</div>
			</li>
		</ul>
		<!-- /.doc-meta -->

		<?php if ($this->item->can_edit || $this->item->can_delete)
		{
			?>
			<div class="private-actions btn-group pull-right">
				<a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#"><span class="fa fa-cog"></span> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<?php if ($this->item->can_edit)
					{
						?>
						<li>
							<a title="<?php echo JText::_('COM_JUDOWNLOAD_EDIT_COLLECTION'); ?>" href="<?php echo $this->item->edit_link ?>">
								<i class="fa fa-edit"></i> <?php echo JText::_('COM_JUDOWNLOAD_EDIT_COLLECTION'); ?>
							</a>
						</li>
					<?php
					}
					?>
					<?php if ($this->item->can_delete)
					{
						?>
						<li>
							<a title="<?php echo JText::_('COM_JUDOWNLOAD_DELETE_COLLECTION'); ?>" href="<?php echo $this->item->delete_link ?>">
								<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE_COLLECTION'); ?>
							</a>
						</li>
					<?php
					}
					?>
				</ul>
			</div>
		<?php
		}
		?>

		<!--Show title-->
		<h3 class="collection-title">
			<a href="<?php echo $this->item->collection_link; ?>">
				<?php echo $this->item->title; ?>
			</a>
		</h3>
		<!--END Show title-->

		<div class="collection-introtext">
			<?php
			echo $this->item->description;
			?>
		</div>

		<div class="collection-vote">
			<?php
			if ($this->item->can_vote)
			{
				$vote_btn_html = '';
				// Allow vote down, only show vote button if have not voted yet
				if ($this->params->get('collection_allow_vote_down', 1))
				{
					if (!$this->item->voted_value)
					{
						$vote_btn_html .= '<span id="collection-vote-' . $this->item->id . '" class="collection-vote-action btn-group">';
						$vote_btn_html .= '<button class="vote-up btn btn-default btn-xs" title="' . JText::_('COM_JUDOWNLOAD_VOTE_UP') . '" onclick="return false;">
												<i class="fa fa-thumbs-o-up"></i>
											</button>';
						$vote_btn_html .= '<button class="vote-down btn btn-default btn-xs" title="' . JText::_('COM_JUDOWNLOAD_VOTE_DOWN') . '" onclick="return false;">
												<i class="fa fa-thumbs-o-down"></i>
											</button>';
						$vote_btn_html .= '</span>';
					}
				}
				// Not allow vote down(only like or unlike)
				else
				{
					$vote_btn_html .= '<span id="collection-vote-' . $this->item->id . '" class="collection-vote-action">';
					// If have dislike, or have not voted yet -> show like button
					if ($this->item->voted_value == -1 || !$this->item->voted_value)
					{
						$vote_btn_html .= '<button class="vote-up btn btn-default btn-xs" onclick="return false;">
												<i class="fa fa-thumbs-o-up"></i> ' . JText::_('COM_JUDOWNLOAD_LIKE') . '
											</button>';
						$vote_btn_html .= '<button class="vote-down btn btn-default btn-xs" onclick="return false;" style="display: none">
												<i class="fa fa-times"></i> ' . JText::_('COM_JUDOWNLOAD_UNLIKE') . '
											</button>';
					}
					// If have like, show unlike button
					elseif ($this->item->voted_value == 1)
					{
						$vote_btn_html .= '<button class="vote-down btn btn-default btn-xs" onclick="return false;">
												<i class="fa fa-times"></i> ' . JText::_('COM_JUDOWNLOAD_UNLIKE') . '
											</button>';
						$vote_btn_html .= '<button class="vote-up btn btn-default btn-xs" onclick="return false;" style="display: none">
												<i class="fa fa-thumbs-o-up"></i> ' . JText::_('COM_JUDOWNLOAD_LIKE') . '
											</button>';
					}
					$vote_btn_html .= '</span>';
				}

				echo $vote_btn_html;
			}
			
			echo '<span class="vote-result">';
			echo JText::sprintf('COM_JUDOWNLOAD_N_HELPFUL_VOTES_N_TOTAL_VOTES', $this->item->helpful_votes, $this->item->total_votes);
			echo '</span>';
			?>
		</div>
		
	</div>
	<!--end div.judl-collection -->
<?php
if ((($this->col_counter % $this->collection_columns) == 0) && (($this->index + 1) < count($this->items)))
{
	$this->row_counter += 1;
	$this->col_counter = 0;
	?>
	</div>
	<!--end div.judl-collection-row -->
	<div
	class="judl-collection-row <?php echo $this->collection_row_class; ?> judl-collection-row-<?php echo $this->row_counter + 1; ?> row">
<?php
}
?>