<?php

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Orcamentos');
$controller->execute(JRequest::getVar('task', 'click'));
$controller->redirect();
