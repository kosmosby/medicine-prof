<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerDoclink extends ComDefaultControllerResource
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        // Load the default model from frontend for itemid calculations
        KService::get('koowa:loader')->loadIdentifier('com://site/docman.model.default');
    }

    protected function _initalize(KConfig $config)
    {
        $config->append(array(
            'persistent' => false
        ));
        parent::_initialize($config);
    }

    /**
     * Passes the e_view parameter that Joomla sends in the request for the editor name.
     *
     * @see KControllerResource::getView()
     */
    public function getView()
    {
        $view = parent::getView();

        if ($view) {
            $view->assign('editor', $this->_request->e_name);
        }

        return $view;
    }
}
