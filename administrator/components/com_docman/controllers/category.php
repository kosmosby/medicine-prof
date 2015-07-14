<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerCategory extends ComDefaultControllerDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback('before.delete', array($this, 'beforeDelete'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array('aclable')
        ));

        parent::_initialize($config);
    }

    /**
     * Halts the delete if the category has documents attached to it.
     *
     * Also makes sure subcategories are deleted correctly when both
     * they and their parents are in the rowset to be deleted.
     *
     * @param KCommandContext $context
     */
    public function beforeDelete(KCommandContext $context)
    {
        $data = $this->getModel()->getList();

        $documents = $data->getDocumentMap();

        if ($count = count($documents)) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            $message = $translator->choose(array(
                'This category or its children has a document attached. You first need to delete or move it before deleting this category.',
                'This category or its children has %count% documents attached. You first need to delete or move them before deleting this category.'
               ), $count, array('%count%' => $count));
            $context->setError(new KControllerException($message));

            return false;
        }

        /*
         * This removes the child categories from the rowset since they will be deleted by their parent.
         * If we don't do this, rowset gets confused when it tries to delete a non-existant row.
         */
        if ($data instanceof KDatabaseRowsetInterface) {
            $to_be_deleted = array();
            // PHP gets confused if you extract a row and then continue iterating on the rowset
            $iterator = clone $data;
            foreach ($iterator as $row) {
                if (in_array($row->id, $to_be_deleted)) {
                    $data->extract($row);
                }

                $to_be_deleted += $row->getDescendants()->getColumn('id');
            }
        }
    }
}
