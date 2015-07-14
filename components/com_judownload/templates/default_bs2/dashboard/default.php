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
<div id="judl-container" class="jubootstrap component judl-container view-dashboard">
	<div id="judl-dashboard" class="judl-dashboard">
		<?php
			echo $this->loadTemplate('toolbar');
		?>
		<div class="quick-box-wrapper">
			<div class="quick-box">
				<div class="quick-box-head">
					<div class="quick-box-title"><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_OVERVIEW'); ?></div>
				</div>
				<div class="quick-box-body clearfix">
					<ul class="stat-list">
						<li>
							<span class="stat-info"><?php echo $this->totalDocuments ?></span>
							<span> <a
									href="<?php echo $this->link_user_documents; ?>"><?php echo JText::_('COM_JUDOWNLOAD_USER_DOCUMENTS'); ?></a></span>
						</li>
						<li>
							<span class="stat-info"><?php echo $this->totalPublishedDocuments; ?></span>
							<span> <a
									href="<?php echo $this->link_user_published_documents; ?>"><?php echo JText::_('COM_JUDOWNLOAD_PUBLISHED_DOCUMENTS'); ?></a></span>
						</li>
						<?php
						if ($this->params->get('document_owner_can_view_unpublished_document', 0))
						{
							?>
							<li>
								<span class="stat-info"><?php echo $this->totalUnPublishedDocuments; ?></span>
								<span> <a
										href="<?php echo $this->link_user_unpublished_documents; ?>"><?php echo JText::_('COM_JUDOWNLOAD_UNPUBLISHED_DOCUMENTS'); ?></a></span>
							</li>
						<?php
						} ?>
						<li>
							<span class="stat-info"><?php echo $this->totalPendingDocuments; ?></span>
							<span> <a
									href="<?php echo $this->approvedDocument; ?>"><?php echo JText::_('COM_JUDOWNLOAD_OWNER_PENDING_DOCUMENTS'); ?></a></span>
						</li>
						<li>
							<span class="stat-info"><?php echo $this->totalComments ?></span>
							<span> <a
									href="<?php echo $this->comment; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENTS'); ?></a></span>
						</li>
						<li>
							<span class="stat-info"><?php echo $this->totalCollections ?></span>
							<span> <a
									href="<?php echo $this->collections; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COLLECTIONS'); ?></a></span>
						</li>
						<li>
							<span class="stat-info"><?php echo $this->totalSubscriptions ?></span>
							<span> <a
									href="<?php echo $this->subscriptions; ?>"><?php echo JText::_('COM_JUDOWNLOAD_SUBSCRIPTIONS'); ?></a></span>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php
		$isModerator = JUDownloadFrontHelperModerator::isModerator();
		if ($isModerator)
		{
			?>
			<div id="quick-box-wrapper">
				<div class="quick-box">
					<div class="quick-box-head">
						<div class="quick-box-title"><?php echo JText::_('COM_JUDOWNLOAD_MODERATOR_AREA'); ?></div>
					</div>
					<div class="quick-box-body clearfix">
						<ul class="stat-list">

							<li>
								<span class="stat-info"><?php echo $this->total_document_mod_can_view; ?></span>
								<span> <a
										href="<?php echo $this->documents_link; ?>"><?php echo JText::_('COM_JUDOWNLOAD_DOCUMENTS'); ?></a></span>
							</li>

							<li>
								<span class="stat-info"><?php echo $this->total_document_mod_can_approval; ?></span>
								<span> <a
										href="<?php echo $this->unapproved_documents_link; ?>"><?php echo JText::_('COM_JUDOWNLOAD_PENDING_DOCUMENTS'); ?></a></span>
							</li>

							<li>
								<span class="stat-info"><?php echo $this->total_comments_mod_can_manage; ?></span>
								<span> <a
										href="<?php echo $this->comments_link; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENTS'); ?></a></span>
							</li>
							<li>
								<span class="stat-info"><?php echo $this->total_comments_mod_can_approval; ?></span>
								<span> <a
										href="<?php echo $this->unapproved_comments_link; ?>"><?php echo JText::_('COM_JUDOWNLOAD_PENDING_COMMENTS'); ?></a></span>
							</li>
							<li>
								<span>
									<a class="btn btn-mini"
									   href="<?php echo JRoute::_('index.php?option=com_judownload&view=modpermissions'); ?>">
										<i class="fa fa-shield"></i> <?php echo JText::_('COM_JUDOWNLOAD_MODERATOR_PERMISSIONS'); ?>
									</a>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		<?php
		} ?>
	</div>
</div>