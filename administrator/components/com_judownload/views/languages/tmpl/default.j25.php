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

JHtml::_('behavior.modal', 'a.modal');
?>
<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=languages'); ?>"
		method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<?php if (!$this->fileExisted)
			{
				?>
				<div class="alert alert-danger"><?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_FILE_DOES_NOT_EXIST_SAVE_TO_CREATE_NEW_FILE'); ?></div>
			<?php
			} ?>

			<?php if ($this->groupCanDoManage)
			{
				?>
				<div class="filter-search input-append pull-left">
					<label for="filter_search" class="filter-search-lbl element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" id="filter_search" class="input-medium"
						placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
						value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
						title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH_DESC'); ?>" />
					<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>

					<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
						onclick="document.id('filter_search').value='';this.form.submit();">
						<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>
			<?php
			} ?>

			<div class="filter-select">
				<?php if ($this->lang != 'en-GB')
				{
					?>
					<div class="pull-right">
						<label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_FILTER'); ?></label>
						<select name="filter" class="input-small" onchange="this.form.submit()">
							<?php echo JHtml::_('select.options', $this->filterArr, 'value', 'text', $this->filter); ?>
						</select>
					</div>
				<?php
				}

				if (count($this->fileArr) > 0)
				{
					?>
					<div class="pull-right">
						<label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_FILE'); ?></label>
						<select name="item" id="files" class="input-xlarge" onchange="this.form.submit()">
							<?php echo JHtml::_('select.options', $this->fileArr, 'value', 'text', $this->item); ?>
						</select>
					</div>
				<?php
				} ?>

				<div class="pull-right">
					<label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_LANGUAGE'); ?></label>
					<select name="lang" id="language" class="input-small" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->language, 'value', 'text', $this->lang); ?>
					</select>
				</div>

				<div class="pull-right">
					<label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_SITE'); ?></label>
					<select name="site" id="site" class="input-medium" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->siteArr, 'value', 'text', $this->site); ?>
					</select>
				</div>
			</div>
		</fieldset>

		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<td><?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_FILE_COMMENT'); ?></td>
				<td colspan="2"><textarea name="comment" cols="60" rows="6"><?php echo $this->comment ?></textarea></td>
				<td><a class="modal" href="<?php echo $this->share_link; ?>"
						title="<?php echo JText::_('COM_JUDOWNLOAD_SHARE_FILE'); ?>"
						rel="{handler: 'iframe', size: {x:800, y:450}}">
						<button class="btn btn-info">
							<i class="icon-mail"></i> <?php echo JText::_('COM_JUDOWNLOAD_SHARE_FILE'); ?></button>
					</a>
				</td>
			</tr>
			<tr>
				<th style="width:5%; text-align: left;"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_ID'); ?></th>
				<th style="width:25%; text-align: left;"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_KEY'); ?></th>
				<th style="width:30%; text-align: left;"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_ORIGINAL'); ?></th>
				<th style="width:40%; text-align: left;"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TRANSLATION'); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php
			$i = 1;
			foreach ($this->original AS $key => $value)
			{
				$subKey = $key;
				if (strlen($key) > 50)
				{
					$subKey = substr($key, 0, 50) . '...';
				}
				?>

				<tr class="row<?php echo $i % 2 ?>">
					<td style="text-align: left;"><?php echo($this->state->get('list.start') + $i); ?></td>
					<td style="text-align: left;"><?php echo '<span title="' . $key . '">' . $subKey . '</span>'; ?></td>
					<td style="text-align: left;"><?php echo $value; ?></td>
					<td>
						<?php
						$messege = '';
						if (isset($this->translation[$key]))
						{
							$translatedValue = $this->translation[$key];
							if (trim($translatedValue) == trim($value) && $this->lang != 'en-GB')
							{
								$messege = 'warning';
							}
						}
						else
						{
							$translatedValue = $value;
							$messege         = 'empty';
						}

						?>
						<input type="hidden" name="keys[]" value="<?php echo $key; ?>" />
						<input type="text" name="<?php echo $key; ?>" class="input-large" size="65" value="<?php echo htmlspecialchars($translatedValue); ?>" />

						<div class="pull-right">
							<a href="#" class="btn btn-mini btn-success save-language">
								<i class="icon-save"></i> <?php echo JText::_('COM_JUDOWNLOAD_SAVE'); ?>
							</a>
							<a href="#" class="btn btn-mini btn-danger remove-language">
								<i class="icon-remove"></i> <?php echo JText::_('COM_JUDOWNLOAD_REMOVE'); ?>
							</a>
						</div>

						<?php if ($messege == 'empty')
						{
							?>
							<span style="margin-left: 10px"><img class="img-tooltip" alt="<?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_STRING_DOES_NOT_EXIST'); ?>"
									data-toggle="tooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_STRING_DOES_NOT_EXIST'); ?>"
									src="<?php echo JUri::root(); ?>administrator/components/com_judownload/assets/img/error.png" /></span>
						<?php
						}
						elseif ($messege == 'warning')
						{
							?>
							<span style="margin-left: 10px"><img class="img-tooltip" alt="<?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_STRING_SAME_AS_ORIGINAL_STRING'); ?>"
									data-toggle="tooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_STRING_SAME_AS_ORIGINAL_STRING'); ?>"
									src="<?php echo JUri::root(); ?>administrator/components/com_judownload/assets/img/warning.png" /></span>
						<?php } ?>
					</td>
				</tr>

				<?php
				$i++;
			}?>
			</tbody>
			<tfoot>
			<?php if ($this->lang == 'en-GB'): ?>
				<tr>
					<td colspan="4">
						<a href="#" id="add-language" class="btn btn-mini btn-primary">
							<i class="icon-new"></i> <?php echo JText::_('COM_JUDOWNLOAD_ADD_LANGUAGE'); ?>
						</a>
					</td>
				</tr>
			<?php endif ?>
			<tr>
				<td colspan="4" style="text-align:center">
					<div class="pagination">
						<div class="limit">
							<?php echo JHtml::_('select.genericlist', $this->limitArr, 'limit',
								'style="width:70px" class="inputbox" onchange="this.form.submit()"', 'value', 'text', $this->state->get('list.limit'))?>
						</div>
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="limitstart" value="<?php echo $this->start ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>