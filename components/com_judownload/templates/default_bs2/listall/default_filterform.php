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

$dataSearch = $this->state->get('filter.dataSearch', array());
?>
<form action="#" method="post"
      name="judl-filter-form"
      id="judl-filter-form"
      class="filter-form judl-form form-horizontal"
      style="<?php echo !$dataSearch ? 'display: none' : ''; ?>">
	<?php if (!$this->fieldGroups)
	{
		?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_JUDOWNLOAD_NO_FILTER'); ?>
		</div>
	<?php
	}
	else
	{
		?>
		<hr/>
		<div class="actions">
			<input class="btn btn-default btn-primary" type="submit" name="btn_search"
			       value="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?>"/>
			<input class="btn btn-default" type="submit" name="sub_reset" onclick="document.getElementById('reset').value = 1;"
			       value="<?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>"/>
			<input type="hidden" name="reset" id="reset" value="0"/>
		</div>

		<?php 
		echo JHtml::_('bootstrap.startTabSet', 'filter-field', array('active' => 'fieldgroup-1')); 
		foreach ($this->fieldGroups AS $fieldGroup)
		{
			echo JHtml::_('bootstrap.addTab', 'filter-field', 'fieldgroup-' . $fieldGroup->id, $fieldGroup->name);

				foreach ($fieldGroup->fields AS $field)
				{
					echo '<div class="form-group">';
					$defaultValue = isset($dataSearch[$field->id]) ? $dataSearch[$field->id] : "";
					echo $field->getLabel(false);
					echo '<div class="col-sm-10">';
					echo $field->getDisplayPrefixText();
					echo $field->getSearchInput($defaultValue);
					echo $field->getDisplaySuffixText();
					echo "</div>";
					echo "</div>";
				}

				echo JHtml::_('bootstrap.endTab');
		}
		echo JHtml::_('bootstrap.endTabSet');
		?>
	<?php
	}
	?>
</form>
