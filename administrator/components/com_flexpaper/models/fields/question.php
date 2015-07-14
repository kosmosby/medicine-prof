<?php
// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * HelloWorld Form Field class for the HelloWorld component
 */
class JFormFieldQuestion extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'question';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions()
	{
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
////		$query->select('a.id, a.name');
////		$query->from('#__lms_tests a');
////        //$query->leftJoin('#__lms_tests b on a.testid=b.id');
////		$db->setQuery((string)$query);
//
//        $query->select('#__lms_questions.id as id,question,#__lms_tests.name as test,testid');
//        $query->from('#__lms_questions');
//        $query->leftJoin('#__lms_tests on testid=#__lms_tests.id');
//        $db->setQuery((string)$query);
//
//
//        $tests = $db->loadObjectList();
//
////        echo "<pre>";
////        print_r($tests); die;
//
//		$options = array();
//		if ($tests)
//		{
//            foreach($tests as $test)
//            {
//                $options[] = JHtml::_('select.option', $test->id, $test->question .
//                    ($test->testid ? ' (' . $test->test . ')' : ''));
//            }
//		}
//
//		$options = array_merge(parent::getOptions(), $options);
//		return $options;
	}
}