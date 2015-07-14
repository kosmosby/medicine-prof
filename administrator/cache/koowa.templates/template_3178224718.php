<?php /**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('KOOWA') or die;

$show_delete   = isset($show_delete) ? $show_delete : $document->canPerform('delete');
$show_edit     = isset($show_edit) ? $show_edit : $document->canPerform('edit');
$button_size   = 'btn-'.(isset($button_size) ? $button_size : 'small');
?>

<?php // Edit and delete buttons ?>
<?php if (!($document->isLockable() && $document->isLocked()) && ($show_edit || $show_delete)): ?>
<div class="btn-toolbar koowa_toolbar">
        <div class="btn-group">

        <?php // Edit ?>
        <?php if ($show_edit): ?>
            <a class="btn btn-default <?php echo $button_size ?>"
               href="<?php echo $this->helper('route.document', array('entity' => $document, 'layout' => 'form', 'tmpl' => 'koowa'));?>"
            ><?php echo $this->translate('Edit'); ?></a>
        <?php endif ?>

        <?php // Delete ?>
        <?php if ($show_delete):
            $data = array(
                'method' => 'post',
                'url'    => (string)$this->helper('route.document',array('entity' => $document), true, false),
                'params' => array(
                    'csrf_token' => $this->object('user')->getSession()->getToken(),
                    '_action'    => 'delete',
                    '_referrer'  => base64_encode((string) $this->object('request')->getUrl())
                )
            );

            if ($this->parameters()->view == 'document')
            {
                if ((string)$this->object('request')->getReferrer()) {
                    $data['params']['_referrer'] = base64_encode((string) $this->object('request')->getReferrer());
                } else {
                    $data['params']['_referrer'] = base64_encode(JURI::base());
                }

            }
        ?>
            <?php echo $this->helper('behavior.deletable'); ?>
            <a class="btn <?php echo $button_size ?> btn-danger docman-deletable" href="#" rel="<?php echo $this->escape(json_encode($data)) ?>">
                <?php echo $this->translate('Delete') ?>
            </a>
        <?php endif ?>
    </div>
</div>
<?php endif ?>
