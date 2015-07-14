<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelEntityDocument extends KModelEntityRow
{
    /**
     * image_extensions
     *
     * @var array
     */
    public static $image_extensions = array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'xbm', 'bmp');

    /**
     * audio extensions
     *
     * @var array
     */
    public static $audio_extensions = array('mp3', '3gp', 'act', 'aiff', 'aac', 'amr', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'm4a', 'm4p', 'ogg', 'oga', 'ra', 'rm', 'raw', 'tta', 'vox', 'wav', 'wma', 'wv', 'webm');

    /**
     * video extensions
     *
     * @var array
     */
    public static $video_extensions = array('webm','mkv','flv','vob','ogv','ogg','avi','rm','rmvb','mp4','m4p','m4v','asf','mpg','mpeg','mpv','mpe','3gp','3g2','roq','nsv');

    /**
     * executable extensions
     *
     * @var array
     */
    public static $executable_extensions = array('exe','bat','bin','apk','msi');

    public function save()
    {
        $this->storage_path = trim($this->storage_path);

        if ($this->isNew() && empty($this->storage_type)) {
            $this->storage_type = 'file';
        }

        if (!in_array($this->storage_type, array('file', 'remote')))
        {
            $this->setStatusMessage($this->getObject('translator')->translate('Storage type is not available'));
            $this->setStatus(KDatabase::STATUS_FAILED);

            return false;
        }

        if ($this->storage_type == 'remote')
        {
            $schemes = $this->getSchemes();
            $scheme  = parse_url($this->storage_path, PHP_URL_SCHEME);

            if (isset($schemes[$scheme]) && $schemes[$scheme] === false)
            {
                $this->setStatusMessage($this->getObject('translator')->translate('Storage type is not allowed'));
                $this->setStatus(KDatabase::STATUS_FAILED);

                return false;
            }
        }

        if (empty($this->docman_category_id))
        {
            if ($this->isNew())
            {
                $this->setStatusMessage($this->getObject('translator')->translate('Category cannot be empty'));
                $this->setStatus(KDatabase::STATUS_FAILED);

                return false;
            }
            else
            {
                unset($this->docman_category_id);
                unset($this->_modified['docman_category_id']);
            }
        }

        if (!$this->getParameters()->icon)
        {
            $icon = $this->getIcon($this->extension);

            if (empty($icon)) {
                $icon = 'default';
            }

            $this->getParameters()->icon = $icon;
        }

        return parent::save();
    }

    public function delete()
    {
        $result = parent::delete();

        // Also delete related file if not attached to any other document.
        if ($result && ($this->storage_type == 'file'))
        {
            $state = array(
                'storage_type' => 'file',
                'storage_path' => $this->storage_path
            );

            if (!$this->getObject('com://admin/docman.model.documents')->setState($state)->count())
            {
                $file = $this->storage;

                if (!$file->isNew())
                {
                    $file->delete();

                    // Clear com_files cache
                    JCache::getInstance('output', array('defaultgroup' => 'com_docman.files'))->clean();
                }
            }
        }

        return $result;
    }

    public function getStorageInfo()
    {
        if (!isset($this->_data['storage']))
        {
            if (!empty($this->_data['storage_type']))
            {
                $this->_data['storage'] = $this->getObject('com://admin/docman.model.storages')
                    ->container('docman-files')
                    ->storage_type($this->_data['storage_type'])
                    ->storage_path($this->_data['storage_path'])
                    ->fetch();
            }
            else  $this->_data['storage'] = null;
        }

        return $this->_data['storage'];
    }

    /**
     * Get a list of the supported streams.
     *
     * We use a whitelist approach to be secure against unknown streams
     *
     * @return array
     */
    public function getSchemes()
    {
        $streams = stream_get_wrappers();
        $allowed  = array(
            'http'  => true,
            'https' => true,
            'file'  => false,
            'ftp'   => false,
            'sftp'  => false,
            'php'   => false,
            'zlib'  => false,
            'data'  => false,
            'glob'  => false,
            'expect'=> false
        );

        if (in_array('file', $streams)) {
            $allowed['file'] = true;
        }

        // Following streams depend on allow_url_fopen
        if (ini_get('allow_url_fopen'))
        {
            foreach (array('ftp', 'sftp') as $stream)
            {
                if (in_array($stream, $streams)) {
                    $allowed[$stream] = true;
                }
            }
        }

        return $allowed;
    }

    public function getIcon($extension)
    {
        $extension = strtolower($extension);

        foreach (ComFilesTemplateHelperIcon::getIconExtensionMap() as $type => $extensions)
        {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return false;
    }

    public function getPropertyPublished()
    {
        return $this->isPublished();
    }

    public function getPropertyPending()
    {
        return $this->isPending();
    }

    public function getPropertyExpired()
    {
        return $this->isExpired();
    }

    public function getProperty($name)
    {
        if ($name === 'alias') {
            return isset($this->_data['alias']) ? $this->_data['alias'] : $this->id.'-'.$this->slug;
        }

        return parent::getProperty($name);
    }

    public function getPropertyImagePath()
    {
        if ($this->image)
        {
            $image = implode('/', array_map('rawurlencode', explode('/', $this->image)));

            return $this->getObject('request')->getSiteUrl().'/joomlatools-files/docman-images/'.$image;
        }

        return null;
    }

    public function getPropertyIcon()
    {
        $icon = $this->getParameters()->get('icon', 'default');

        // Backwards compatibility: remove .png from old style icons
        if (substr($icon, 0, 5) !== 'icon:' && substr($icon, -4) === '.png') {
            $icon = substr($icon, 0, strlen($icon)-4);
        }

        return $icon;
    }

    public function getPropertyIconPath()
    {
        $path = $this->icon;

        if (substr($path, 0, 5) === 'icon:')
        {
            $icon = implode('/', array_map('rawurlencode', explode('/', substr($path, 5))));
            $path = $this->getObject('request')->getSiteUrl().'/joomlatools-files/docman-icons/'.$icon;
        } else {
            $path = null;
        }

        return $path;
    }

    public function getPropertyStorage()
    {
        return $this->getStorageInfo();
    }

    public function getPropertyStatus()
    {
        return $this->isPublished() ? 'published' : ($this->isPending() ? 'pending' : 'expired');
    }

    public function getPropertyCategory()
    {
        return $this->getObject('com://admin/docman.model.categories')->id($this->docman_category_id)->fetch();
    }

    public function getPropertyDescriptionSummary()
    {
        $description = $this->description;
        $position    = strpos($description, '<hr id="system-readmore" />');
        if ($position !== false) {
            return substr($description, 0, $position);
        }

        return $description;
    }

    public function getPropertyDescriptionFull()
    {
        return str_replace('<hr id="system-readmore" />', '', $this->description);
    }

    public function getPropertySize()
    {
        if ($this->getStorageInfo()) {
            return $this->storage->size;
        }

        return null;
    }

    public function getPropertyExtension()
    {
        if ($this->getStorageInfo()) {
            return $this->storage->extension;
        }

        return null;
    }

    public function getPropertyMimetype()
    {
        if ($this->getStorageInfo()) {
            return $this->storage->mimetype;
        }

        return null;
    }

    public function isImage()
    {
        return in_array(strtolower($this->extension), self::$image_extensions);
    }

    public function isPending()
    {
        $result = false;

        if ((int) $this->publish_on)
        {
            $now = JFactory::getDate()->toUnix();
            $up  = JFactory::getDate($this->publish_on)->toUnix();
            return $up >= $now;
        }

        return $result;
    }

    public function isExpired()
    {
        $result = false;

        if ((int) $this->unpublish_on)
        {
            $now  = JFactory::getDate()->toUnix();
            $down = JFactory::getDate($this->unpublish_on)->toUnix();
            return $now >= $down;
        }

        return $result;
    }

    public function isPublished()
    {
        $result = !($this->isPending() || $this->isExpired());
        return $result;
    }

    public function isTopSecret()
    {
        return false;
    }

    /**
     * Returns the kind of the file
     *
     * Used in RSS:Media (audio, image, video, executable, document)
     *
     * @return string
     */
    public function getPropertyKind()
    {
        $result = null;

        if ($this->getStorageInfo())
        {
            $result = 'document';

            if (in_array($this->extension, self::$audio_extensions)) {
                $result = 'audio';
            }
            elseif (in_array($this->extension, self::$video_extensions)) {
                $result = 'video';
            }
            elseif (in_array($this->extension, self::$image_extensions)) {
                $result = 'image';
            }
            elseif (in_array($this->extension, self::$executable_extensions)) {
                $result = 'executable';
            }
        }

        return $result;
    }

}
