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
<!-- Modal submit unlock password -->
<div id="judl-enter-password" class="judl-enter-password modal fade" tabindex="-1" role="dialog"
     aria-labelledby="judl-enter-password-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form name="judl-submit-password-form" onsubmit="return false;" id="judl-submit-password-form"
			      class="form-horizontal" action="#"
			      method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="judl-enter-password-label" class="modal-title">
						<?php echo JText::_('COM_JUDOWNLOAD_DOCUMENT_DOWNLOAD_PASSWORD'); ?>
					</h3>
				</div>
				<div class="modal-body">
					<div id="show-alert">
					</div>
					<div class="form-group">
						<label class="control-label col-sm-2"
						       for="download-password"><?php echo JText::_('COM_JUDOWNLOAD_PASSWORD'); ?></label>

						<div class="col-sm-10">
							<input type="password" name="download_password" id="download-password" value=""/>
						</div>
					</div>
					<input type="hidden" name="doc_id" id="judl-docid-submit" value=""/>
					<input type="hidden" name="task" value="download.checkPasswordAjax"/>
					<input type="hidden" name="return" value="<?php echo base64_encode(urlencode(JUri::getInstance())); ?>"/>
					<input type="hidden" name="<?php echo $this->token; ?>" id="judl-token-download" value="1"/>
				</div>
				<div class="modal-footer">
					<a href="#" id="judl-submit-password" class="btn btn-default btn-primary judl-submit-password">
						<?php echo JText::_("COM_JUDOWNLOAD_SUBMIT"); ?>
					</a>
					<a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">
						<?php echo JText::_("COM_JUDOWNLOAD_CLOSE"); ?>
					</a>
				</div>
			</form>
		</div>
	</div>
</div>