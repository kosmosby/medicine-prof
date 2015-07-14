<?php
/*
	Class: WarpMenuMobile
		Mobile menu class
*/
class WarpMenuMobile extends WarpMenu {
	
	/*
		Function: process

		Returns:
			Object
	*/	
	public function process($module, $element) {

		// add mobile class
		$element->first('ul:first')->addClass('menu-mobile');

		return $element;
	}

}