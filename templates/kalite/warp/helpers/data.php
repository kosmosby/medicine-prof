<?php
/*
	Class: DataWarpHelper
		Data helper class.
*/
class DataWarpHelper extends WarpHelper {

	/*
		Function: __construct
			Class Constructor.
	*/
	public function __construct() {
		parent::__construct();

		// load class
		require_once($this['path']->path('classes:data.php'));
	}

	/*
		Function: create
			Retrieve a data object

		Parameters:
			$data - Data
			$format - Data format

		Returns:
			Mixed
	*/
	public function create($data = array(), $format = 'json') {
		
		// load data class
		$class = $format.'WarpData';

		return new $class($data);
	}
	
}