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
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(true) . '/modules/mod_contact_social_footer/assets/css/style.css');
$document->addScript(JURI::base(true) . '/modules/mod_contact_social_footer/assets/js/script.js');

$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');
$logo = $params->get('logo');
$phone = $params->get('phone');
$phoneNumber = modContact_Social_FooterHelper::removeCharacters($phone);
$email = $params->get('email');
$facebook = $params->get('facebook');
$youtube = $params->get('youtube');
$instagram = $params->get('instagram');
$linkedin = $params->get('linkedin');
$twitter = $params->get('twitter');
$whatsapp = $params->get('whatsapp');
$whatsappNumber = modContact_Social_FooterHelper::removeCharacters($whatsapp);
$whatsapp_msg = $params->get('whatsapp_msg');

require JModuleHelper::getLayoutPath('mod_contact_social_footer', $params->get('layout', 'default'));