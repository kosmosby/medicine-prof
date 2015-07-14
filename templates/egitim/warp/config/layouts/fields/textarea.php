<?php
printf('<textarea %s>%s</textarea>', $control->attributes(array_merge($node->attr(), compact('name')), array('label', 'description', 'default')), htmlspecialchars($value));