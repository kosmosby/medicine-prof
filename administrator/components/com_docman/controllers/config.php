<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerConfig extends ComDefaultControllerDefault
{
    /**
     * We always need to call edit since config is never new
     */
    protected function _actionAdd(KCommandContext $context)
    {
        return $this->_actionEdit($context);
    }

    /**
     * Avoid getting redirected to the configs view. It doesn't exist.
     */
    public function setRedirect($url, $msg = null, $type = 'message')
    {
        if ($url) {
            $url = $this->getService('koowa:http.url',array('url' => $url));
            $query = $url->getQuery(true);
            if (isset($query['view']) && $query['view'] === 'configs') {
                $query['view'] = 'documents';
            }
            $url->setQuery($query);
        }

        return parent::setRedirect($url, $msg, $type);
    }
}
