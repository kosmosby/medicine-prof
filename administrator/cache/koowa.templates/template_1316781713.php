<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php // Title field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item two-thirds">
        <label class="control-label" for="docman_form_title"><?php echo $this->translate('Title') ?></label>
        <div class="controls">
            <div class="input-group">
                <span class="input-group-btn">
                    <?php echo $this->helper('behavior.icon', array(
                        'name'  => 'parameters[icon]',
                        'id'    => 'params_icon',
                        'value' => $document->getParameters()->get('icon', 'default'),
                        'link'  => $this->route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-icons&types[]=image')
                    ))?>
                </span>
                <input required class="input-group-form-control" id="docman_form_title" type="text" name="title" maxlength="255"
                       value="<?php echo $this->escape($document->title); ?>" />
            </div>
        </div>
    </div>
    <div class="control-group docman_grid__item one-third">
        <label class="control-label" for="docman_form_alias"><?php echo $this->translate('Alias') ?></label>
        <div class="controls">
            <input id="docman_form_alias" type="text" class="input-block-level" name="slug" maxlength="255"
                   value="<?php echo $this->escape($document->slug) ?>" />
        </div>
    </div>
</div>

<?php // File field ?>
<?php echo $this->import('com://admin/docman.document.default_field_file.html') ?>

<?php // Category field ?>
<div class="docman_grid">
    <div class="control-group docman_grid__item one-whole">
        <label class="control-label"><?php echo $this->translate('Category'); ?></label>
        <div class="controls">
            <?php echo $this->helper('listbox.categories', array(
                'check_access' => true,
                'deselect' => false,
                'required' => true,
                'name' => 'docman_category_id',
                'attribs' => array(
                    'required' => true,
                    'id'    => 'docman_category_id'
                ),
                'selected' => $document->docman_category_id
            ))?>
        </div>
    </div>
</div>