<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die; ?>

<?php echo $this->helper('bootstrap.load', array('javascript' => true)); ?>
<?php echo $this->helper('behavior.koowa'); ?>
<?php echo $this->helper('behavior.modal'); ?>
<?php echo $this->helper('behavior.keepalive'); ?>
<?php echo $this->helper('behavior.validator'); ?>
<?php echo $this->helper('translator.script', array('strings' => array(
    '- Use default -'
))); ?>

<ktml:script src="media://com_docman/js/admin/category.default.js" />

<ktml:module position="toolbar">
    <ktml:toolbar type="actionbar" icon="category-add icon-pencil-2">
</ktml:module>

<div class="docman_form_layout">
    <form action="" method="post" class="-koowa-form">
        <div class="docman_container">
            <div class="docman_grid">
                <div class="docman_grid__item two-thirds">
                    <fieldset>

                        <legend><?php echo $this->translate('Details') ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item two-thirds">
                                <label class="control-label" for="docman_form_title"><?php echo $this->translate('Title') ?></label>
                                <div class="controls">
                                    <div class="input-group">
                                        <span class="input-group-btn input-group-img">
                                            <?php echo $this->helper('behavior.icon', array(
                                                'name'  => 'parameters[icon]',
                                                'id' => 'params_icon',
                                                'value' => $category->getParameters()->get('icon', 'folder'),
                                                'link'  => $this->route('option=com_docman&view=files&layout=select&tmpl=koowa&container=docman-icons&types[]=image')
                                            ))?>
                                        </span>
                                        <input required id="docman_form_title" class="input-group-form-control" type="text" name="title" maxlength="255"
                                               value="<?php echo $this->escape($category->title) ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="control-group docman_grid__item one-third">
                                <label class="control-label" for="docman_form_alias"><?php echo $this->translate('Alias') ?></label>
                                <div class="controls">
                                    <input id="docman_form_alias" type="text" class="input-block-level" name="slug" maxlength="255"
                                           value="<?php echo $this->escape($category->slug) ?>" />
                                </div>
                            </div>
                        </div>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Parent Category') ?></label>
                                <div class="controls">
                                    <?php echo $this->helper('listbox.categories', array(
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


                        <legend><?php echo $this->translate('Description') ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <div class="controls">
                                    <?php echo $this->helper('editor.display', array(
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
                <div class="docman_grid__item one-third">
                    <fieldset>

                        <legend><?php echo $this->translate('Publishing') ?></legend>

                        <div class="docman_grid">
                            <div class="control-group docman_grid__item one-whole">
                                <label class="control-label"><?php echo $this->translate('Status'); ?></label>
                                <div class="controls radio btn-group">
                                        <?php echo $this->helper('select.booleanlist', array(
                                            'name' => 'enabled',
                                            'selected' => $category->enabled,
                                            'true' => 'Published',
                                            'false' => 'Unpublished'
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
                                        'value' => $category->created_on,
                                        'format' => '%Y-%m-%d %H:%M:%S',
                                        'filter' => 'user_utc'
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
                                        'name' => 'access_raw',
                                        'attribs' => array(
                                            'id' => 'access_raw',
                                            'class' => 'input-block-level',
                                            'data-default' => $default_access->title
                                        ),
                                        'selected' => $category->access_raw,
                                        'prompt' => '- '.$this->translate('Inherit from parent category').' -',
                                        'inheritable' => true
                                    )); ?>

                                    <div style="clear: both"></div>
                                    <?php $default = '<span></span>';
                                    if (!$category->isNew() && $category->access_raw == -1):
                                        $default = '<span>'.$category->access_title.'</span>';
                                    endif;
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
                                        'selected' => $category->created_by ? $category->created_by : $this->object('user')->getId(),
                                        'deselect' => false,
                                        'attribs' => array('class' => 'input-block-level select2-users-listbox'),
                                        'select2' => true,
                                        'select2_options' => array('element' => '.select2-users-listbox')
                                    )) ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($category->isPermissible() && $category->canPerform('admin')): ?>
                            <div class="mfp-hide well" id="advanced-permissions" style="max-width: 800px; margin: 10% auto; position: relative;">
                                <?php echo $this->helper('access.rules', array(
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
                            <div class="docman_grid">
                                <div class="control-group docman_grid__item one-whole">
                                    <label class="control-label"><?php echo $this->translate('Action');?></label>
                                    <div class="controls">
                                        <a class="btn" id="advanced-permissions-toggle" href="#advanced-permissions">
                                            <?php echo $this->translate('Change action permissions')?>
                                        </a>
                                        <p class="help-block">
                                            <?php echo $this->translate('For advanced use only'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </fieldset>
                </div>
            </div>
        </div>

        <div class="docman_secondary_container">
            <div class="docman_container">
                <div class="docman_grid">
                    <div class="docman_grid__item one-whole">
                        <fieldset>

                            <legend><?php echo $this->translate('Image') ?></legend>

                            <div class="docman_grid">
                                <div class="control-group docman_grid__item one-half">
                                    <div class="controls">
                                        <?php echo $this->helper('behavior.thumbnail', array(
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
