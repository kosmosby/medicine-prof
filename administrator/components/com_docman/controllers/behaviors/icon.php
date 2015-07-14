<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Used by the file controller to make icons smaller if they are bigger than a certain size
 */
class ComDocmanControllerBehaviorIcon extends KControllerBehaviorAbstract
{
    protected static $_library_loaded;

    protected $_thumbnail_size;

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
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'thumbnail_size' => array('x' => 64, 'y' => 64)
        ));

        parent::_initialize($config);
    }

    protected function _beforeAdd(KCommandContext $context)
    {
        $container = $this->getModel()->container;
        if ($container instanceof ComFilesDatabaseRowContainer) {
            $size = $container->getParameters()->thumbnail_size;

            if (isset($size['x']) && isset($size['y'])) {
                $this->setThumbnailSize($size);
            }
        }

        @ini_set('memory_limit', '256M');

        $source = $context->data->file;
        if ($source)
        {
            try {
                $thumb = PhpThumbFactory::create($source);
            }
            catch (Exception $e) {
                // GD is not available
                return;
            }

            $thumb_size = $this->getThumbnailSize();

            $thumb->resize($thumb_size['x'], $thumb_size['y']);

            ob_start();
            echo $thumb->getImageAsString();
            $str = ob_get_clean();
            $str = sprintf('data:%s;base64,%s', 'image/png', base64_encode($str));

            $context->data->thumbnail_string = $str;
        }
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
