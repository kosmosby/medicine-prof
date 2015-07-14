<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class PlgLogmanDocman extends ComLogmanPluginAbstract
{
    /**
     * @var array DOCman 2.x dependencies.
     * @see ComLogmanPluginAbstract::$_dependencies
     */
    protected $_dependencies = array(
        'com://site/docman.controller.download'  => 'com://admin/logman.controller.behavior.download_loggable',
        'com://site/docman.controller.document'  => 'com://admin/logman.controller.behavior.loggable',
        'com://admin/docman.controller.document' => 'com://admin/logman.controller.behavior.loggable',
        'com://admin/docman.controller.category' => 'com://admin/logman.controller.behavior.loggable');

    /**
     * DOCman v1.x after edit document event handler.
     *
     * @param $data
     */
    public function onAfterEditDocument($data)
    {
        $isNew    = $data['process'] == 'new document';
        $document = $data['document'];

        $activity = $this->getActivity(array(
            'action'  => $isNew ? 'add' : 'edit',
            'subject' => array(
                'component' => 'docman',
                'resource'  => 'document',
                'id'        => $document->id,
                'title'     => $document->dmname,
            )));

        $this->save($activity);
    }
}