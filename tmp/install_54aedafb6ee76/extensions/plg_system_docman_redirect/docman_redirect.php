<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class plgSystemDocman_redirect extends JPlugin
{
    public function onAfterRoute()
    {
        $app    = JFactory::getApplication();
        $input  = $app->input;
        $option = $input->getCmd('option', '');

        if (!class_exists('Koowa')
            || !class_exists('KObjectManager')
            || !$app->isSite()
            || $option !== 'com_docman'
        ) {
            return;
        }

        $task   = $input->getCmd('task', '');
        $id     = $input->getInt('gid', 0);

        if (empty($task) && preg_match('#(doc_details|doc_download|cat_view)\/([0-9]+)#i', (string)JFactory::getURI(), $matches))
        {
            $task = $matches[1];
            $id   = $matches[2];
        }

        if (!in_array($task, array('doc_details', 'doc_download', 'cat_view'))) {
            return;
        }

        $itemid = $input->getInt('Itemid', 0);
        $url    = null;

        if (!$app->getMenu()->getItem($itemid)) {
            $itemid = 0;
        }

        if ($task === 'doc_download' || $task === 'doc_details')
        {
            $document = KObjectManager::getInstance()->getObject('com://site/docman.model.documents')
                ->enabled(1)
                ->status('published')
                // Also need to redirect links for registered users
                //->access(KObjectManager::getInstance()->getObject('user')->getRoles())
                ->page('all')
                ->id($id)
                ->fetch();

            if (!$document->isNew())
            {
                $view = $task === 'doc_download' ? 'download' : 'document';
                $url = sprintf('index.php?option=com_docman&view=%s&category_slug=%s&alias=%s&Itemid=%d',
                    $view, $document->category_slug, $document->alias, $itemid ? $itemid : $document->itemid);
            }
        }
        elseif ($task === 'cat_view')
        {
            $category = KObjectManager::getInstance()->getObject('com://site/docman.model.categories')
                ->enabled(1)
                // Also need to redirect links for registered users
                //->access(KObjectManager::getInstance()->getObject('user')->getRoles())
                ->page('all')
                ->id($id)
                ->fetch();

            if (!$category->isNew())
            {
                $url = sprintf('index.php?option=com_docman&view=list&slug=%s&Itemid=%d',
                    $category->slug, $itemid ? $itemid : $category->itemid);
            }
        }

        if ($url)
        {
            if (version_compare(JVERSION, '3.2', '<')) {
                $app->redirect(JRoute::_($url, false), '', 'message', true);
            } else {
                $app->redirect(JRoute::_($url, false), true);
            }
        }
    }
}