<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerUserlist extends ComDocmanControllerList
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.render', '_checkUser');
    }

    public function getFormats()
    {
        $result = parent::getFormats();

        $rss = array_search('rss', $result);

        if ($rss !== false) {
            unset($result[$rss]);
        }

        return $result;
    }

    protected function _checkUser(KControllerContextInterface $context)
    {
        if (!$context->user->isAuthentic())
        {
            $message  = $this->getObject('translator')->translate('You need to be logged in to access your document list');
            $url      = $context->getRequest()->getUrl();
            $redirect = JRoute::_('index.php?option=com_users&view=login&return='.base64_encode($url), false);

            JFactory::getApplication()->redirect($redirect, $message, 'error');
        }
    }
}
