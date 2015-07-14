<?php 
defined('_JEXEC') or die;

// logged in ?
echo $this->loadTemplate($this->user->get('guest') ? 'login' : 'logout');