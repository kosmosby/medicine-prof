<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerDocument extends ComDefaultControllerDefault
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->registerCallback('after.read', array($this, 'afterRead'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'behaviors' => array('aclable', 'image'),
            'limit' => array('max' => 100000)
        ));

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        /*
         * If there is a storage_path array in the post, set it as a GET parameter
         *
         * This is used to circumvent the URL size exceeding problem (after 2k bytes)
         * that happens in "create documents" screen
         */
        if (KRequest::has('post.storage_path'))
        {
            $paths = KRequest::get('post.storage_path', 'raw');

            if (is_array($paths)) {
                $request->storage_path = $paths;
            }
        }

        return $request;
    }

    public function afterRead(KCommandContext $context)
    {
        $request = $this->getRequest();
        $view = $this->getView();

        if ($context->result->isNew()) {
            if ($request->format == 'html' && $view->getName() == 'document' && $view->getLayout() == 'form') {
                if (!empty($request->storage_path)) {
                    $context->result->storage_path = $request->storage_path;
                }
            }

            if ($request->storage_type) {
                $context->result->storage_type = $request->storage_type;
            }

            if ($request->category) {
                $context->result->docman_category_id = $request->category;
            }
        }
    }
}
