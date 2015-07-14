<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Used by the document controller to make an image thumbnail out of the file if possible
 */
class ComDocmanControllerBehaviorImage extends KControllerBehaviorAbstract
{
    /**
     * If set to true in before.edit, after.edit will regenerate the thumbnail
     */
    protected $_regenerate;

    protected $_thumbnail_size;

    protected static $_library_loaded;

    protected static $_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->setThumbnailSize(KConfig::unbox($config->thumbnail_size));

        if (!self::$_library_loaded) {
            @ini_set('memory_limit', '256M');

            //Load the library
            $this->getService('koowa:loader')->loadIdentifier('com://admin/files.helper.phpthumb.phpthumb');

            self::$_library_loaded = true;
        }

        $path = JPATH_ROOT.'/joomlatools-files/docman-images/generated';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'thumbnail_size' => array('x' => 512, 'y' => 512)
        ));

        parent::_initialize($config);
    }

    protected function _generateThumbnail($file)
    {
        try {
            $thumb = PhpThumbFactory::create($file)->setOptions(array('jpegQuality' => 50));
        }
        catch (Exception $e) {
            // GD is not available
            return false;
        }

        $thumb_size = $this->getThumbnailSize();

        $thumb->resize($thumb_size['x'], $thumb_size['y']);

        ob_start();
        echo $thumb->getImageAsString();
        $str = ob_get_clean();

        if ($str) {
            $file = new SplTempFileObject();
            $file->fwrite($str);

            return $file;
        }

        return false;
    }

    protected function _saveThumbnail(KDatabaseRowInterface $row)
    {
        if ($row->storage_type !== 'file' || !in_array($row->extension, self::$_extensions)) {
            return false;
        }

        $thumbnail = $this->_generateThumbnail($row->storage->fullpath);

        if ($thumbnail)
        {
            try
            {
                $controller = $this->getService('com://admin/files.controller.file', array(
                        'request' => array('container' => 'docman-images')
                ));

                $image = $controller->post(array(
                    'file' => $thumbnail,
                    'name' => $this->getDefaultFilename($row),
                    'folder' => '',
                    'overwrite' => true
                ));

                $row->image = $image->path;
                $row->save();
            } catch (KControllerException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a thumbnail for new files
     *
     * @param KCommandContext $context
     */
    protected function _afterAdd(KCommandContext $context)
    {
        if ($context->status !== 201 || !empty($context->result->image)
            || !$context->data->automatic_thumbnail) {
            return;
        }

        $this->_saveThumbnail($context->result);
    }

    /**
     * Figure out if the file has changed and if so regenerate the thumbnail on after save
     * @param KCommandContext $context
     */
    protected function _beforeEdit(KCommandContext $context)
    {
        $item = $this->getModel()->getItem();

        if ($context->data->automatic_thumbnail && (empty($item->image) || $item->image !== $this->getDefaultFilename($item))
            || ($context->data->storage_path && ($item->storage_path !== $context->data->storage_path) && $item->image === $this->getDefaultFilename($item))
        ) {
            $this->_regenerate = true;
        }
    }

    protected function _afterEdit(KCommandContext $context)
    {
        if ($context->status < 200 || $context->status >= 300) {
            return;
        }

        if ($this->_regenerate) {
            $source = $context->result instanceof KDatabaseRowInterface ? array($context->result) : $context->result;
            foreach ($source as $row) {
                if ($this->_saveThumbnail($row)) {
                    $row->image = $this->getDefaultFilename($row);
                    $row->save();
                }
            }
        }
    }

    /**
     * Remove the attached thumbnail
     *
     * @param KCommandContext $context
     */
    protected function _afterDelete(KCommandContext $context)
    {
        $data = $context->result;
        if ($data instanceof KDatabaseRowInterface) {
            $data = array($data);
        }

        foreach ($data as $row)
        {
            if ($row->image !== $this->getDefaultFilename($row)) {
                continue;
            }

            try
            {
                $controller = $this->getService('com://admin/files.controller.file', array(
                    'request' => array('container' => 'docman-images')
                ))->name($row->image);

                $image = $controller->delete(array(
                    'name' => $row->image
                ));
            } catch (KControllerException $e) {
                return;
            }
        }
    }

    public function getDefaultFilename($row)
    {
        return 'generated/'.md5($row->id).'.png';
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
}
