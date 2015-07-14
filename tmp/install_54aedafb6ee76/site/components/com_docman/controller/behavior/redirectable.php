<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorRedirectable extends KControllerBehaviorAbstract
{
    protected $_redirect_schemes;

    protected $_redirect_unknown;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_redirect_schemes = KObjectConfig::unbox($config->redirect_schemes);
        $this->_redirect_unknown = $config->redirect_unknown;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'redirect_schemes' => array('http', 'https'),
            'redirect_unknown' => true,
        ));

        parent::_initialize($config);
    }

    protected function _beforeRender(KControllerContextInterface $context)
    {
        $result = true;

        $document = $this->getMixer()->execute('read', $context);

        if ($document instanceof KModelEntityInterface && !$document->isNew())
        {
            if ($document->storage_type == 'remote')
            {
                // Use browser redirection for http/https links or if the path does not have a whitelisted stream wrapper
                $valid_scheme   =  in_array($document->storage->scheme, $this->_redirect_schemes);
                $unknown_scheme =  !array_key_exists($document->storage->scheme, $document->getSchemes());

                if ($valid_scheme || ($this->_redirect_unknown && $unknown_scheme))
                {
                    $context->document = $document;
                    $this->redirect($context);
                    $result = false;
                }
            }
        }
        else throw new KControllerExceptionResourceNotFound('Document not found');

        return $result;
    }

    protected function _actionRedirect(KControllerContextInterface $context)
    {
        $document = $context->document;

        if ($document->isHittable()) {
            $document->hit();
        }

        $context->response->setRedirect($document->storage_path);
    }
}