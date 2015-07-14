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
<div class="actions clearfix">
	<div class="general-actions">
	<?php if ($this->item->params->get('access-report'))
	{
		?>
		<span class="action-report">
			<?php echo '<a href="' . $this->item->report_link . '" title="' . JText::_('COM_JUDOWNLOAD_REPORT') . '" class="hasTooltip report-task btn btn-default"><i class="fa fa-warning"></i></a>'; ?>
		</span>
	<?php
	}
	if ($this->item->params->get('access-contact'))
	{
		?>
		<span class="action-contact">
			<?php echo '<a href="' . $this->item->contact_link . '" title="' . JText::_('COM_JUDOWNLOAD_CONTACT') . '" class="hasTooltip btn btn-default"><i class="fa fa-user"></i></a>'; ?>
		</span>
	<?php
	}

	if (!$this->item->is_subscriber)
	{
		?>
		<span class="action-subscribe">
			<?php echo '<a href="' . $this->item->subscribe_link . '" title="' . JText::_('COM_JUDOWNLOAD_SUBSCRIBE') . '" class="hasTooltip btn btn-default"><i class="fa fa-bookmark"></i></a>'; ?>
		</span>
	<?php
	}
	else
	{
		?>
		<span class="action-unsubscribe">
			<?php echo '<a href="' . $this->item->unsubscribe_link . '" title="' . JText::_('COM_JUDOWNLOAD_UNSUBSCRIBE') . '" class="hasTooltip btn btn-default"><i class="fa fa-bookmark-o"></i></a>'; ?>
		</span>
	<?php
	} ?>

	<span class="action-print">
		<?php
			$windowOpenSpecs = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			$onclick = "window.open(this.href, 'document_print', '" . $windowOpenSpecs . "'); return false;";
			echo '<a class="hasTooltip btn btn-default" title="' . JText::_('COM_JUDOWNLOAD_PRINT') . '" href="' . $this->item->print_link . '" rel="nofollow" onclick="' . $onclick . '"><i class="fa fa-print" ></i></a>';
		?>
	</span>

	<span class="action-mailtofriend">
		<a href="#judl-mailtofriend"
		   title="<?php echo JText::_('COM_JUDOWNLOAD_SEND_EMAIL_TO_FRIEND'); ?>" class="hasTooltip btn btn-default"
		   data-toggle="modal"><i class="fa fa-envelope"></i></a>
	</span>

	<div class="modal fade" id="judl-mailtofriend" tabindex="-1" role="dialog"
	     aria-labelledby="judl-mailtofriend-label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title"
					    id="judl-mailtofriend-label"><?php echo JText::_('COM_JUDOWNLOAD_SEND_EMAIL'); ?>
					</h3>
				</div>
				<div class="modal-body form-horizontal">
					<div class="message form-group hide">
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3" for="inputEmail">
							<?php echo JText::_('COM_JUDOWNLOAD_SEND_TO'); ?>
							<span style="color: red">*</span>
						</label>

						<div class="col-sm-9">
							<input id="inputEmail" type="text" name="to_email" size="32"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3" for="inputUsername">
							<?php echo JText::_("COM_JUDOWNLOAD_YOUR_NAME"); ?>
							<span style="color: red">*</span>
						</label>

						<div class="col-sm-9">
							<input id="inputUsername" type="text" name="name" <?php if (!$this->user->get('guest'))
							{
								echo 'readonly="readonly"';
							} ?> value="<?php echo $this->user->username; ?>" size="32"/>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3" for="inputYourEmail">
							<?php echo JText::_("COM_JUDOWNLOAD_YOUR_EMAIL"); ?>
							<span style="color: red">*</span>
						</label>

						<div class="col-sm-9">
							<input id="inputYourEmail" type="text" name="email" <?php if (!$this->user->get('guest'))
							{
								echo 'readonly="readonly"';
							} ?> value="<?php echo $this->user->email; ?>" size="32"/>
						</div>
					</div>
					<div>
						<input type="hidden" name="task" value="document.sendemail"/>
						<input type="hidden" name="tmpl" value="component"/>
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-default btn-primary" id="send_mail_button"
					        data-loading-text="<?php echo JText::_("COM_JUDOWNLOAD_LOADING"); ?>"><?php echo JText::_("COM_JUDOWNLOAD_SEND"); ?></button>
					<button class="btn btn-default" aria-hidden="true"
					        data-dismiss="modal"><?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?></button>
				</div>
			</div>
		</div>
	</div>

	<?php if ($this->collection_popup)
	{
		?>
		<span class="action-addcollection">
	            <a class="hasTooltip btn btn-default judl-add-collection"
	               title="<?php echo JText::_('COM_JUDOWNLOAD_ADD_TO_COLLECTIONS'); ?>">
		            <i class="fa fa-inbox"></i>
	            </a>
			</span>

		<div class="judl-collection-list" style="display: none;">
			<div class="collection-popup jubootstrap component judl-container">
				<ul class="collection-list">
					<?php
					foreach ($this->collections AS $collection)
					{
						$added = "";
						if ($collection->hasThisDoc)
						{
							$added = " added";
						}
						echo "<li class='collection-item'>
	                                <i class='add-to-collection fa fa-check" . $added . "' id='collection-" . $collection->id . "'></i>
	                                <a class='collection-item-popup' href=\"" . $collection->collection_link . "\">" . $collection->title . "</a>
	                            </li>";
					}
					?>
				</ul>
				<div class="create-new-collection">
					<a href="#create-collection-modal" data-toggle="modal" id="create-new-collection">
						<i class='fa fa-plus'></i>
						<?php echo JText::_("COM_JUDOWNLOAD_CREATE_A_NEW_COLLECTION"); ?>
					</a>
				</div>
			</div>
			<input type="hidden" name="token" value="<?php echo JSession::getFormToken(); ?>">
		</div>

		<div class="modal fade" id="create-collection-modal" tabindex="-1" role="dialog"
		     aria-labelledby="create-collection-modal-label" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 class="modal-title" id="create-collection-modal-label">
							<?php echo JText::_("COM_JUDOWNLOAD_CREATE_A_NEW_COLLECTION"); ?>
						</h3>
					</div>
					<form action="#" method="post" name="create-new-collection" class="form-horizontal">
						<div class="modal-body">
							<div class="form-group">
								<label class="control-label col-sm-2"><?php echo JText::_("COM_JUDOWNLOAD_FIELD_TITLE"); ?>
									<span style="color: red;">*</span></label>

								<div class="col-sm-10">
									<input type="text" name="title" size="53"/>
								</div>
							</div>

							<div class="form-group">
								<label
									class="control-label col-sm-2"><?php echo JText::_("COM_JUDOWNLOAD_FIELD_DESCRIPTION"); ?></label>

								<div class="col-sm-10">
									<textarea name="description" class="form-control" rows="3"></textarea>
								</div>
							</div>

							<div class="form-group">
								<label
									class="control-label col-sm-2"><?php echo JText::_("COM_JUDOWNLOAD_FIELD_PRIVATE"); ?></label>

								<div class="col-sm-10">
									<label><input type="radio" name="private"
									              value="1"/> <?php echo JText::_("COM_JUDOWNLOAD_ONLY_ME_CAN_VIEW_THIS_COLLECTION"); ?>
									</label>
									<label><input type="radio" name="private" value="0"
									              checked/> <?php echo JText::_("COM_JUDOWNLOAD_ANYONE_CAN_VIEW_THIS_COLLECTION"); ?>
									</label>
								</div>
							</div>

							<?php echo JHtml::_('form.token'); ?>
							<input type="hidden" name="doc_id" value="<?php echo $this->item->id; ?>"/>
						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-default btn-primary"
							       value="<?php echo JText::_("COM_JUDOWNLOAD_SUBMIT"); ?>"/>
							<input type="reset" class="btn btn-default" id="collection_form_reset"
							       value="<?php echo JText::_("COM_JUDOWNLOAD_RESET"); ?>"/>
						</div>
					</form>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- /.modal -->
	<?php
	} ?>
	</div>
</div>
<!-- /.actions -->