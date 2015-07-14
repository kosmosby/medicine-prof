<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterTerms
{
	function getTerms()
	{
		$member = oseRegistry::call('member');
		$terms = $member->getInstance('Email')->getTerms();
		$total = count($terms);

		$result = array();
		$result['total'] = $total;

		$terms = str_replace("../", JURI::root(), $terms);
		$result['results'] = $terms;
		$result = oseJson::encode($result);
		oseExit($result);
	}

	function getTerm()
	{
		$id = JRequest::getInt('id',0);

		$terms = oseRegistry::call('member')->getInstance('Email')->getTerm($id);
		$terms = str_replace("../", JURI::root(), $terms);
		$result = empty($terms)?'':$terms;
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
?>