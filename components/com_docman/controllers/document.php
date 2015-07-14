<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerDocument extends ComDefaultControllerDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback(array('after.save', 'after.delete'), array($this, 'setStatusMessage'));
        $this->registerCallback(array('before.add', 'before.edit'),  array($this, 'checkSubmittedFolder'));

        $this->registerCallback('after.read',   array($this, 'prepopulateCategory'));
        $this->registerCallback('before.get',   array($this, 'checkDownloadLink'));
        $this->registerCallback('after.apply',  array($this, 'setRedirectAfterApply'));
        $this->registerCallback('after.delete', array($this, 'setRedirectAfterDelete'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array('aclable', 'image')
        ));

        parent::_initialize($config);
    }

    /**
     * Redirect back to the correct category after deleting an item
     *
     * @param KCommandContext $context
     */
    public function setRedirectAfterApply(KCommandContext $context)
    {
        $redirect = $this->getRedirect();
        $url      = $redirect['url'];

        unset($url->query['slug']);

        $this->setRedirect($url);
    }

    /**
     * Redirect back to the correct category after deleting an item
     *
     * @param KCommandContext $context
     */
    public function setRedirectAfterDelete(KCommandContext $context)
    {
        if ($context->status = KHttpResponse::NO_CONTENT)
        {
            $url = sprintf('index.php?view=list&slug=%s&Itemid=%d', $context->result->category_slug, $this->getRequest()->Itemid);
            $this->setRedirect(JRoute::_($url, false));
        }
    }

    /**
     * Check if user is trying to add a document in a folder allowed in the menu parameters
     *
     * @param KCommandContext $context
     */
    public function checkSubmittedFolder(KCommandContext $context)
    {
        if (!$this->canManage())
        {
            unset($context->data->created_by);
            unset($context->data->access);

            // Unset enabled too if it's not user's own document
            if ($this->getModel()->getItem()->created_by != $this->getRequest()->current_user) {
                unset($context->data->enabled);
            }
        }

        // Only run it if storage_path is local and being changed
        if (isset($context->data->storage_path) && !preg_match('#[a-z0-9]:/#i', $context->data->storage_path))
        {
            $page = JFactory::getApplication()->getMenu()->getItem($this->getRequest()->Itemid);

            if ($page->params->get('upload_folder'))
            {
                $folder = trim($page->params->get('upload_folder'), '/');

                if (strpos($context->data->storage_path, $folder) !== 0)
                {
                    unset($context->data->storage_path);
                    unset($context->data->storage_path_file);
                }
            }
        }
    }

    /**
     * Check to see if we need to redirect to the download view or a remote URL
     *
     * @param KCommandContext $context
     *
     * @return bool|void
     */
    public function checkDownloadLink(KCommandContext $context)
    {
        $row = $this->getModel()->getItem();

        // Redirect document view to download view if the title links are set as download in menu parameters
        if ($this->_request->view === 'document' && $this->_request->format === 'html'
                && $this->getView()->getLayout() === 'default')
        {
            $menu = JFactory::getApplication()->getMenu()->getActive();

            if ($menu->params->get('document_title_link') === 'download'
                    && in_array($menu->query['view'], array('list', 'filteredlist', 'document')))
            {
                $url = JRoute::_('index.php?option=com_docman&view=download&alias='.$row->alias.'&category_slug='.$row->category_slug.'&Itemid='.$menu->id, false);

                return JFactory::getApplication()->redirect($url);
            }
        }

        // Use browser redirection for http/https links or if the path does not have a whitelisted stream wrapper
        if ($this->_request->view == 'download' && $row->storage_type == 'remote')
        {
            if (substr($row->storage_path, 0, 4) === 'http' || !$row->hasStreamWrapper()) {
                return $this->_redirect($row->storage_path);
            }
        }

        return true;
    }

    /**
     * Redirects user to a given URL
     *
     * Uses JavaScript redirection if headers are already sent. Otherwise sends a 303 header.
     *
     * @param $url string A fully qualified URL
     */
    protected function _redirect($url)
    {
        // Strip out any line breaks.
        $url = preg_split("/[\r\n]/", $url);
        $url = $url[0];

        // If the headers have been sent, then we cannot send an additional location header
        // so we will output a javascript redirect statement.
        if (headers_sent())
        {
            echo "<script>document.location.href='" . htmlspecialchars($url) . "';</script>\n";
        }
        else
        {
            jimport('phputf8.utils.ascii');

            $document   = JFactory::getDocument();
            $user_agent = null;

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
            }

            if ((stripos($user_agent, 'MSIE') !== false || stripos($user_agent, 'Trident') !== false)
                && !utf8_is_ascii($url))
            {
                // MSIE type browser and/or server cause issues when url contains utf8 character,so use a javascript redirect method
                echo '<html><head><meta http-equiv="content-type" content="text/html; charset=' . $document->getCharset() . '" />'
                    . '<script>document.location.href=\'' . htmlspecialchars($url) . '\';</script></head></html>';
            }
            else
            {
                // All other browsers, use the more efficient HTTP header method
                header('HTTP/1.1 303 See other');
                header('Location: ' . $url);
                header('Content-Type: text/html; charset=' . $document->getCharset());
            }
        }

        JFactory::getApplication()->close();
    }

    /**
     * If the category slug is supplied in the URL, prepopulate it in the new document form
     *
     * @param KCommandContext $context
     */
    public function prepopulateCategory(KCommandContext $context)
    {
        if ($context->result->isNew())
        {
            $request = $this->getRequest();
            $view = $this->getView();

            if ($request->format == 'html' && $view->getName() == 'document' && $view->getLayout() == 'form')
            {
                $slug = $request->category_slug;
                if (empty($slug) && $request->path) {
                    $slug = explode('/', $request->path);
                }

                if (empty($slug))
                {
                    $menu = JFactory::getApplication()->getMenu()->getActive();
                    if ($menu->query['view'] === 'list') {
                        $slug = $menu->query['slug'];
                    }
                }

                if (!empty($slug)) {
                    $id = $this->getService('com://site/docman.model.categories')->slug($slug)->getItem()->id;
                    $context->result->docman_category_id = $id;
                }
            }
        }
    }

    /**
     * Set status messages after save and delete actions
     *
     * @param KCommandContext $context
     */
    public function setStatusMessage(KCommandContext $context)
    {
        $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
        $action = strtolower($translator->translate($context->action));
        $name   = $this->getIdentifier()->name;
        $item_type = ucfirst($translator->translate(KInflector::singularize($name)));
        $rowset = ($context->result instanceof KDatabaseRowAbstract) ? array($context->result) : $context->result;
        $failed = false;

        foreach ($rowset as $row) {
            if ($row->getStatus() == KDatabase::STATUS_FAILED) {
                $this->_redirect        = KRequest::referrer();
                $this->_redirect_type   = 'error';

                if ($row->getStatusMessage()) {
                    $this->_redirect_message = $row->getStatusMessage();
                } else {
                    $this->_redirect_message = ucfirst($translator->translate('%item_type% %action% failed', array(
                        '%item_type%' => $item_type,
                        '%action%'    => $action
                    )));
                }

                $failed = true;
                break;
            }
        }

        if (!$failed && count($rowset) === 1) {
            $this->_redirect_message = ucfirst($translator->translate('%item_type% %action% successful', array(
                '%item_type%' => $item_type,
                '%action%'    => $action
            )));
        }
    }
}
