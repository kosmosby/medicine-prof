<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
		* "License") +  you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/
defined('_JEXEC') or die;

?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend>
				<?php echo JText::_('COM_OPENMEETINGS_ADMIN_ADD_OMROOM'); ?>
			</legend>
			<!-- Fields go here -->
			<dl>
				<?php
				// Iterate through the fields and display them.
				foreach($this->form->getFieldset($fieldset->name) as $field) {
				// If the field is hidden, only use the input.
					if ($field->hidden) {
						echo $field->input;
					} else {
				?>
				<dt>
					<?php echo $field->label; ?>
				</dt>
				<dd
				<?php echo ($field->type == 'Editor' || $field->type == 'Textarea') ? ' style="clear: both; margin: 0;"' : ''?>>
					<?php echo $field->input ?>
				</dd>
				<?php
					}
				}
				?>

			</dl>

		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="clr"></div>
	<input type="hidden" name="option"
		value="<?php echo JRequest::getVar( 'option' ); ?>" /> <input
		type="hidden" name="id"
		value="<?php $cid = JRequest::getVar( 'cid', 0 ); if (!$cid) { echo 0; } else { echo $cid[0]; } ?>" />

</form>

