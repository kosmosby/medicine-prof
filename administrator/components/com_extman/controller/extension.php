<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanControllerExtension extends ComKoowaControllerModel
{
	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

        $this->addCommandCallback('before.render', '_setState');
        $this->addCommandCallback('before.add'   , '_setEntity');
	}

	protected function _initialize(KObjectConfig $config)
	{
		$config->append(array(
			'behaviors' => array('trackable')
		));

		parent::_initialize($config);
	}

	protected function _setState()
	{
		if ($this->isDispatched())
        {
			$this->getRequest()->query->top_level = true;
            $this->getModel()->getState()->top_level = true;
		}
	}

	/**
	 * Instantiate the package specific entity class for installations
	 *
	 * @param KControllerContextInterface $context
	 */
	protected function _setEntity(KControllerContextInterface $context)
	{
		$data = $context->request->data;

        if (is_string($data->manifest) && strlen($data->manifest) < 255 && is_file($data->manifest)) {
            $data->manifest = simplexml_load_file($data->manifest);
        }

        if (is_object($data->manifest) && (string)$data->manifest->identifier)
		{
			$identifier = new KObjectIdentifier((string)$data->manifest->identifier);

        	if ($identifier->type === 'com') {
        		$this->getModel()->package($identifier->package);
        	}
		}
	}
}