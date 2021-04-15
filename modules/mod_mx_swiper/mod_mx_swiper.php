<?php
/**
* @title			Mx Swiper
* @version   		4.0.0
* @copyright   		Copyright (C) 2020 mixwebtemplates.com, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   	http://www.mixwebtemplates.com/
* @developers   	mixwebtemplates.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}


// Add stylesheets
$document = JFactory::getDocument();


require JModuleHelper::getLayoutPath('mod_mx_swiper', $params->get('layout', 'default'));