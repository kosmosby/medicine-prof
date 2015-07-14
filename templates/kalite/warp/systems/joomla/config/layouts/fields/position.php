<?php
printf('<select %s>', $control->attributes(compact('name')));

foreach ($this['dom']->create($this['path']->path('template:templateDetails.xml'), 'xml')->find('positions > position') as $position) {

	// set attributes
	$attributes = array('value' => $position->text());

	// is checked ?
	if ($position->text() == $value) {
		$attributes = array_merge($attributes, array('selected' => 'selected'));
	}

	printf('<option %s>%s</option>', $control->attributes($attributes), $position->text());
}

printf('</select>');