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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=treestructure'); ?>"
	method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="span12">
		<?php
		
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped adminlist" id="data-list">
				<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'tbl_cat.title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_PUBLISHED', 'tbl_cat.published', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_GROUP_SETTING', 'tbl_cat.selected_fieldgroup', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_GROUP', 'tbl_cat.fieldgroup_id', $listDirn, $listOrder); ?>
					</th>
					<?php
					if(JUDownloadHelper::hasMultiRating())
					{
						?>
						<th width="5%">
							<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_CRITERIA_GROUP_SETTING', 'tbl_cat.selected_criteriagroup', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_CRITERIA_GROUP', 'tbl_cat.criteriagroup_id', $listDirn, $listOrder); ?>
						</th>
					<?php
					}
					?>
					<th width="5%">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_STYLE_SETTING', 'tbl_cat.style_id', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JText::_('COM_JUDOWNLOAD_STYLE'); ?>
					</th>
					<th width="5%">
						<?php echo JText::_('COM_JUDOWNLOAD_TEMPLATE'); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'tbl_cat.access', $listDirn, $listOrder); ?>
					</th>
					<th width="1%">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'tbl_cat.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
				<tr>
					<td colspan="15"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				foreach ($this->items AS $i => $item) :
					$canEdit    = $user->authorise('core.edit',       'com_judownload');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_judownload') && $item->created_by == $user->id;
					$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin;
                    $canChange = ($item->level == 0) ? false : $canChange;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'categories.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
								?>
							<?php endif; ?>

							<?php if (($canEdit || $canEditOwn) && $item->level > 0)
							{
                                ?>
                                <?php echo str_repeat('<span class="gi">&mdash;</span>', $item->level) ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=category.edit&amp;id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							<?php
							}
							else
							{
								?>
                                <?php echo str_repeat('<span class="gi">&mdash;</span>', $item->level) ?>
								<?php echo $item->title; ?>
							<?php
							} ?>

                            <?php
                            if ($item->level == 1 && JUDownloadHelperRoute::findItemId(array('tree' => array($item->id))))
                            {
                                ?>
                                <span class="btn btn-mini"><i class="icon-home"></i></span>
                            <?php
                            }
                            if ($item->level == 1 && $item->config_params)
                            {
                                ?>
                                <span class="btn btn-mini"><i class="icon-cog hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_OVERRIDE_CONFIG'); ?>"></i></span>
                            <?php
                            }
                            ?>

							<p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
						</td>
						<td class="center">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange, 'cb'); ?>
						</td>

                        <td>
                            <?php
                            if($item->selected_fieldgroup > 0)
                            {
                                ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=fieldgroup.edit&amp;id=' . $item->selected_fieldgroup); ?>">
                                    <?php echo $item->selected_fieldgroup_title; ?>
                                </a>
                            <?php
                            }
                            else
                            {
                                echo $item->selected_fieldgroup_title;
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($item->fieldgroup_id > 0)
                            { ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=fieldgroup.edit&amp;id=' . $item->fieldgroup_id); ?>">
                                    <?php echo $item->field_group_title; ?>
                                </a>
                            <?php
                            }
                            else
                            {
                                echo $item->field_group_title;
                            }
                            ?>
                        </td>
						<?php
						if(JUDownloadHelper::hasMultiRating())
						{
							?>
							<td>
								<?php
								if ($item->selected_criteriagroup > 0)
								{
									?>
									<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->selected_criteriagroup); ?>">
										<?php echo $item->selected_criteriagroup_title; ?>
									</a>
								<?php
								}
								else
								{
									echo $item->selected_criteriagroup_title;
								}
								?>
							</td>
							<td>
								<?php
								if ($item->criteriagroup_id > 0)
								{ ?>
									<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->selected_criteriagroup); ?>">
										<?php echo $item->criteria_group_title; ?>
									</a>
								<?php
								}
								else
								{
									echo $item->criteria_group_title;
								}
								?>
							</td>
						<?php
						}
						?>
                        <td>
                            <?php
                            echo $item->selected_style_title;
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=style.edit&amp;id=' . $item->real_style_id); ?>">
                            <?php echo $item->style_title; ?></a>
                        </td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=template.edit&amp;id=' . $item->template_id . '&amp;file=' . base64_encode('home')); ?>">
                            <?php echo $item->template_title; ?></a>
                        </td>
                        <td>
                            <?php echo $item->access_level; ?>
                        </td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<div>
            <input type="hidden" name="view" value="<?php echo $this->_name; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>