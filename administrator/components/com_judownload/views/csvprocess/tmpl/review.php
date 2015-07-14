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
<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form method="post" name="adminForm" id="adminForm" class="form-validate">
		<fieldset>
			<legend><?php echo JText::_("COM_JUDOWNLOAD_FIELDS_MAPPING"); ?></legend>
			<table class="table">
				<thead>
				<tr>
					<th>
						<?php echo JText::_("COM_JUDOWNLOAD_CSV_COLUMNS"); ?>
					</th>
					<th>

					</th>
					<th>
						<?php echo JText::_("COM_JUDOWNLOAD_DOCUMENT_FIELDS"); ?>
					</th>
				</tr>
				</thead>
				<tbody>

				<?php
				foreach ($this->review['csv_columns'] AS $key => $column)
				{
					echo "<tr><td>" . $column . "</td> <td><i class=\"icon-arrow-right-4\"></i></td> <td>" . $this->review['csv_assigned_columns_name'][$key] . "</td></tr>";
				}
				?>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_("COM_JUDOWNLOAD_CSV_IMPORT_CONFIG"); ?></legend>
			<table class="table">
				<thead>
				<tr>
					<th>
						<?php echo JText::_('COM_JUDOWNLOAD_FIELD'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_JUDOWNLOAD_VALUE'); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php if (!empty($this->review['config']['save_options']))
				{
					?>
					<tr>
						<td>
							<?php echo JText::_("COM_JUDOWNLOAD_IF_DOCUMENT_EXISTED"); ?>
						</td>
						<td>
							<?php echo $this->review['config']['save_options'] ?>
						</td>

					</tr>
				<?php } ?>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_FIELD_CREATED_BY"); ?>
					</td>
					<td>
						<?php if(!empty($this->review['config']['created_by'])) echo JFactory::getUser($this->review['config']['created_by'])->name; ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_FORCE_PUBLISH"); ?>
					</td>
					<td>
						<?php
						if ($this->review['config']['force_publish'] == 1)
						{
							echo JText::_("JYES");
						}
						elseif ($this->review['config']['force_publish'] == 0)
						{
							echo JText::_("JNO");
						}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_SELECTED_MAIN_CATEGORY"); ?>
					</td>
					<td>
						<?php
							if($this->review['config']['main_cat_assign'])
							{
								$mainCat = $this->review['config']['main_cat_assign'];
								$catObj  = JUDownloadHelper::getCategoryById($mainCat);
								echo $catObj->title;
							}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_SELECTED_SECONDARY_CATEGORIES"); ?>
					</td>
					<td>
						<?php
						if (isset($this->review['config']['secondary_cats_assign']))
						{
							$catName = array();
							foreach ($this->review['config']['secondary_cats_assign'] AS $catId)
							{
								$catObj    = JUDownloadHelper::getCategoryById($catId);
								$catName[] = $catObj->title;
							}
							echo implode(', ', $catName);
						}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_META_KEYWORD"); ?>
					</td>
					<td>
						<?php
						echo $this->review['config']['meta_keyword'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_META_DESCRIPTION"); ?>
					</td>
					<td>
						<?php
						echo $this->review['config']['meta_description'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_CREATED"); ?>
					</td>
					<td>
						<?php
						echo $this->review['config']['created'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_PUBLISH_UP"); ?>
					</td>
					<td>
						<?php
						echo $this->review['config']['publish_up'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_PUBLISH_DOWN"); ?>
					</td>
					<td>
						<?php
						echo $this->review['config']['publish_down'];
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_("COM_JUDOWNLOAD_DEFAULT_IMAGE"); ?>
					</td>
					<td>
						<?php
						if (isset($this->review['config']['default_icon']))
						{
							echo "<img src='" . $this->review['config']['default_icon'] . "' width='50' height='50' />";
						}
						?>
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>

		<div>
			<input type="hidden" name="task" value="csvprocess.import" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>