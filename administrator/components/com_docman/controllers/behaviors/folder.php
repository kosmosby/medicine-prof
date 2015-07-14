<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Used by the file controller to determine if a folder has any documents attached to it before delete
 */
class ComDocmanControllerBehaviorFolder extends ComDocmanControllerBehaviorFile
{
    public function checkName($name)
    {
        if (in_array(strtolower($name), self::$_rejected_names)) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            throw new KControllerException($translator->translate(
                'You cannot create a folder named %foldername% for security reasons.',
                array('%foldername%' => $name)
            ));
        }
    }

    protected function _beforeAdd(KCommandContext $context)
    {
        return $this->checkName($this->getModel()->name);
    }

    protected function _beforeDelete(KCommandContext $context)
    {
        $request = $this->getRequest();
        $path = ($request->folder ? $request->folder.'/' : '') . $request->name;

        $documents = $this->getService('com://admin/docman.model.documents')
            ->search_path($path.'/%')
            ->storage_type('file')
            ->getList();
        $count = count($documents);

        if ($count) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            $message = $translator->choose(array(
                'The document with the title %title% has a file attached from this folder. You should either change the attached file or delete the document before deleting this folder.',
                'There are %count% documents that have a file attached from this folder. You should either change the attached files or delete these documents before deleting this folder.'
            ), $count, array(
                '%count%' => $count,
                '%title%' => $count == 1 ? $documents->top()->title : ''
            ));

            throw new KControllerException($message);
        }
    }
}
