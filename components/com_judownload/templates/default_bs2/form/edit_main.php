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

echo '<div class="control-group">';
// Call field cat_id
if ($this->fieldCatid)
{
	echo '<div class="control-label">';
	echo $this->fieldCatid->getLabel();
	echo '</div>';
	echo '<div class="controls">';
	echo $this->fieldCatid->getModPrefixText();
	echo $this->fieldCatid->getInput(isset($this->fieldsData[$this->fieldCatid->id]) ? $this->fieldsData[$this->fieldCatid->id] : null);
	echo $this->fieldCatid->getModSuffixText();
	echo $this->fieldCatid->getCountryFlag();
	echo '</div>';
	// parse field using joomla field
}
else
{
	$fieldCatid = $this->form->getField("cat_id");
	echo $fieldCatid->getControlGroup();
}
// end call field cat id
echo '</div>';
?>

<?php
if ($this->fieldsetDetails)
{
	foreach ($this->fieldsetDetails AS $field)
	{
		// Load field from DB
		echo '<div class="control-group">';
		if (is_object($field))
		{
			echo '<div class="control-label">';
			echo $field->getLabel();
			echo '</div>';
			echo '<div class="controls">';
			echo $field->getModPrefixText();
			echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
			echo $field->getModSuffixText();
			echo $field->getCountryFlag();
			echo '</div>';
		}
		// Load field as XML joomla field
		else
		{
			$field = $this->form->getField($field);
			echo $field->getControlGroup();
		}
		echo "</div>";
	}
}
?>