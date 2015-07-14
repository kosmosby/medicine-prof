<?php
/*
	Class: WarpMenu
		Menu base class
*/
class WarpMenu {

	/*
		Function: process
			Abstract function. New implementation in child classes.

		Returns:
			Object
	*/
	public function process($module, $element) {
		return $element;
	}

}