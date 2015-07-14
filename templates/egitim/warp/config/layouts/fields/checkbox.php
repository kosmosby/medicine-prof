<?php
printf('<input %s />', $control->attributes(array_merge($node->attr(), array('type' => 'checkbox', 'name' => $name, 'value' => $value)), array('label', 'description', 'default')));