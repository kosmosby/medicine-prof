<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyFiles extends ComLogmanActivityMessageStrategyDefault
{
    /**
     * @var bool Indicates if the template for modal windows is already loaded.
     */
    static protected $_loaded = false;

    protected $_translator;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_translator = $this->getService($config->translator);
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array('translator' => 'com://admin/logman.translator'));
        parent::_initialize($config);
    }

    /**
     * @var array Holds a copy of loaded file containers.
     */
    static protected $_containers = array();

    public function getContent(KConfig $config)
    {
        $content = '';

        if (!self::$_loaded) {
            // Push file template to be used on modal windows.
            $content       = $this->_getFileTemplate();
            self::$_loaded = true;
        }

        return $content;
    }

    public function getIcon(KConfig $config)
    {
        $activity = $config->activity;

        if ($activity->name == 'file' && $activity->action == 'add') {
            $config->append(array('class' => 'icon-upload'));
        }

        return parent::getIcon($config);
    }

    public function getText(KConfig $config)
    {
        $activity = $config->activity;

        $config->append(array('subject' => '%resource% %name%'));

        if ($activity->name == 'file' && ($metadata = $activity->metadata)) {
            // Add target info.
            $config->append(array('target' => '%folder%'));
        }

        return parent::getText($config);
    }

    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        $metadata = $activity->metadata;

        $path = $metadata->name;

        if ($folder = $metadata->folder) {
            $path = $folder . '/' . $path;
        }

        switch ($activity->name) {
            case 'file':
                if ($activity->package == 'docman') {
                    // Made relative to site root since routing is disabled for this link (see comment below on ::_getTitleConfig).
                    $url = 'administrator/index.php?option=com_' . $activity->package . '&view=file&format=raw&routed=1&name=' . rawurlencode($path) . '&container=' . $metadata->container->slug;
                } else {
                    // Generic through com_files.
                    $container = $this->getService('com://admin/files.model.containers')->id($metadata->container->id)
                        ->getItem();

                    $url = array($container->path_value);
                    if ($folder = $metadata->folder) $url[] = $folder;
                    $url[] = $metadata->name;

                    $url = implode('/', $url);
                }
                break;
            case 'folder':
                // Made relative to site root since routing is disabled for this link (see comment below on ::_getTitleConfig).
                $url = 'administrator/index.php?option=com_' . $activity->package . '&view=files&folder=' . rawurlencode($path) . '&container=' . $metadata->container->slug;
                break;
            default:
                $url = '';
                break;
        }

        return $url;
    }

    protected function _getFolder(KConfig $config)
    {
        $activity = $config->activity;
        $metadata = $activity->metadata;

        // Add folder info. Use container title as folder.
        $folder = $metadata->container->title;

        $config->append(array('text' => $folder, 'translate' => false));

        if ($this->_resourceExists(array('activity' => $activity))) {
            // If subject exists, so does its target (folder) ... make it linkable.
            $config->append(array(
                'link' => array(
                    'url'   => 'administrator/index.php?option=com_' . $activity->package . '&view=files&folder=' . rawurlencode($metadata->folder) . '&container=' . $metadata->container->slug,
                    'route' => false)));
        }

        return $this->_getParameter($config);
    }

    protected function _getAction(KConfig $config)
    {
        $activity = $config->activity;

        if ($activity->name == 'file' && $activity->action == 'add') {
            $config->append(array('text' => 'uploaded'));
        }

        return parent::_getAction($config);
    }

    protected function _getResource(KConfig $config)
    {
        $activity = $config->activity;

        if ($activity->name == 'file' && ($metadata = $activity->metadata) && $metadata->image) {
            $config->text = 'image';
        }

        return parent::_getResource($config);
    }

    protected function _getName(KConfig $config)
    {
        // Disable routing for avoiding JRoute::_ from decoding the URL. Links passed
        $config->append(array('link' => array('route' => false)));

        $activity = $config->activity;

        if ($activity->name == 'file' && ($metadata = $activity->metadata)) {

            if ($metadata->image) {
                $config->append(array(
                    'link' => array(
                        'attribs' => array(
                            'data-width'  => $metadata->width,
                            'data-height' => $metadata->height))));
            }

            $config->append(array(
                'link' => array(
                    'attribs' => array(
                        'class'     => 'logman-file',
                        'data-name' => $metadata->name,
                        'data-size' => $metadata->size))));
        }

        return $this->_getTitle($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        $activity = $config->activity;

        if ($activity->status == 'deleted') {
            $result = false;
        } elseif (($metadata = $activity->metadata) && ($container = $this->_getContainer($metadata->container->slug))) {
            $path   = $container->path . '/' . $metadata->path;
            $result = (bool) file_exists($path);
        } else {
            // Assume deleted.
            $result = false;
        }

        return $result;
    }

    protected function _getContainer($slug)
    {
        if (!isset(self::$_containers[$slug])) {
            if (class_exists('ComFilesModelContainers')) {
                $container = $this->getService('com://admin/files.model.containers')->slug($slug)->getItem();
            } else {
                $container = null;
            }
            self::$_containers[$slug] = $container;
        }
        return self::$_containers[$slug];
    }

    protected function _getFileTemplate()
    {
        $template   = <<<EOD
<script src="media://com_logman/js/logman.js"/>
<script src="media://com_files/js/ejs/ejs.js"/>
<script src="media://com_files/js/files.utilities.js"/>
<script type="text/javascript">
    window.addEvent('domready', function() {
        Logman.Files.init();
    });
</script>
<textarea style="display: none" id="logman-file-template">
<div class="logman-file-preview">
    [% if (typeof image !== 'undefined') {
        var ratio = 400 / (width > height ? width : height); %]
        <img src="[%=url%]" alt="[%=name%]" border="0" style="
             width: [%=Math.min(ratio*width, width)%]px;
             height: [%=Math.min(ratio*height, height)%]px
         "/>
    [% } else { %]
        <img src="media://com_files/images/document-64.png" width="64" height="64" alt="[%=name%]" border="0" />
    [% } %]

    <div class="btn-toolbar">
        [% if (typeof image !== 'undefined') { %]
        <a class="btn btn-mini" href="[%=url%]" target="_blank">
            <i class="icon-eye-open"></i>[%=view-label%]
        </a>
        [% } else { %]
        <a class="btn btn-mini" href="[%=url%]" target="_blank" download="[%=name%]">
            <i class="icon-download"></i>[%=download-label%]
        </a>
        [% } %]
    </div>
</div>
<div class="logman-file-details">
    <table class="table table-condensed parameters">
        <tbody>
            <tr>
                <td class="detail-label">[%=name-label%]</td>
                <td>[%=name%]</td>
            </tr>
            <tr>
                <td class="detail-label">[%=size-label%]</td>
                <td>[%=new Files.Filesize(size).humanize()%]</td>
            </tr>
        </tbody>
    </table>
</div>
</textarea>
<div id="logman-file-tmp" style="display: none;">
</div>
EOD;
        $translator = $this->_translator;

        // Replace translations.
        $template = str_replace(array(
            '[%=view-label%]',
            '[%=download-label%]',
            '[%=name-label%]',
            '[%=size-label%]'), array(
            $translator->translate('View'),
            $translator->translate('Download'),
            $translator->translate('Name'),
            $translator->translate('Size')), $template);

        return $template;
    }
}
