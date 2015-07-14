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
class ComDocmanControllerBehaviorFile extends KControllerBehaviorAbstract
{
    protected static $_rejected_names = array('.htaccess', 'web.config', 'index.htm', 'index.html', 'index.php', '.svn', '.git', 'cvs');

    public function checkName($name)
    {
        if (in_array(strtolower($name), self::$_rejected_names)) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            throw new KControllerException($translator->translate(
                'You cannot upload a file named %filename% for security reasons.',
                array('%filename%' => $name)
            ));
        }
    }

    protected function _beforeAdd(KCommandContext $context)
    {
        return $this->checkName($context->data->name);
    }

    protected function _beforeCopy(KCommandContext $context)
    {
        return $this->checkName($context->data->destination_name);
    }

    protected function _beforeMove(KCommandContext $context)
    {
        return $this->_beforeCopy($context);
    }

    protected function _beforeDelete(KCommandContext $context)
    {
        $request = $this->getRequest();
        $path = ($request->folder ? $request->folder.'/' : '') . $request->name;

        $documents = $this->getService('com://admin/docman.model.documents')
            ->storage_path($path)
            ->storage_type('file')
            ->getList();
        $count = count($documents);

        if ($count) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            $message = $translator->choose(array(
                'The document with the title %title% has this file attached to it. You should either change the attached file or delete the document before deleting this file.',
                'This file has %count% documents attached to it. You should either change the attached files or delete these documents before deleting this file.',
            ), $count, array(
                    '%count%' => $count,
                    '%title%' => $count == 1 ? $documents->top()->title : ''
            ));

            throw new KControllerException($message);
        }
    }
}
