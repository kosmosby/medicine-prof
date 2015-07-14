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
} else {
	JHtml::_('behavior.tooltip');
	$separator = "::";
	$classTooltip = "hasTip";
}

$session = JFactory::getSession();
$copied_cat_id_arr = $session->get('copied_cat_id_arr');
$categories = array();
foreach($copied_cat_id_arr as $cat_id){
	$categories[] = JUDownloadHelper::getCategoryById($cat_id);
}

?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		var category = document.getElementsByName("categories[]")[0].value;
		if (task == 'categories.copyCats' && category == '') {
			alert("<?php echo JText::_('COM_JUDOWNLOAD_PLEASE_SELECT_CATEGORY'); ?>");
			return false;
		}

		Joomla.submitform(task, document.getElementById('adminForm'));
	};
</script>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=documents'); ?>"
		method="post" name="adminForm" id="adminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_COPY_CATEGORIES'); ?></legend>
			<div class="form-horizontal">
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group ">
							<div class="control-label">
								<label for="categories"><?php echo JText::_('COM_JUDOWNLOAD_COPY_TO_CATEGORIES'); ?></label>
							</div>
							<div class="controls">
								<?php
									$options = JUDownloadHelper::getCategoryOptions(1, true, "category");
									echo JHtml::_('select.genericList', $options, 'categories[]', 'class="inputbox" multiple size="6"', 'value', 'text', 1, 'categories');
								?>
							</div>
						</div>

						<div class="row-fluid">
							<div class="span6">

								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="subcategories" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_SUB_CATEGORIES') . $separator . JText::_('COM_JUDOWNLOAD_COPY_SUB_CATEGORIES_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_SUB_CATEGORIES'); ?></label>
									</div>
									<div class="controls">
										<input checked="checked" type="checkbox" name="copy_options[]" value="copy_subcategories" id="subcategories" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="related_categories" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_RELATED_CATEGORIES') . $separator . JText::_('COM_JUDOWNLOAD_COPY_RELATED_CATEGORIES_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_RELATED_CATEGORIES'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_related_categories" id="related_categories" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="cat_permission" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_CATEGORY_PERMISSION') . $separator . JText::_('COM_JUDOWNLOAD_COPY_CATEGORY_PERMISSION_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_CATEGORY_PERMISSION'); ?></label>
									</div>
									<div class="controls">
										<input checked="checked" type="checkbox" name="copy_options[]" value="copy_cat_permission" id="cat_permission" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="documents" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_DOCUMENTS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_DOCUMENTS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_DOCUMENTS'); ?></label>
									</div>
									<div class="controls">
										<input checked="checked" type="checkbox" name="copy_options[]" value="copy_documents" id="documents" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="extra_fields" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_EXTRA_FIELDS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_EXTRA_FIELDS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_EXTRA_FIELDS'); ?></label>
									</div>
									<div class="controls">
										<input checked="checked" type="checkbox" name="copy_options[]" value="copy_extra_fields" id="extra_fields" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="files" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_FILES') . $separator . JText::_('COM_JUDOWNLOAD_COPY_FILES_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_FILES'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_files" id="files" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="changelogs" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_CHANGELOGS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_CHANGELOGS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_CHANGELOGS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_changelogs" id="changelogs" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="related_documents" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_RELATED_DOCUMENTS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_RELATED_DOCUMENTS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_RELATED_DOCUMENTS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_related_documents" id="related_documents" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="comments" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_COMMENTS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_COMMENTS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_COMMENTS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_comments" id="comments" />
									</div>
								</div>
							</div>
							<div class="span6">
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="reports" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_REPORTS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_REPORTS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_REPORTS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_reports" id="reports" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="downloads" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_DOWNLOADS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_DOWNLOADS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_DOWNLOADS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_downloads" id="downloads" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="rates" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_RATES') . $separator . JText::_('COM_JUDOWNLOAD_COPY_RATES_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_RATES'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_rates" id="rates" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="hits" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_HITS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_HITS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_HITS'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" name="copy_options[]" value="copy_hits" id="hits" />
									</div>
								</div>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="permission" title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_PERMISSION') . $separator . JText::_('COM_JUDOWNLOAD_COPY_PERMISSION_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_PERMISSION'); ?></label>
									</div>
									<div class="controls">
										<input type="checkbox" checked="checked" name="copy_options[]" value="copy_permission" id="permission" />
									</div>
								</div>
								<?php
								if(JUDLPROVERSION)
								{
									?>
									<div class="control-group ">
										<div class="control-label">
											<label class="<?php echo $classTooltip; ?>" for="subscriptions"
												title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_SUBSCRIPTIONS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_SUBSCRIPTIONS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_SUBSCRIPTIONS'); ?></label>
										</div>
										<div class="controls">
											<input type="checkbox" name="copy_options[]" value="copy_subscriptions"
												id="subscriptions"/>
										</div>
									</div>
									<div class="control-group ">
										<div class="control-label">
											<label class="<?php echo $classTooltip; ?>" for="logs"
												title="<?php echo JText::_('COM_JUDOWNLOAD_COPY_LOGS') . $separator . JText::_('COM_JUDOWNLOAD_COPY_LOGS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_COPY_LOGS'); ?></label>
										</div>
										<div class="controls">
											<input type="checkbox" name="copy_options[]" value="copy_logs" id="logs"/>
										</div>
									</div>
								<?php
								}
								?>
								<div class="control-group ">
									<div class="control-label">
										<label class="<?php echo $classTooltip; ?>" for="keep_template_params" title="<?php echo JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS') . $separator . JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS_DESC'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_KEEP_TEMPLATE_PARAMS'); ?></label>
									</div>
									<div class="controls">
										<input checked="checked" type="checkbox" name="copy_options[]" value="keep_template_params" id="keep_template_params" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="span6">
						<div>
							<?php
								echo JText::plural('COM_JUDOWNLOAD_TOTAL_N_CATEGORIES_WILL_BE_COPIED', count($categories));
							?>
							<ul>
								<?php
								foreach($categories as $category)
								{ ?>
									<li>
										<?php echo $category->title;?>
									</li>
								<?php
								} ?>
							</ul>
						</div>
						<?php echo JText::_('COM_JUDOWNLOAD_COPY_CATEGORIES_MESSAGE'); ?>
					</div>
				</div>

			</div>

			<div>
				<input type="hidden" name="task" value="documents.copyDocuments" />
				<input type="hidden" name="option" value="com_judownload" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</fieldset>
	</form>
</div>