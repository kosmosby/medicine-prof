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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<script type="text/javascript">

	var taskSubmit = null;

	Joomla.submitbutton = function(task)
	{
		if (task == 'plugins.delete')
		{
			taskSubmit = task;
			var pluginIDs = [];

			jQuery("#adminForm input:checkbox:checked[id^='cb']").map(function(){
				pluginIDs.push(jQuery(this).val());
			});

			var objPost = {};
			objPost.pluginIDs = pluginIDs;

			jQuery.ajax({
				type: "POST",
				url : "index.php?option=com_judownload&task=plugins.checkDeleteTemplateHasChild",
				data: objPost
			}).done(function (data) {
				var data = jQuery.parseJSON(data);

				if (data) {
					if(data.status == 1){
						jQuery('#alertTemplateModal .modal-body').html(data.message);
						jQuery('#alertTemplateModal').modal();
					}else{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
			});

		}else{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};

	jQuery(document).ready(function($){
		$('#confirmDeleteTemplate').on('click',function(e){
			e.preventDefault();
			var task = taskSubmit;
			Joomla.submitform(task, document.getElementById('adminForm'));
		});
	});
</script>

<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=plugins'); ?>"
	method="post" name="adminForm" id="adminForm">

	<div id="alertTemplateModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="alertTemplateModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="alertTemplateModalLabel"><?php echo JText::_('COM_JUDOWNLOAD_DELETE_TEMPLATE_WARNING'); ?></h3>
		</div>
		<div class="modal-body">

		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
			<button id="confirmDeleteTemplate" class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_DELETE'); ?></button>
		</div>
	</div>

	<div id="j-main-container" class="span12">
		<?php
		
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_JUDOWNLOAD_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped adminlist" id="data-list">
				<thead>
				<tr>
					<th style="width:2%" class="center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th style="width:15%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'plg.title', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_TYPE', 'plg.type', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_AUTHOR', 'plg.author', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_EMAIL', 'plg.email', $listDirn, $listOrder); ?>
					</th>
					<th style="width:15%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_WEBSITE', 'plg.website', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_DATE', 'plg.date', $listDirn, $listOrder); ?>
					</th>
					<th style="width:5%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_VERSION', 'plg.version', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_FOLDER', 'plg.folder', $listDirn, $listOrder); ?>
					</th>
					<th style="width:5%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_CORE', 'plg.core', $listDirn, $listOrder); ?>
					</th>
					<th style="width:5%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_DEFAULT', 'plg.default', $listDirn, $listOrder); ?>
					</th>
					<th style="width:3%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'plg.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
				<tr>
					<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				foreach ($this->items AS $i => $item) :
					$canEdit    = $user->authorise('core.edit',       'com_judownload') && $this->groupCanDoManage;
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone">
							<?php $show = $item->default; ?>
							<?php echo JHtml::_('grid.id', $i, $item->id, $show); ?>
						</td>
						<td>
							<?php
							if ($item->checked_out)
							{
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'plugins.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
							}
							?>
							<?php if ($canEdit)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=plugin.edit&id=' . $item->id); ?>"><?php echo $item->title; ?></a>
							<?php
							}
							else
							{
								echo $item->title;
							} ?>
						</td>
						<td>
							<?php echo $item->type; ?>
						</td>
						<td>
							<?php echo $item->author; ?>
						</td>
						<td>
							<?php echo $item->email; ?>
						</td>
						<td>
							<?php echo $item->website; ?>
						</td>
						<td>
							<?php echo $item->date; ?>
						</td>
						<td>
							<?php echo $item->version; ?>
						</td>
						<td>
							<?php echo $item->folder; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_("grid.boolean", $i, $item->core); ?>
						</td>
						<td class="center">
							<?php echo JHtml::_("grid.boolean", $i, $item->default); ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>