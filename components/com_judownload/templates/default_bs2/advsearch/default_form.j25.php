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

$app = JFactory::getApplication();
$dataSearch = $app->getUserState("com_judownload.advancedsearch.data", array());
?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		setTimeout(function () {
			$("#categories").trigger('change');
		}, 100);

		$("#search-sub-categories").change(function () {
			$("#categories").trigger('change');
		});

		$("#categories").change(function () {
			var catids = $(this).val().join(",");
			var search_sub_categories = $('#search-sub-categories').val();
			$.ajax({
				type: "POST",
				url: "index.php?option=com_judownload&task=advsearch.getFieldGroupsByCatIds&tmpl=component",
				data: { catids: catids, search_sub_categories: search_sub_categories},
				beforeSend: function (xhr) {
					$('#judl-field > dl > dt:eq(1)').removeClass('closed').addClass('open');
					$('#judl-field > dl > dt:gt(1)').hide().removeClass('open').addClass('closed');
					$('#judl-field dd.tabs:eq(0)').show();
					$('#judl-field dd.tabs:gt(0)').hide().find('[name^="fields["]').prop("disabled", true);
				}
			}).done(function (data) {
				if (data) {
					var fieldGroupIds = data.split(",");
					var index_arr = new Array();
					for (var i = 0; i < fieldGroupIds.length; i++) {
						if (fieldGroupIds[i] > 0) {
							var index = $('.fieldgroup-' + fieldGroupIds[i]).show().removeClass('open').addClass('closed');
							$('#judl-field > .current > dd.tabs').eq(index - 2).find('[name^="fields["]').prop("disabled", false);
						}
					}
				}
			});
		});
	});

	function clearForm() {
		adminForm = document.getElementById('adminForm');
		//adminForm.reset();
		elements = adminForm.elements;
		for (i = 0; i < elements.length; i++) {

			field_type = elements[i].type.toLowerCase();
			switch (field_type) {

				case "text":
				case "password":
				case "textarea":
				case "hidden":
					elements[i].value = '';
					break;

				case "radio":
				case "checkbox":
					if (elements[i].checked) {
						elements[i].checked = false;
					}
					break;

				case "select-one":
				case "select-multi":
					elements[i].selectedIndex = 0;
					break;

				default:
					break;
			}
		}
	}
</script>
<div id="splitterContainer">
	<div id="rightPane">
		<div class="inner-pane">
			<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=advsearch'); ?>" method="post"
			      name="adminForm" id="adminForm" class="form-horizontal">
				<div class="control-group">
					<div class="controls">
						<input class="btn btn-primary" type="submit" name="btn_search"
						       value="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?>"/>
						<input class="btn" type="button" name="sub_reset" onclick="clearForm()"
						       value="<?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label id="jform_condition-lbl" for="condition" class="control-label"
					       title=""><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_CONDITION'); ?></label>

					<div class="controls">
						<?php
						$defaultValue = isset($dataSearch['condition']) ? $dataSearch['condition'] : 1;
						?>
						<select name="condition" id="condition">
							<option
								value="1" <?php echo $defaultValue == 1 ? "selected" : "" ?>><?php echo JText::_('COM_JUDOWNLOAD_ALL'); ?></option>
							<option
								value="2" <?php echo $defaultValue == 2 ? "selected" : "" ?>><?php echo JText::_('COM_JUDOWNLOAD_ANY'); ?></option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label id="jform_categories-lbl" for="categories" class="control-label"
					       title=""><?php echo JText::_('COM_JUDOWNLOAD_CATEGORIES'); ?></label>

					<div class="controls">
						<?php
						$options = JUDownloadHelper::getCategoryOptions(1, true, false, true);
						$defaultValue = isset($dataSearch['categories']) ? $dataSearch['categories'] : 1;
						echo JHtml::_('select.genericList', $options, 'categories[]', 'multiple size="10"', 'value', 'text', $defaultValue);
						?>
					</div>
				</div>
				<div class="control-group">
					<label id="jform_subcategories-lbl" for="search-sub-categories" class="control-label"
					       title=""><?php echo JText::_('COM_JUDOWNLOAD_SUBCATEGORIES'); ?></label>

					<div class="controls">
						<?php
						$defaultValue = isset($dataSearch['search_sub_categories']) ? $dataSearch['search_sub_categories'] : "1";
						?>
						<select name="search_sub_categories" id="search-sub-categories">
							<option
								value="0" <?php echo $defaultValue === "0" ? "selected" : "" ?>><?php echo JText::_("JNO"); ?></option>
							<option
								value="1" <?php echo $defaultValue === "1" ? "selected" : "" ?>><?php echo JText::_("JYES"); ?></option>
						</select>
					</div>
				</div>

				<div id="judl-field">
					<?php
					echo JHtml::_('tabs.start', 'field');
					foreach ($this->groupFields AS $groupField)
					{
						echo JHtml::_('tabs.panel', $groupField->name, 'fieldgroup-' . $groupField->id);
						?>
						<?php
						foreach ($groupField->fields AS $field)
						{
							if($field->canSearch())
							{
								$value = isset($dataSearch['fields'][$field->id]) ? $dataSearch['fields'][$field->id] : "";
								?>
								<div class="control-group">
									<?php echo $field->getLabel(false); ?>
									<div class="controls">
										<?php
										echo $field->getDisplayPrefixText();
										echo $field->getSearchInput($value);
										echo $field->getDisplaySuffixText();
										?>
									</div>
								</div>
							<?php
							}
						}
						?>
					<?php
					}
					echo JHtml::_('tabs.end');
					?>
				</div>

				<div>
					<input type="hidden" name="task" value=""/>
					<input type="hidden" name="advancedsearch" value="2"/>
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>
		</div>
	</div>
</div>