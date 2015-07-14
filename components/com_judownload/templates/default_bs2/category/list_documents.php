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

if ($this->params->get('show_header_sort', 1) && $this->params->get('show_pagination', 1))
{
	$sortPaginationSpan = array(8, 4);
}
elseif ($this->params->get('show_header_sort', 1))
{
	$sortPaginationSpan = array(12, 0);
}
elseif ($this->params->get('show_pagination', 1))
{
	$sortPaginationSpan = array(0, 12);
}
?>
<form name="judl-form-documents" id="judl-form-documents" class="judl-form-documents" method="post" action="#">
<?php
	// Load header
	echo $this->loadTemplate('header');
?>

<div class="judl-doc-list-table">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<?php
			if ($this->params->get('allow_zip_file', 1) && $this->params->get('allow_download_multi_docs', 0))
			{ ?>
				<th style="width:5%">
					<input type="checkbox" name="judl-cbAll" id="judl-cbAll" checked="checked"
					       value=""/>
				</th>
			<?php
			}
			?>

			<th>
				<?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?>
			</th>

			<th style="width:15%">
				<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?>
			</th>

			<th style="width:15%">
				<?php echo JText::_('COM_JUDOWNLOAD_FIELD_VERSION'); ?>
			</th>

			<th style="width:15%">
				<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADS'); ?>
			</th>

			<?php
			if ($this->params->get('show_download_btn_in_listview', 1))
			{
				?>
				<th style="width:15%">
					<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
				</th>
			<?php
			}
			?>
		</tr>
		</thead>

		<tbody>
		<?php
		foreach ($this->items AS $index => $item)
		{
			$this->index = $index;
			$this->item = $item;
			echo $this->loadTemplate('document');
		} ?>
		</tbody>
	</table>
<?php
if ($this->params->get('allow_zip_file', 1) && $this->params->get('allow_download_multi_docs', 0))
{ ?>
	<div class="btn-download-container pull-left">
		<input type="hidden" name="cat_id" value="<?php echo $this->category->id; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo base64_encode(urlencode(JUri::getInstance())); ?>"/>
		<input type="submit" id="judl-btn-multidownload" class="btn" name="download"
		       value="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
<?php
}
?>
</div>
<!-- end div.judl-list-documents-wrapper -->

<?php
	// Load header
	echo $this->loadTemplate('footer');
?>

</form>

<?php
	// Load modal password form outside of document list form
	echo $this->loadTemplate('password_form');
?>