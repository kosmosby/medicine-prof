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
<fieldset class="adminform">
	<ul class="adminformlist">
		<!--CALL FIELD CAT ID-->
		<?php
			echo "<li>";
			if ($this->fieldCatid)
			{
				echo $this->fieldCatid->getLabel();
				echo $this->fieldCatid->getModPrefixText();
				echo $this->fieldCatid->getInput(isset($this->fieldsData[$this->fieldCatid->id]) ? $this->fieldsData[$this->fieldCatid->id] : null);
				echo $this->fieldCatid->getModSuffixText();
				echo $this->fieldCatid->getCountryFlag();
				
			}
			else
			{
				$fieldCatid = $this->form->getField("cat_id");
				echo $fieldCatid->label;
				echo $fieldCatid->input;
			}
			echo "</li>";
		?>
		<!--END CALL FIELD CAT ID-->

		<!--CALL MORE FIELD-->
		<?php
		if ($this->fieldsetDetails)
		{
			foreach ($this->fieldsetDetails AS $field)
			{
				
				echo "<li>";
				if (is_object($field))
				{
					echo $field->getLabel();
					echo $field->getModPrefixText();
					echo $field->getInput(isset($this->fieldsData[$field->id]) ? $this->fieldsData[$field->id] : null);
					echo $field->getModSuffixText();
					echo $field->getCountryFlag();
				}
				
				else
				{
					$field = $this->form->getField($field);
					echo $field->label;
					echo $field->input;
				}
				echo "</li>";
			}
		}
		?>
	</ul>
</fieldset>