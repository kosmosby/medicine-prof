<?
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die;

$type = KStringInflector::singularize($entity->getIdentifier()->name);
?>

<?= helper('behavior.koowa'); ?>
<?= helper('translator.script', array('strings' => array(
    'Calculating',
    'Use default',
    'Inherit from selected category',
    'Inherit from parent category'
))); ?>

<ktml:script src="media://com_docman/js/access.js" />

<script>
var DOCman = DOCman || {};
DOCman.viewlevels = <?= json_encode($viewlevels); ?>;

kQuery(function() {
    new DOCman.Usergroups('.access-box', {
        category: "<?= $type === 'document' ? '#docman_category_id' : '#category' ?>",
        entity: "<?= $type ?>"
    });
});
</script>

<div class="controls access-box"
     data-selected="<?= $entity->access_raw ?>"
     data-default-id="<?= $default_access->id ?>"
     data-default-title="<?= $default_access->title ?>"
    >

    <div class="access_container">

        <div class="control-group">
            <label class="checkbox">
                <input type="checkbox" name="inherit" value="1"  />
                <span></span>
            </label>
        </div>

        <div class="access_choices_container">
            <div>
                <ul class="nav nav-tabs">
                    <li><a data-target=".access-box .tab-pane:eq(0)"
                           data-pane="groups" data-toggle="tab"><?= translate('Groups'); ?></a>
                    </li>
                    <li><a data-target=".access-box .tab-pane:eq(1)"
                           data-pane="presets" data-toggle="tab"><?= translate('Presets'); ?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane">
                        <label class="control-label">
                            <?= translate('This {item_type} can be viewed by:', array('item_type' => $type)); ?>
                        </label>
                        <?= helper('listbox.groups', array(
                            'name' => '',
                            'selected' => array_keys($entity->getGroups()),
                            'attribs'  => array(
                                'multiple' => 'true',
                                'class'    => 'group_selector'
                            )
                        )); ?>
                    </div>
                    <div class="tab-pane">
                        <?= helper('listbox.access', array(
                            'name'     => '',
                            'deselect' => false,
                            'selected' => $entity->access_raw >= 0 ? $entity->access_raw : null,
                            'attribs' => array(
                                'class' => 'access_selector input-block-level'
                            )
                        )); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="who-can-see-container">
            <label class="control-label">
                <?= translate('This {item_type} can be viewed by:', array('item_type' => $type)); ?>
            </label>
            <ul class="who-can-see">

            </ul>
        </div>

    </div>

</div>