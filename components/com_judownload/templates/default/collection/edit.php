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
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'collection.cancel' || document.formvalidator.isValid(document.id('collection-form'))) {
			Joomla.submitform(task, document.getElementById('collection-form'));
		}
	};
</script>

<?php
if (is_object($this->item) && $this->item->id > 0)
{
	echo "<h2>" . JText::_("COM_JUDOWNLOAD_EDIT") . ": " . $this->item->title . "</h2>";
}
else
{
	echo "<h2>" . JText::_("COM_JUDOWNLOAD_CREATE_NEW_COLLECTION") . "</h2>";
}
?>
<div id="judl-container" class="jubootstrap component judl-container view-collection layout-edit judl-form"">
	<form
		action="<?php echo JRoute::_("index.php?option=com_judownload&layout=edit" . isset($this->item) && is_object($this->item) ? "&id=" . (int) $this->item->id : ""); ?>"
		method="post" name="collection-form" id="collection-form" class="form-validate form-horizontal form-search"
		enctype="multipart/form-data">
		<?php
		if(!$this->item || !$this->item->global)
		{
			echo JHtml::_('bootstrap.startTabSet', 'judownload', array('active' => 'details'));
			echo JHtml::_('bootstrap.addTab', 'judownload', 'details', JText::_('COM_JUDOWNLOAD_COLLECTION_DETAILS'));

			foreach ($this->form->getFieldset('details') AS $field)
			{
				?>
				<div class='form-group'>
					<div class="control-label col-sm-2">
						<?php echo $field->label; ?>
					</div>
					<div class="col-sm-10">
						<?php
						if ($field->fieldname == 'description')
						{
							$field->class = "form-control";
						}
						?>
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php
			}
			echo JHtml::_('bootstrap.endTab');
		}
		else
		{
			echo JHtml::_('bootstrap.startTabSet', 'judownload', array('active' => 'documents'));
		}
		echo JHtml::_('bootstrap.addTab', 'judownload', 'documents' , JText::_('COM_JUDOWNLOAD_DOCUMENTS'));
		?>
		<h4 class="text-right">
			<?php if (isset($this->items))
			{
				echo JText::plural("COM_JUDOWNLOAD_N_DOCUMENTS_IN_COLLECTION", count($this->items));
			} ?>
		</h4>

		<div class="collection-search-docs">
			<input type="text" id="search-document" class="input-large autosuggest" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_TYPE_TO_SEARCH_DOCUMENT'); ?>"/>
			<input type="hidden" id="document-id" name="document-id" value=""/>
			<input type="hidden" id="document-title" name="document-title" value=""/>
			<input type="hidden" id="document-icon" name="document-icon" value=""/>
			<input type="hidden" id="document-link" name="document-link" value=""/>
			<button type="submit" class="btn btn-default" id="add-doc-to-collection"><?php echo JText::_("COM_JUDOWNLOAD_ADD_DOCUMENT"); ?></button>
		</div>

		<div>
			<input type="hidden" id="pending" value=""/>
		</div>

		<table class="table table-striped table-bordered collection-docs-list" id="table-collection-items">
			<thead>
			<tr>
				<th class="center">
					<p class="text-center"><?php echo JText::_("COM_JUDOWNLOAD_DOCUMENTS"); ?></p>
				</th>
				<th class="center">
					<p class="text-center"><?php echo JText::_("COM_JUDOWNLOAD_FIELD_CREATED"); ?></p>
				</th>
				<th class="center">
					<p class="text-center"><?php echo JText::_("COM_JUDOWNLOAD_REMOVE"); ?></p>
				</th>
			</tr>
			</thead>

			<tbody id="table-collection-items-tbody">
			<?php
			if (count($this->items))
			{
				$i = 0;
				foreach ($this->items AS $item)
				{
					$specialFields = JUDownloadFrontHelperField::getFields($item, null, array("description", "created", "title", "cat_id", "icon"));
					?>
					<tr id="coll-item-row-<?php echo $i; ?>">
						<td>
							<?php
							$iconField = $specialFields['icon'];
							if (isset($iconField) && $iconField->canView())
							{
								?>
								<div class="collection-item-icon">
									<?php echo $iconField->getOutput(array('view' => "list")); ?>
								</div>
							<?php
							}
							?>
							<?php
							$titleField = $specialFields['title'];
							if (isset($titleField) && $titleField->canView())
							{
								echo $titleField->getDisplayPrefixText() . " " . $titleField->getOutput() . " " . $titleField->getDisplaySuffixText();
							}
							?>
							<input type="hidden" name="documents[]" value="<?php echo $item->id; ?>"/>
						</td>
						<td >
							<p class="text-center">
							<?php
							echo $item->createdAgo;
							?>
							</p>
						</td>
						<td>
							<p class="text-center"><i class="fa fa-trash-o btn-lg remove-doc" data-index="<?php echo $i; ?>"></i></p>
						</td>
					</tr>
					<?php
					$i++;
				}
			}
			?>
			</tbody>
		</table>
		<?php
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
		?>

		<div class="judl-submit-buttons">
			<button type="button" class="btn btn-default btn-primary" id="save-edit-collection-button">
				<i class="fa fa-save"></i> <?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
			</button>

			<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('collection.cancel')">
				<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>
			</button>
		</div>

		<div>
			<input type="hidden" name="doc-icon-width" value="<?php echo $this->doc_icon_width; ?>"/>
			<input type="hidden" name="doc-icon-height" value="<?php echo $this->doc_icon_height; ?>"/>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>