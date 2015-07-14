<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Used by the document controller to make an image thumbnail out of the file if possible
 */
class ComDocmanControllerBehaviorThumbnailable extends KControllerBehaviorAbstract
{
    /**
     * If set to true in before.edit, after.edit will regenerate the thumbnail
     */
    protected $_regenerate;

    protected $_thumbnail_size;

    protected static $_library_loaded;

    protected static $_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

    /**
     * @var mixed The thumbnail container.
     */
    protected $_container;

    /**
     * @var string The folder (relative to container's root) where generated thumbnails will be stored.
     */
    protected $_folder;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->setThumbnailSize(KObjectConfig::unbox($config->thumbnail_size));

        if (!self::$_library_loaded)
        {
            @ini_set('memory_limit', '256M');

            //Load the library
            require_once JPATH_LIBRARIES.'/koowa/components/com_files/helper/phpthumb/phpthumb.php';

            self::$_library_loaded = true;
        }

        $this->_container = $config->container;
        $this->_folder    = $config->folder;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'container'      => 'docman-images',
            'folder'         => 'generated',
            'thumbnail_size' => array('x' => 512, 'y' => 512)
        ));

        parent::_initialize($config);
    }

    public function getDefaultFilename($entity, $path = true)
    {
        $filename = md5($entity->id) . '.png';

        if ($path) {
            $filename = $this->_folder . '/' . $filename;
        }

        return $filename;
    }

    public function getThumbnailSize()
    {
        return $this->_thumbnail_size;
    }

    /**
     * @param array $size An array with x and y properties
     */
    public function setThumbnailSize(array $size)
    {
        $this->_thumbnail_size = $size;
        return $this;
    }

    protected function _getContainer()
    {
        if (!$this->_container instanceof KModelEntityInterface)
        {
            $container = $this->getObject('com:files.model.containers')
                ->slug($this->_container)
                ->fetch();

            $this->_setContainer($container);
        }

        return $this->_container;
    }

    protected function _setContainer(KModelEntityInterface $container)
    {
        $this->_container = $container;
        $folder           = $container->fullpath . '/' . $this->_folder;

        if (!file_exists($folder))
        {
            jimport('joomla.filesystem.folder');
            JFolder::create($folder);
        }

        $container->folder = $folder . '/';

        return $this;
    }

    protected function _createThumbnail($file)
    {
        try {
            $thumb = PhpThumbFactory::create($file)->setOptions(array('jpegQuality' => 50));
        } catch (Exception $e) {
            return false;
        }

        $thumb_size = $this->getThumbnailSize();

        $thumb->resize($thumb_size['x'], $thumb_size['y']);

        ob_start();
        echo $thumb->getImageAsString();
        $str = ob_get_clean();

        if ($str)
        {
            $file = new SplTempFileObject();
            $file->fwrite($str);

            return $file;
        }

        return false;
    }

    protected function _saveThumbnail(KModelEntityInterface $entity)
    {
        $result = false;
        $config = $this->getObject('com://admin/docman.model.configs')->fetch();

        if ($config->thumbnails)
        {
            if ($entity->storage_type == 'file' && in_array($entity->extension, self::$_extensions))
            {
                $thumbnail = $this->_createThumbnail($entity->storage->fullpath);

                if ($thumbnail)
                {
                    $container = $this->_getContainer();

                    try
                    {
                        $data = array(
                            'file'      => $thumbnail,
                            'name'      => $this->getDefaultFilename($entity, false),
                            'folder'    => $this->_folder,
                            'overwrite' => true
                        );

                        $image = $this->getObject('com:files.controller.file', array(
                            'behaviors' => array(
                                'permissible' => array(
                                    'permission' => 'com://admin/docman.controller.permission.file'
                                )
                            )))->container($container->slug)->add($data);

                        $entity->image = $image->path;
                        $result = (bool) $entity->save();

                    }
                    catch (KControllerException $e) {}
                }
            }
        }

        return $result;
    }

    /**
     * Create a thumbnail for new files
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterAdd(KControllerContextInterface $context)
    {
        if ($context->response->getStatusCode() !== 201 || !empty($context->result->image) || !$context->result->automatic_thumbnail) {
            return;
        }

        $this->_saveThumbnail($context->result);
    }

    /**
     * Figure out if the file has changed and if so regenerate the thumbnail on after save
     *
     * @param KControllerContextInterface $context
     */
    protected function _beforeEdit(KControllerContextInterface $context)
    {
        $item = $this->getModel()->fetch();
        $data = $context->request->data;

        $filename = $this->getDefaultFilename($item);

        // None or custom to automatic thumbnail.
        if ($data->automatic_thumbnail && ($data->image !== $filename)) {
            $this->_regenerate = true;
        }

        if ($data->image && ($data->image === $filename))
        {
            // Force a re-generate if the document file changes.
            if ($data->storage_path && ($item->storage_path !== $data->storage_path)) {
                $this->_regenerate = true;
            }

            // Make sure that the thumb still exists ... re-generate if it doesn't.
            if (!file_exists($this->_getContainer()->fullpath . '/' . $filename)) {
                $this->_regenerate = true;
            }
        }
    }

    protected function _afterEdit(KControllerContextInterface $context)
    {
        $status_code = $context->getResponse()->getStatusCode();

        if ($status_code < 200 || $status_code >= 300) {
            return;
        }

        if ($this->_regenerate)
        {
            foreach ($context->result as $entity) {
               $this->_saveThumbnail($entity);
            }
        }
    }

    /**
     * Remove the attached thumbnail
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterDelete(KControllerContextInterface $context)
    {
        foreach ($context->result as $entity)
        {
            $default = $this->getDefaultFilename($entity);

            $thumbnails = array($default);

            // Check if a custom thumbnail is set.
            if (($thumbnail = $entity->image) && ($thumbnail != $default))
            {
                // See if the custom image is being used on another document and mark it for deletion.
                if ($this->getObject('com://admin/docman.model.documents')->image($thumbnail)->count() == 0) {
                    $thumbnails[] = $thumbnail;
                }
            }

            // Delete thumbnails.
            foreach ($thumbnails as $thumbnail)
            {
                if (file_exists($this->_getContainer()->fullpath . '/' . $thumbnail))
                {
                    try
                    {
                        $this->getObject('com:files.controller.file')
                            ->container('docman-images')
                            ->folder(dirname($thumbnail))
                            ->name(basename($thumbnail))
                            ->delete();

                    } catch (KControllerException $e) {
                        // Do nothing.
                    }
                }
            }

            // Reset image on all documents making use of the default thumbnail.
            $documents = $this->getObject('com://admin/docman.model.documents')->image($default)->fetch();

            if (count($documents))
            {
                $documents->image = "";
                $documents->save();
            }
        }
    }
}
