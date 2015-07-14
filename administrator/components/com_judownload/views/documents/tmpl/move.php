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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.multiselect');

if(JUDownloadHelper::isJoomla3x()){
	$classTooltip =  "hasTooltip";
	$separator = "<br/>";
	JHtml::_('bootstrap.tooltip');
}else{
	JHtml::_('behavior.tooltip');
	$separator = "::";
	$classTooltip = "hasTip";
}

$session = JFactory::getSession();

$moved_document_id = $session->get('moved_document_id');
$documents = array();
foreach($moved_document_id as $doc_id){
	$documents[] = JUDownloadHelper::getDocumentById($doc_id);
}
?>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=documents'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_MOVE_DOCUMENTS'); ?></legend>
			<div class="span6">
				<div class="form-horizontal">
					<div class="control-group">
						<div class="control-label">
								<label for="categories"><?php echo JText::_('COM_JUDOWNLOAD_MOVE_TO_CATEGORY'); ?></label>
						</div>
						<div class="controls">
							<?php
								$options = JUDownloadHelper::getCategoryOptions();
								$params = JUDownloadHelper::getParams();
								if (!$params->get('allow_add_doc_to_root', 0))
								{
									$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
									foreach ($options AS $key => $option)
									{
										if ($option->value == $rootCat->id)
										{
											unset($options[$key]);
											break;
										}
									}
								}
								echo JHtml::_('select.genericList', $options, 'categories', '', 'value', 'text', '', 'categories');
							?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label class="<?php echo $classTooltip; ?>" for="keep_extra_fields" title="<?php echo JText::_('COM_JUDOWNLOAD_KEEP_EXTRA_FIELDS') . $separator . JText::_('COM_JUDOWNLOAD_KEEP_EXTRA_FIELDS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_KEEP_EXTRA_FIELDS'); ?></label>
						</div>
						<div class="controls">
							<input type="checkbox" checked="checked" name="move_options[]" value="keep_extra_fields" id="keep_extra_fields" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label class="<?php echo $classTooltip; ?>" for="keep_rates" title="<?php echo JText::_('COM_JUDOWNLOAD_KEEP_RATES') . $separator . JText::_('COM_JUDOWNLOAD_KEEP_RATES_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_KEEP_RATES'); ?></label>
						</div>
						<div class="controls">
							<input type="checkbox" checked="checked" name="move_options[]" value="keep_rates" id="keep_rates" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label class="<?php echo $classTooltip; ?>" for="permission" title="<?php echo JText::_('COM_JUDOWNLOAD_KEEP_PERMISSION') . $separator . JText::_('COM_JUDOWNLOAD_KEEP_PERMISSION_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_KEEP_PERMISSION'); ?></label>
						</div>
						<div class="controls">
							<input type="checkbox" checked="checked" name="move_options[]" value="keep_permission" id="permission" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<label class="<?php echo $classTooltip; ?>" for="keep_template_params" title="<?php echo JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS') . $separator . JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS'); ?></label>
						</div>
						<div class="controls">
							<input type="checkbox" checked="checked" name="move_options[]" value="keep_template_params" id="keep_template_params" />
						</div>
					</div>
				</div>
			</div>
			<div class="span6">
				<div>
					<?php
					echo JText::plural('COM_JUDOWNLOAD_TOTAL_N_DOCUMENTS_WILL_BE_MOVED', count($documents));
					?>
					<ul>
						<?php foreach($documents as $document)
						{ ?>
							<li>
								<?php echo $document->title;?>
							</li>
						<?php
						} ?>
					</ul>
				</div>
				<div class="right"><?php echo JText::_('COM_JUDOWNLOAD_MOVE_DOCUMENTS_MESSAGE'); ?></div>
			</div>

			<div>
				<input type="hidden" name="task" value="documents.moveDocuments" />
				<input type="hidden" name="option" value="com_judownload" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</fieldset>
	</form>
</div>