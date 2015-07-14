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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$searchword = trim(JFactory::getApplication()->input->get('searchword', '', 'string'));
?>
<script type="text/javascript">
	function doc_isChecked(isitchecked) {
		if (isitchecked == true) {
			document.adminForm.doc_boxchecked.value++;
		}
		else {
			document.adminForm.doc_boxchecked.value--;
		}
	}

	function doc_checkAll(n) {
		var f = document.adminForm;
		var c = f.doc_toggle.checked;
		var n2 = 0;
		for (i = 0; i < n; i++) {
			lb = eval('f.doc' + i);
			if (lb) {
				lb.checked = c;
				n2++;
			}
		}
		if (c) {
			document.adminForm.doc_boxchecked.value = n2;
		} else {
			document.adminForm.doc_boxchecked.value = 0;
		}
	}

	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == "documents.delete" || pressbutton == "documents.moveDocuments" || pressbutton == "documents.copyDocuments") {
			if (document.adminForm.doc_boxchecked.value == 0) {
				alert("<?php echo JText::_('COM_JUDOWNLOAD_PLEASE_FIRST_MAKE_A_SELECTION_FROM_THE_LIST'); ?>");
				return false;
			}
		}

		if (pressbutton == "categories.delete") {
			var r = confirm('<?php echo JText::_("COM_JUDOWNLOAD_WARRING_WHEN_DELETE_CATEGORIES"); ?>');
			if (r == false) {
				return false;
			}

		}

		if (pressbutton == "documents.delete") {
			var r = confirm('<?php echo JText::_("COM_JUDOWNLOAD_WARRING_WHEN_DELETE_DOCUMENTS"); ?>');
			if (r == false) {
				return false;
			}

		}

		Joomla.submitform(pressbutton);
	};
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu('manager-listcat'); ?>

	<div id="iframe-help"></div>

	<div id="splitterContainer">
		<div id="leftPane">
			<div class="inner-pane">
				<?php echo $this->loadTemplate('left'); ?>
			</div>
		</div>

		<div id="rightPane">
			<div class="list-item">
				<form
					action="<?php echo JRoute::_('index.php?option=com_judownload'); ?>"
					method="post" name="adminForm" id="adminForm">
					<?php
					echo $this->loadTemplate('documents');
					?>
					<div>
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="view" value="searchdocuments" />
						<input type="hidden" name="doc_boxchecked" value="0" />
						<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
						<input type="hidden" name="searchword" value="<?php echo $searchword; ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>