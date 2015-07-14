<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerSubmit extends ComDefaultControllerDefault
{
    /**
     * A reference to the uploaded file row
     * Used to delete the file if the add action fails
     */
    protected $_uploaded;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback('before.add', array($this, 'beforeAdd'));
        $this->registerCallback('after.add', array($this, 'afterAdd'));
        $this->registerCallback('after.save', array($this, 'afterSave'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'model' => 'documents'
        ));

        parent::_initialize($config);
    }

    public function beforeAdd(KCommandContext $context)
    {
        $data = $context->data;

        $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
        $page = JFactory::getApplication()->getMenu()->getItem($this->getRequest()->Itemid);

        if (!$page) {
            $context->setError(new KControllerException($translator->translate('Invalid menu item.')));

            return false;
        }

        foreach ($this->getModel()->getTable()->getColumns() as $key => $column) {
            if (!in_array($key, array('storage_type', 'title', 'description'))) {
                unset($data->$key);
            }
        }

        $data->docman_category_id = $page->params->get('category_id');
        $data->enabled = $page->params->get('auto_publish') ? 1 : 0;

        if (empty($data->storage_type)) {
            $data->storage_type = $data->storage_path_remote ? 'remote' : 'file';
        }

        if ($data->storage_type === 'file') {
            $file = KRequest::get('files.storage_path_file', 'raw');
            if (empty($file) || empty($file['name'])) {
                $context->setError(new KControllerException($translator->translate('You did not select a file to be uploaded.')));

                return false;
            }

            try {
                $controller = $this->getService('com://admin/files.controller.file', array(
                    'request' => array('container' => 'docman-files', 'Itemid' => $page->id)
                ));

                $this->_uploaded = $controller->add(array(
                    'file' => $file['tmp_name'],
                    'name' => $file['name'],
                    'folder' => $page->params->get('folder')
                ));

                $data->storage_path = $this->_uploaded->path;
            } catch (KControllerException $e) {
                $context->setError($e);

                return false;
            }

        } else {
            $data->storage_path = $data->{'storage_path_'.$data->storage_type};
        }
    }

    public function afterAdd(KCommandContext $context)
    {
        if ($context->status !== 201) {
            try {
                if ($this->_uploaded instanceof KDatabaseRowInterface) {
                    $this->_uploaded->delete();
                }
            } catch (KException $e) {
                // Well, we tried
            }
        } else {
            $this->sendNotifications($context);
        }
    }

    public function sendNotifications(KCommandContext $context)
    {
        $page = JFactory::getApplication()->getMenu()->getItem($this->getRequest()->Itemid);
        $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
        $emails = $page->params->get('notification_emails');
        if (empty($emails)) {
            return;
        }

        $emails = explode("\n", $emails);

        $config	= JFactory::getConfig();
        $from_name = $config->get('fromname');
        $mail_from = $config->get('mailfrom');
        $sitename = $config->get('sitename');
        $subject = $translator->translate('A new document was submitted for you to review on %sitename%', array(
            '%sitename%' => $sitename));

        $admin_link = JURI::root().'administrator/index.php?option=com_docman&view=documents';
        $title = $context->result->title;
        $admin_title = $translator->translate('Document Manager');

        foreach ($emails as $email) {
            $body = $translator->translate('Submit notification mail body', array(
                '%title%'    => $title,
                '%sitename%' => $sitename,
                '%url%'      => $admin_link,
                '%url_text%' => $admin_title
            ));
            JFactory::getMailer()->sendMail($mail_from, $from_name, $email, $subject, $body, true);
        }
    }

    public function afterSave(KCommandContext $context)
    {
        if ($context->status === 201) {
            $route = JRoute::_('index.php?option=com_docman&view=submit&layout=success&Itemid='.$this->getRequest()->Itemid, false);
            $this->setRedirect($route);
        }
    }
}
