<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load', array('javascript' => true)); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.keepalive'); ?>
<?php echo $this->helper('behavior.validator'); ?>
<?php echo $this->helper('behavior.icon_map'); ?>

<ktml:script src="media://com_docman/js/document.js" />

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" icon="article-add icon-pencil-2">
</ktml:module>

<div class="docman_form_layout">
    <form action="" method="post" class="-koowa-form">
        <div class="docman_container">
            <div class="docman_grid">
                <div class="docman_grid__item two-thirds">

                    <legend><?php echo $this->translate('Details') ?></legend>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item two-thirds">
                            <label class="control-label" for="docman_form_title"><?php echo $this->translate('Title') ?></label>
                            <div class="controls">
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <?php echo $this->helper('behavior.icon', array(
                                            'name'  => 'parameters[icon]',
                                            'id' => 'params_icon',
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

                    <?php echo $this->import('default_field_file.html'); ?>

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


                    <legend><?php echo $this->translate('Description') ?></legend>

                    <div class="docman_grid description_container">
                        <div class="control-group docman_grid__item one-whole">
                            <div class="controls">
                                <?php echo $this->helper('editor.display', array(
                                    'name' => 'description',
                                    'value' => $document->description,
                                    'id'   => 'description',
                                    'width' => '100%',
                                    'height' => '341',
                                    'cols' => '100',
                                    'rows' => '20',
                                    'buttons' => array('pagebreak')
                                )); ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="docman_grid__item one-third">

                    <legend><?php echo $this->translate('Publishing') ?></legend>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Status'); ?></label>
                            <div class="controls radio btn-group">
                                <?php echo $this->helper('select.booleanlist', array(
                                    'name' => 'enabled',
                                    'selected' => $document->enabled,
                                    'true' => $this->translate('Published'),
                                    'false' => $this->translate('Unpublished')
                                )); ?>
                            </div>
                        </div>
                    </div>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Date'); ?></label>
                            <div class="controls">
                                <?php echo $this->helper('behavior.calendar', array(
                                    'name' => 'created_on',
                                    'id' => 'created_on',
                                    'value' => $document->created_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc'
                                ))?>
                            </div>
                        </div>
                    </div>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Start publishing on'); ?></label>
                            <div class="controls">
                                <?php $datetime = new DateTime(null, new DateTimeZone('UTC')) ?>
                                <?php $datetime->modify('-1 day'); ?>
                                <?php echo $this->helper('behavior.calendar', array(
                                    'name' => 'publish_on',
                                    'id' => 'publish_on',
                                    'value' => $document->publish_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc',
                                    'options' => array(
                                        'clearBtn' => true,
                                        'startDate' => $datetime->format('Y-m-d H:i:s'),
                                        'todayBtn' => false
                                    )
                                ))?>
                            </div>
                        </div>
                    </div>
                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Stop publishing on'); ?></label>
                            <div class="controls">
                                <?php echo $this->helper('behavior.calendar', array(
                                    'name' => 'unpublish_on',
                                    'id' => 'unpublish_on',
                                    'value' => $document->unpublish_on,
                                    'format' => '%Y-%m-%d %H:%M:%S',
                                    'filter' => 'user_utc',
                                    'options' => array(
                                        'clearBtn' => true,
                                        'todayBtn' => false
                                    )
                                ))?>
                            </div>
                        </div>
                    </div>


                    <legend><?php echo $this->translate('Permissions') ?></legend>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Access'); ?></label>
                            <div class="controls">
                                <?php echo $this->helper('listbox.access', array(
                                    'name' => 'access',
                                    'inheritable' => true,
                                    'attribs' => array('class' => 'input-block-level'),
                                    'selected' => $document->access_raw
                                ))?>

                                <?php $has_value = !$document->isNew() && $document->access_raw == -1;
                                $default = '<span>'
                                    . ($has_value ? $document->access_title : '')
                                    .'</span>';
                                ?>
                                <p class="help-block current-access">
                                    <?php echo $this->translate('Calculated as {access}', array('access' => $default)); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="docman_grid">
                        <div class="control-group docman_grid__item one-whole">
                            <label class="control-label"><?php echo $this->translate('Owner'); ?></label>
                            <div class="controls">
                                <?php echo $this->helper('listbox.users', array(
                                    'name' => 'created_by',
                                    'selected' => $document->created_by ? $document->created_by : $this->object('user')->getId(),
                                    'deselect' => false
                                )) ?>
                            </div>
                        </div>
                    </div>

                    <?php echo $this->import('default_field_acl.html'); ?>

                </div>
            </div>
        </div>

        <div class="docman_secondary_container">
            <div class="docman_container">
                <div class="docman_grid">
                    <div class="docman_grid__item two-thirds">
                        <legend><?php echo $this->translate('Image') ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <div class="controls">
                                    <?php echo $this->helper('behavior.thumbnail', array(
                                        'automatic_path' => !$document->isNew() ? 'generated/' . md5($document->id) . '.png' : '',
                                        'value' => $document->image,
                                        'name'  => 'image',
                                        'id'  => 'image',
                                        'automatic_switch' => $document->isNew()
                                    )) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="docman_grid__item one-third">
                        <legend><?php echo $this->translate('Audit') ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Downloads'); ?></label>
                                <div class="controls" id="hits-container">
                                    <p class="help-inline">
                                        <?php echo $document->hits; ?>
                                    </p>
                                    <?php if ($document->hits): ?>
                                        <a href="#" class="btn btn-small"><?php echo $this->translate('Reset'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($document->modified_by): ?>
                            <div class="docman_grid">
                                <div class="control-group docman_grid__item one-whole">
                                    <label class="control-label"><?php echo $this->translate('Modified by'); ?></label>
                                    <div class="controls">
                                        <span class="help-info">
                                        <?php echo $this->object('user.provider')->load($document->modified_by)->getName(); ?>
                                        <?php echo $this->translate('on') ?>
                                        <?php echo $this->helper('date.format', array('date' => $document->modified_on)); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </form>
</div>