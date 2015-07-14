<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerFile extends ComDefaultControllerResource
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'request'   => array(
                'view'  => 'files'
            )
        ));

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        if (KRequest::has('post.paths'))
        {
            $paths = KRequest::get('post.paths', 'raw');

            if ($paths) {
                $request->paths = $paths;
            }
        }

        if (!in_array($request->container, array('docman-files', 'docman-icons', 'docman-images'))) {
            $request->container = 'docman-files';
        }

        return $request;
    }
}
