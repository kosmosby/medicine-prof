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
$fieldsData = $app->getUserState("com_judownload.edit.document.fieldsdata", array());
?>
<fieldset class="adminform">
	<ul id="field-lists" class="adminformlist">
		<?php
		if ($this->extraFields)
		{
			foreach ($this->extraFields AS $fieldGroupId => $fields)
			{
				echo "<li  id=\"fieldgroup-$fieldGroupId\" >";
				foreach ($fields AS $field)
				{
					echo "<div class=\"control-group\">";
					echo "<div class=\"control-label\">";
					echo $field->getLabel();
					echo "</div>";
					echo "<div class=\"controls\">";
					echo $field->getModPrefixText();
					echo $field->getInput(isset($fieldsData[$field->id]) ? $fieldsData[$field->id] : null);
					echo $field->getModSuffixText();
					echo $field->getCountryFlag();
					echo "</div>";
					echo "</div>";
				}
				echo "</li>";
			}
		}
		?>
	</ul>
</fieldset>