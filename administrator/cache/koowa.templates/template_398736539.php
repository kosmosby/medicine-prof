<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.local_dates'); ?>
<?php echo $this->helper('bootstrap.load', array('class' => array('full_height'))); ?>

<?php echo $this->helper('behavior.doclink', array('list' => $pages, 'editor' => $editor)) ?>

<div class="koowa_dialog koowa_dialog--doclink">
    <div class="koowa_dialog__layout">
        <div class="koowa_dialog__wrapper">

            <?php if (count($pages)): ?>

            <div class="koowa_dialog__wrapper__child koowa_dialog__doclink_menu_items">
                <h2 class="koowa_dialog__title"><?php echo $this->translate('Menu Items and categories')?></h2>
                <div class="koowa_dialog__child__content">
                    <div class="koowa_dialog__child__content__box">
                        <div id="documents-sidebar">
                            <div class="sidebar-inner" style="border-right:none;">
                                <?php /* Doclink.Tree loads a jqTree instance here */ ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="koowa_dialog__wrapper__child koowa_dialog__doclink_table">
                <h2 class="koowa_dialog__title"><?php echo $this->translate('Documents'); ?></h2>
                <div class="koowa_dialog__child__content" id="spinner_container">
                    <div class="koowa_dialog__child__content__box">
                        <div id="files-container">
                            <div class="scopebar">
                                <div class="scopebar-search">
                                    <?php echo $this->helper('grid.search', array('submit_on_clear' => false)) ?>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="table table-striped table-condensed" id="document_list">
                                    <thead>
                                        <tr>
                                            <th data-name="title" class="koowa_dialog__table_title footable-sortable">
                                                <?php echo $this->translate('Title'); ?>
                                                <span class="footable-sort-indicator koowa_icon--sort koowa_icon--12"></span>
                                            </th>
                                            <th data-name="access_title" class="koowa_dialog__table_title footable-sortable" width=1>
                                                <?php echo $this->translate('Access'); ?>
                                                <span class="footable-sort-indicator koowa_icon--sort koowa_icon--12"></span>
                                            </th>
                                            <th data-name="created_on" class="koowa_dialog__table_title footable-sortable" data-sort-initial="descending" width=1>
                                                <?php echo $this->translate('Date'); ?>
                                                <span class="footable-sort-indicator koowa_icon--sort koowa_icon--12"></span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="initial-row">
                                            <td style="text-align: center" colspan="3">
                                                <?php echo $this->translate('Please first select a menu item and then a category from the sidebar.')?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="koowa_dialog__wrapper__child koowa_dialog__doclink_insert">
                <form class="form-horizontal" id="properties">
                    <input type="hidden" id="url" value="" />
                    <div class="input-group">
                        <input type="text" id="caption" value="" class="span4 input-group-form-control" />
                        <span class="input-group-btn">
                            <button type="button" id="insert-image" class="btn btn-primary input-append"><?php echo $this->translate('Go') ?></button>
                        </span>
                    </div>
                </form>
            </div>

            <?php else: ?>

            <div class="koowa_dialog__message_layer">
            <div class="alert alert-error">
                <h4 style="margin-top:0;"><?php echo $this->translate('No menu items found') ?></h4>
                <p><?php echo $this->translate('Docman menu warning'); ?></p>
                <p><?php echo $this->translate('Docman menu warning instruction'); ?></p>
                <?php if ($admin): ?>
                <p><a href="<?php echo JRoute::_('index.php?option=com_menus&view=items'); ?>" target="_parent" class="btn btn-primary"><?php echo $this->translate('Go to menu manager') ?></a></p>
                <?php endif; ?>
            </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>
