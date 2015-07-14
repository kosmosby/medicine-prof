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
      class="filter-form judl-form"
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
			<input class="btn btn-primary" type="submit" name="btn_search"
			       value="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?>"/>
			<input class="btn" type="submit" name="sub_reset" onclick="document.getElementById('reset').value = 1;"
			       value="<?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>"/>
			<input type="hidden" name="reset" id="reset" value="0"/>
		</div>

		<?php echo JHtml::_('tabs.start', 'filter-field'); ?>
		<?php
		foreach ($this->fieldGroups AS $fieldGroup)
		{
			echo JHtml::_('tabs.panel', $fieldGroup->name, 'fieldgroup-' . $fieldGroup->id);
			?>
			<ul class="adminformlist">
				<?php
				foreach ($fieldGroup->fields AS $field)
				{
					echo "<li>";
					$defaultValue = isset($dataSearch[$field->id]) ? $dataSearch[$field->id] : "";
					echo $field->getLabel(false);
					echo $field->getDisplayPrefixText();
					echo $field->getSearchInput($defaultValue);
					echo $field->getDisplaySuffixText();
					echo "</li>";
				}
				?>
			</ul>
		<?php
		}
		echo JHtml::_('tabs.end');
		?>
	<?php
	}
	?>
</form>
