<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerBehaviorFile_loggable extends ComLogmanControllerBehaviorLoggable
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array('title_column' => 'name'));
        parent::_initialize($config);
    }

    public function execute($name, KCommandContext $context)
    {
        $data = $context->result;

        // Exclude some container actions from being logged.
        if (($data instanceof KDatabaseRowAbstract) && !in_array($data->container->slug,
            array('docman-icons', 'docman-images'))
        ) {
            return parent::execute($name, $context);
        }
    }

    protected function _getActivityData(KDatabaseRowAbstract $row, $status, KCommandContext $context)
    {
        $clone = clone $row;
        // Use container:path as row identifier.
        $clone->id = $row->container->slug . ':' . $row->path;

        $data = parent::_getActivityData($clone, $status, $context);

        $data['metadata'] = array(
            'image'     => $row->isImage(),
            'width'     => $row->width,
            'height'    => $row->height,
            'size'      => $row->size,
            'name'      => $row->name,
            'folder'    => $row->folder,
            'path'      => $row->path,
            'container' => array(
                'id'    => $row->container->id,
                'slug'  => $row->container->slug,
                'title' => $row->container->title));

        return $data;
    }

    /**
     * This method is called with the current context to determine what identifier generates the event.
     *
     * This is useful in cases where the row is from another package or the actual action happens somewhere else.
     *
     * @param KCommandContext $context
     */
    public function getActivityIdentifier(KCommandContext $context)
    {
        $identifier = clone $context->caller->getIdentifier();

        if ($context->result->container instanceof ComFilesDatabaseRowContainer) {
            $container = explode('-', $context->result->container->slug);
            $container = $container[0];

            if ($container) {
                $identifier->package = $container;
            }
        }

        return $identifier;
    }
}