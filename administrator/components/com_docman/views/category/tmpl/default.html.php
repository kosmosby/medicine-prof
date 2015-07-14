<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?= helper('bootstrap.load', array('javascript' => true)); ?>
<?= helper('behavior.koowa'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" icon="category-add icon-pencil-2">
</ktml:module>

<div class="docman_form_layout">
    <form action="" method="post" class="-koowa-form">
        <div class="koowa_container">
            <div class="koowa_grid__row">
                <div class="koowa_grid__item two-thirds">
                    <fieldset>

                        <legend><?= translate('Details') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item two-thirds">
                                <label class="control-label" for="docman_form_title"><?= translate('Title') ?></label>
                                <div class="controls">
                                    <div class="input-group">
                                        <span class="input-group-btn input-group-img">
                                            <?= helper('behavior.icon', array(
                                                'name'  => 'parameters[icon]',
                                                'id' => 'params_icon',
                                                'value' => $category->getParameters()->get('icon', 'folder'),
                                                'link'  => route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-icons&types[]=image')
                                            ))?>
                                        </span>
                                        <input required id="docman_form_title" class="input-group-form-control" type="text" name="title" maxlength="255"
                                               value="<?= escape($category->title) ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="control-group koowa_grid__item one-third">
                                <label class="control-label" for="docman_form_alias"><?= translate('Alias') ?></label>
                                <div class="controls">
                                    <input id="docman_form_alias" type="text" class="input-block-level" name="slug" maxlength="255"
                                           value="<?= escape($category->slug) ?>" />
                                </div>
                            </div>
                        </div>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Parent Category') ?></label>
                                <div class="controls">
                                    <?= helper('listbox.categories', array(
                                        'deselect' => true,
                                        'check_access' => true,
                                        'name' => 'parent_id',
                                        'attribs' => array('id' => 'category'),
                                        'selected' => $parent ? $parent->id : null,
                                        'ignore' => $ignored_parents
                                    )) ?>
                                </div>
                            </div>
                        </div>


                        <legend><?= translate('Description') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <div class="controls">
                                    <?= helper('editor.display', array(
                                        'name' => 'description',
                                        'value' => $category->description,
                                        'id' => 'description',
                                        'width' => '100%',
                                        'height' => '391',
                                        'cols' => '100',
                                        'rows' => '20',
                                        'buttons' => array('pagebreak')
                                    )); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="koowa_grid__item one-third">
                    <fieldset>

                        <legend><?= translate('Publishing') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Status'); ?></label>
                                <div class="controls radio btn-group">
                                        <?= helper('select.booleanlist', array(
                                            'name' => 'enabled',
                                            'selected' => $category->enabled,
                                            'true' => 'Published',
                                            'false' => 'Unpublished'
                                        )); ?>
                                </div>
                            </div>
                        </div>
                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Date'); ?></label>
                                <div class="controls">
                                    <?= helper('behavior.calendar', array(
                                        'name' => 'created_on',
                                        'id' => 'created_on',
                                        'value' => $category->created_on,
                                        'format' => '%Y-%m-%d %H:%M:%S',
                                        'filter' => 'user_utc'
                                    ))?>
                                </div>
                            </div>
                        </div>


                        <legend><?= translate('Permissions') ?></legend>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Access'); ?></label>
                                <?= helper('access.access_box', array(
                                    'entity' => $category
                                )); ?>
                            </div>
                        </div>

                        <div class="koowa_grid__row">
                            <div class="control-group koowa_grid__item one-whole">
                                <label class="control-label"><?= translate('Owner'); ?></label>
                                <div class="controls">
                                    <?= helper('listbox.users', array(
                                        'name' => 'created_by',
                                        'selected' => $category->created_by ? $category->created_by : object('user')->getId(),
                                        'deselect' => false,
                                        'attribs' => array('class' => 'input-block-level select2-users-listbox'),
                                        'select2' => true,
                                        'select2_options' => array('element' => '.select2-users-listbox')
                                    )) ?>
                                </div>
                            </div>
                        </div>

                        <? if ($category->isPermissible() && $category->canPerform('admin')): ?>
                            <div class="mfp-hide well" id="advanced-permissions" style="max-width: 800px; margin: 10% auto; position: relative;">
                                <?= helper('access.rules', array(
                                    'section' => 'category',
                                    'asset' => $category->getAssetName(),
                                    'asset_id' => $category->asset_id
                                )); ?>
                            </div>
                            <script>
                                kQuery(function($){
                                    $('#advanced-permissions-toggle').on('click', function(e){
                                        e.preventDefault();

                                        $.magnificPopup.open({
                                            items: {
                                                src: $('#advanced-permissions'),
                                                type: 'inline'
                                            }
                                        });
                                    });
                                });
                            </script>
                            <div class="koowa_grid__row">
                                <div class="control-group koowa_grid__item one-whole">
                                    <label class="control-label"><?= translate('Action');?></label>
                                    <div class="controls">
                                        <a class="btn" id="advanced-permissions-toggle" href="#advanced-permissions">
                                            <?= translate('Change action permissions')?>
                                        </a>
                                        <p class="help-block">
                                            <?= translate('For advanced use only'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <? endif; ?>

                    </fieldset>
                </div>
            </div>
        </div>

        <div class="koowa_secondary_container">
            <div class="koowa_container">
                <div class="koowa_grid__row">
                    <div class="koowa_grid__item one-whole">
                        <fieldset>

                            <legend><?= translate('Image') ?></legend>

                            <div class="koowa_grid__row">
                                <div class="control-group koowa_grid__item one-half">
                                    <div class="controls">
                                        <?= helper('behavior.thumbnail', array(
                                            'allow_automatic' => false,
                                            'value' => $category->image,
                                            'name'  => 'image',
                                            'id'  => 'image'
                                        )) ?>
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
