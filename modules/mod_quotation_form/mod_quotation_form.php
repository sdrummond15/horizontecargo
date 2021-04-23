<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_representadas
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
$app = JFactory::getApplication();

$telefone = $params->get('phone');
$enviar = $params->get('button_enviar');
$email_admin = (boolval($params->get('email_admin'))) ? $app->get('mailfrom') : $params->get('email');
$subject = $params->get('subject');
$sucesso = $params->get('success');
$erro = $params->get('failure');

require JModuleHelper::getLayoutPath('mod_quotation_form', $params->get('layout', 'default'));

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(true) . '/modules/mod_quotation_form/assets/css/style_quotation_form.css');
