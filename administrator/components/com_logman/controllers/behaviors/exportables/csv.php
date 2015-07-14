<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerBehaviorExportableCsv extends ComLogmanControllerBehaviorExportable
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array('behavior' => 'com://admin/logman.database.behavior.exportable'));
        parent::_initialize($config);
    }

    protected function _write(KConfig $config)
    {
        if (!$this->getRequest()->offset) {
            $config->data = $this->getView()->getHeader() . $config->data; // Append CSV header.
        }

        parent::_write($config);

        // Set the file location in the session.
        KRequest::set($this->_getSessionContainer(), $config->target);
    }

    protected function _beforeGet(KCommandContext $context)
    {
        $request = $this->getRequest();
        if (($request->format == 'file') && $request->export)
        {
            $file = KRequest::get($this->_getSessionContainer(), 'raw', null);
            if (!is_null($file))
            {
                $view = $this->getView();
                // Set the view.
                $view->path     = $file;
                $view->filename = basename($file);
                // Clear session info.
                KRequest::set($this->_getSessionContainer(), null);
            }
        }

        parent::_beforeGet($context);
    }

    /**
     * Session container getter.
     *
     * Provides a session variable for storing the temporary location of the exported file.
     *
     * @return string
     */
    protected function _getSessionContainer()
    {
        return 'session.' . $this->getMixer()->getIdentifier() . '.' . $this->_format . '.export';
    }
}