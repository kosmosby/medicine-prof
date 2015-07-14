<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Exportable Controller Behavior Class.
 *
 * Provides a pluggable mechanism for exporting data from a view. By default, data gets written to
 * a temporary location within the filesystem. It also supports incremental exports, i.e. using multiple
 * requests, making it less prone to timeouts and memory errors.
 */
class ComLogmanControllerBehaviorExportable extends KControllerBehaviorAbstract
{
    /**
     * The export format (used for determining the export view).
     *
     * @var string
     */
    protected $_format;

    /**
     * The maximum amount of records to export per request.
     *
     * @var int
     */
    protected $_limit;

    /**
     * The exportable database behavior.
     *
     * @var mixed
     */
    protected $_behavior;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_format = $config->format;
        $this->_limit  = $config->limit;

        $this->_filename = $config->filename;
        $this->_behavior = $config->behavior;
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'format' => 'csv',
            'limit'  => 50));

        if (!$config->behavior)
        {
            $identifier       = clone $this->getIdentifier();
            $identifier->path = array('database', 'behavior');
            $config->behavior = $identifier;
        }

        parent::_initialize($config);
    }

    protected function _beforeGet(KCommandContext $context)
    {
        if (($this->getRequest()->format == $this->_format) && $this->_limit)
        {
            $table = $this->getModel()->getTable();

            if (is_string($this->_behavior) || $this->_behavior instanceof KServiceIdentifier)
            {
                $this->_behavior = $this->getService((string) $this->_behavior,
                    array(
                        'limit'  => $this->_limit,
                        'offset' => $this->getRequest()->offset,
                        'mixer'  => $table));
            }

            if (!$this->_behavior instanceof ComLogmanDatabaseBehaviorExportable)
            {
                throw new KControllerBehaviorException('Behavior must be an instance of ComLogmanDatabaseBehaviorExportable');
            }

            $table->addBehavior(array($this->_behavior));
        }
    }

    protected function _afterGet(KCommandContext $context)
    {
        if ($this->getRequest()->format == $this->_format)
        {
            $config = new KConfig(array(
                'data' => $this->getView()->output));

            $this->_write($config);

            $model = $this->getModel();
            $items = $model->getList();

            $exported  = count($items);
            $remaining = $model->getTotal() - $exported;

            $output = array(
                'remaining' => $remaining,
                'exported'  => $exported,
                'last'      => $this->_behavior->getLast());

            $context->result = $this->setView($this->getService('com://admin/default.view.json',
                array('output' => json_encode($output))))->display();
        }
    }

    /**
     * Writes the data provided by the view, aka exported data.
     *
     * @param KConfig $config The configuration object.
     *
     * @throws KControllerException If a problem is encountered.
     */
    protected function _write(KConfig $config)
    {
        $config->append(array('target' => $file = $this->_getTemporaryFile()));

        if ($this->_doCleanup())
        {
            if (file_exists($config->target) && !unlink($config->target)) throw new  KControllerException('Unable to delete temporary file during cleanup.');
        }

        $file_obj = new SplFileObject($config->target, 'a');
        $file_obj->fwrite($config->data);
    }

    /**
     * Determines if a cleanup (delete last exported file) must be performed.
     *
     * @return bool True if a cleanup must be performed, false otherwise.
     */
    protected function _doCleanup()
    {
        return (bool) !$this->getRequest()->offset;
    }

    /**
     * Returns a temporary file location.
     *
     * Additionally checks for Joomla tmp folder if the system directory is not writable
     *
     * @param array $config An optional configuration array.
     *
     * @throws KTemplateException
     * @return string Folder path
     */
    protected function _getTemporaryFile($config = array())
    {
        static $file;

        if (!isset($file))
        {
            $config = new KConfig($config);

            $config->append(array(
                'name' => KInflector::pluralize($this->getMixer()
                                                ->getIdentifier()->name) . '.' . $this->_format));

            $path = false;

            $candidates = array(
                ini_get('upload_tmp_dir'),
                JPATH_ROOT . '/tmp'
            );

            if (function_exists('sys_get_temp_dir'))
            {
                array_unshift($candidates, sys_get_temp_dir());
            }

            foreach ($candidates as $folder)
            {
                if ($folder && @is_dir($folder) && is_writable($folder))
                {
                    $path = rtrim($folder, '\\/');
                    break;
                }
            }

            if ($path === false)
            {
                throw new KViewException('Cannot find a writable temporary directory');
            }

            $file = $path . '/' . $config->name;
        }

        return $file;
    }
}