<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
JPluginHelper::importPlugin('content.joomplu');

class OrcamentosViewOrcamentos extends JViewLegacy {

    function display($tpl = null) {
        
        $doc = JFactory::getDocument();
        $doc->addStyleSheet('components/com_orcamentos/css/styleorcamentos.css');
        parent::display($tpl);
    }
}
