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

jimport('joomla.html.html');
JHtml::_('behavior.calendar');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('.judl-changelogs').changelogs();
	});
</script>

<fieldset class="adminform">
	<div id="judl-changelogs" class="judl-changelogs">
		<a href="#" class="btn btn-mini add_changelog" id="add_changelog" onclick="return false;">
			<i class="icon-new"></i> <?php echo JText::_('COM_JUDOWNLOAD_ADD_CHANGELOG'); ?>
		</a>
		<ul id="changelog-list" class="changelog-list">
			<?php
			if ($this->changeLogs)
			{
				foreach ($this->changeLogs AS $key => $changeLog)
				{
					?>
					<li>
						<div class="changelog-item">
							<span class="move"><i class="icon-menu"></i></span>
							<div class="row-fluid">
								<div class="changelog-version span4">
									<input class="input-medium" type="text" size="24" value="<?php echo $changeLog['version'] ?>" name="changelogs[<?php echo $key ?>][version]" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FIELD_VERSION'); ?>" />
								</div>
								<div class="changelog-date span5">
									<?php echo JHtml::_('judownloadadministrator.calendar', $changeLog['date'], "changelogs[$key][date]", 'changelog-date-' . $key, '%Y-%m-%d %H:%M:%S', 'size="25" class="input-medium" placeholder="' . JText::_('COM_JUDOWNLOAD_FIELD_DATE') . '"'); ?>
								</div>
								<div class="changelog-actions span3">
									<span class="<?php echo $changeLog['published'] ? 'publish' : 'unpublish'; ?> btn btn-mini" data-iconpublish="icon-publish" data-iconunpublish="icon-unpublish">
										<?php
										if($changeLog['published'])
										{
											?>
											<i class="icon-publish"></i><?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
										<?php
										}
										else
										{
											?>
											<i class="icon-unpublish"></i><?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
										<?php
										}?>
									</span>
									<input type="hidden" class="changelog-published-value" value="1" name="changelogs[<?php echo $key ?>][published]" />

									<span class="remove btn btn-mini" data-iconremove="icon-trash" data-iconunremove="icon-undo">
										<i class="icon-trash"></i><?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
									</span>
									<input type="hidden" class="changelog-remove-value" value="0" name="changelogs[<?php echo $key ?>][remove]" />
								</div>
							</div>

							<div class="row-fluid">
								<div class="changelog-description span12">
									<textarea placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DESCRIPTION'); ?>" rows="5" cols="50" name="changelogs[<?php echo $key ?>][description]"><?php echo $changeLog['description'] ?></textarea>
								</div>
								<input type="hidden" class="changelog-id-value" value="<?php echo $changeLog['id'] ?>" name="changelogs[<?php echo $key ?>][id]" />
							</div>
						</div>
					</li>
				<?php
				}
			}
			?>
		</ul>
	</div>
</fieldset>