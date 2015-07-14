<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowDocument extends KDatabaseRowDefault
{
    public static $image_extensions = array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'xbm', 'bmp');

    protected static $_container;

    protected static $_icon_extension_map = array(
        'archive'     => array('7z','gz','rar','tar','zip'),
        'audio'       => array('aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'),
        'document'    => array('doc','docx','rtf','txt','ppt','pptx','pps','xml'),
        'image'       => array('bmp','gif','jpg','jpeg','png','psd','tif','tiff'),
        'pdf'         => array('pdf'),
        'spreadsheet' => array('xls', 'xlsx', 'ods'),
        'video'       => array('3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv')
    );

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->mixin($this->getTable()->getBehavior('aclable'));
    }

    public function getStorageInfo()
    {
        if (!isset($this->_data['storage'])) {
            if (!empty($this->_data['storage_type'])) {
                if (empty(self::$_container)) {
                    self::$_container = $this->getService('com://admin/files.model.containers')->slug('docman-files')->getItem();
                }

                $this->_data['storage'] = $this->getService('com://admin/docman.model.storages')
                    ->container(self::$_container)
                    ->storage_type($this->_data['storage_type'])
                    ->storage_path($this->_data['storage_path'])
                    ->getItem();
            } else {
                $this->_data['storage'] = null;
            }

        }

        return $this->_data['storage'];
    }

    public function save()
    {
        $this->storage_path = trim($this->storage_path);

        $translator = $this->getService('translator')->getTranslator($this->getIdentifier());

        if (!in_array($this->storage_type, array('file', 'remote')) && ($this->isModified('storage_type') || $this->isNew())) {
            $this->setStatusMessage($translator->translate('Storage type is not available'));
            $this->setStatus(KDatabase::STATUS_FAILED);

            return false;
        }

        if (empty($this->docman_category_id)) {
            if ($this->isNew()) {
                $this->setStatusMessage($translator->translate('Category cannot be empty'));
                $this->setStatus(KDatabase::STATUS_FAILED);

                return false;
            } else {
                unset($this->docman_category_id);
                unset($this->_modified['docman_category_id']);
            }
        }

        if ($this->isNew() && !empty($this->params) && empty($this->params['icon']) && $this->storage_type === 'file')
        {
             $icon = $this->getIconForExtension($this->extension);

             if (empty($icon)) {
                 $icon = 'default';
             }

             $this->params['icon'] = $icon.'.png';
        }

        return parent::save();
    }

    public function isImage()
    {
        return in_array(strtolower($this->extension), self::$image_extensions);
    }

    public function isPending()
    {
        $result = false;

        if ((int) $this->publish_on) {
            $now = JFactory::getDate()->toUnix();
            $up  = JFactory::getDate($this->publish_on)->toUnix();

            return $up >= $now;
        }

        return $result;
    }

    public function isExpired()
    {
        $result = false;

        if ((int) $this->unpublish_on) {
            $now  = JFactory::getDate()->toUnix();
            $down = JFactory::getDate($this->unpublish_on)->toUnix();

            return $now >= $down;
        }

        return $result;
    }

    public function isPublished()
    {
        return !($this->isPending() || $this->isExpired());
    }

    public function isTopSecret()
    {
        return false;
    }

    public function __get($column)
    {
        if ($column == 'image_path' && !isset($this->_data['image_path'])) {
            return $this->image ? KRequest::root().'/joomlatools-files/docman-images/'.$this->image : null;
        }

        if ($column == 'icon')
        {
            if (!is_object($this->params)) {
                $this->params = json_decode($this->params);
            }
            $icon = $this->params->icon ? $this->params->icon : 'icon:default.png';

            return $icon;
        }

        if ($column == 'icon_path')
        {
            $icon = $this->icon;
            if (substr($icon, 0, 5) === 'icon:') {
                $icon = '/joomlatools-files/docman-icons/'.substr($icon, 5);
            } else {
                $icon = '/media/com_docman/images/icons/'.$icon;
            }

            return KRequest::root().$icon;
        }

        if ($column == 'storage' && !isset($this->_data['storage'])) {
            return $this->getStorageInfo();
        }

        if (in_array($column, array('published', 'pending', 'expired'))) {
            return $this->{'is'.ucfirst($column)}();
        }

        if ($column === 'status') {
            return $this->isPublished() ? 'published' : ($this->isPending() ? 'pending' : 'expired');
        }

        if (in_array($column, array('size', 'mimetype', 'extension')) && $this->getStorageInfo()) {
            return $this->storage->$column;
        }

        if ($column == 'category') {
            return $this->getService('com://admin/docman.model.categories')->id($this->docman_category_id)->getItem();
        }

        if ($column === 'description_summary')
        {
            $description = $this->description;
            $position    = strpos($description, '<hr id="system-readmore" />');
            if ($position !== false) {
                return substr($description, 0, $position);
            }

            return $description;
        }

        if ($column === 'description_full') {
            return str_replace('<hr id="system-readmore" />', '', $this->description);
        }

        return parent::__get($column);
    }

    /**
     * Returns true if the stored path has a stream wrapper that we should use
     *
     * @return bool
     */
    public function hasStreamWrapper()
    {
        return array_key_exists($this->storage->scheme, $this->getStreamWrappers());
    }

    /**
     * Returns true if there is an enabled stream wrapper for the scheme
     *
     * @return bool
     */
    public function hasAvailableStreamWrapper()
    {
        $return = false;

        if ($this->hasStreamWrapper())
        {
            $streams = $this->getStreamWrappers();
            $return = $streams[$this->storage->scheme];
        }

        return $return;
    }

    /**
     * Returns a list of streams that is allowed in the component
     *
     * This follows a whitelist approach to be secure against unknown streams
     *
     * @return array
     */
    public function getStreamWrappers()
    {
        $streams = stream_get_wrappers();
        $return  = array(
            'file'      => false,
            'http'      => false,
            'https'     => false,
            'ftp'       => false,
            'ssh2.sftp' => false
        );

        if (in_array('file', $streams)) {
            $return['file'] = true;
        }

        // Following streams depend on the
        if (ini_get('allow_url_fopen'))
        {
            foreach (array('http', 'https', 'ftp', 'ssh2.sftp') as $stream)
            {
                if (in_array($stream, $streams)) {
                    $return[$stream] = true;
                }
            }
        }

        return $return;
    }

    public function getIconForExtension($extension)
    {
        $extension = strtolower($extension);

        foreach (self::$_icon_extension_map as $type => $extensions)
        {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return false;
    }
}
