<?php
// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * HelloWorld Form Field class for the HelloWorld component
 */
class JFormFieldflexpaper extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'flexpaper';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.id as id, a.name as name, b.title as category, b.id as catid');
		$query->from('#__flexpaper a');
        $query->leftJoin('#__categories b on a.catid=b.id');
		$db->setQuery((string)$query);
        $messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
            foreach($messages as $message)
            {
                $options[] = JHtml::_('select.option', $message->id, $message->name .
                    ($message->catid ? ' (' . $message->category . ')' : ''));
			}
		}

		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}