<?php

/**
 * @subpackage	Cirrus Green v1.6 HM02J
 * @copyright	Copyright (C) 2010-2013 Hurricane Media - All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$document->addStyleSheet($this->baseurl . '/templates/system/css/system.css');
$document->addStyleSheet($this->baseurl . '/templates/system/css/general.css');
$document->addStyleSheet($this->baseurl . '/templates/' . $this->template .'/css/fontawesome-all.min.css');
$document->addStyleSheet($this->baseurl . '/templates/' . $this->template .'/css/bootstrap.min.css');
$document->addStyleSheet($this->baseurl . '/templates/' . $this->template .'/css/template.css');
$document->addScript($this->baseurl . '/templates/' . $this->template .'/js/sfhover.js');
$document->addScript($this->baseurl . '/templates/' . $this->template .'/js/jquery-1.11.3.js');
$document->addScript($this->baseurl . '/templates/' . $this->template .'/js/bootstrap.min.js');
$document->addScript($this->baseurl . '/templates/' . $this->template .'/js/script_main.js');

$LeftMenuOn = $this->countModules('position-7');
$RightMenuOn = $this->countModules('position-6');
$TopNavOn = $this->countModules('position-13');
$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');
$logopath = $this->baseurl . '/templates/' . $this->template . '/images/logo-demo-green.gif';
$logo = $this->params->get('logo', $logopath);
$logoimage = $this->params->get('logoimage');
$sitetitle = $this->params->get('sitetitle');
$sitedescription = $this->params->get('sitedescription');

$menu = $app->getMenu();
$lang = JFactory::getLanguage();
$home = boolval($menu->getActive() == $menu->getDefault($lang->getTag()));

$w = 'w3';
if ($LeftMenuOn and $RightMenuOn) {
    $w = 'w1';
} elseif ($LeftMenuOn or $RightMenuOn) {
    $w = 'w2';
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $this->language; ?>" lang="<?= $this->language; ?>" dir="<?= $this->direction; ?>">

<head>
    <script type="text/javascript">var home = '<?= $home ?>';</script>
    <jdoc:include type="head" />
    <meta name="viewport" content="width=device-width, user-scalable=no"/>
</head>

<body>

    <div id="wrapper">

        <div id="header_wrap">
            <div id="header">

                <!-- Logo -->
                <div id="logo" class="white">
                    <?php if ($logo && $logoimage == 1) : ?>
                        <a href="<?= $this->baseurl ?>">
                            <img src="<?= htmlspecialchars($logo); ?>" alt="<?= $sitename; ?>" />
                        </a>
                    <?php endif; ?>
                    <?php if (!$logo || $logoimage == 0) : ?>

                        <?php if ($sitetitle) : ?>
                            <a href="<?= $this->baseurl ?>">
                                <?= htmlspecialchars($sitetitle); ?>
                            </a><br />
                        <?php endif; ?>

                        <?php if ($sitedescription) : ?>
                            <div class="sitedescription">
                                <?= htmlspecialchars($sitedescription); ?>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <div class="menutop">
                    <?php if ($this->countModules('position-0')) : ?>
                        <div id="search_wrap">
                            <div id="search">
                                <jdoc:include type="modules" name="position-0" />
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('position-15')) : ?>
                        <div id="right_menu">
                            <jdoc:include type="modules" name="position-15" />
                        </div>
                    <?php endif; ?>
                    <div id="topmenu_wrap">
                        <div id="topmenu">
                            <jdoc:include type="modules" name="position-1" />
                        </div>
                    </div>
                </div>

                <div class="gotomenu">
                    <div id="gotomenu">
                        <i class="fa fa-bars smallmenu" aria-hidden="true"></i>
                    </div>
                </div>

            </div>

            <div class="menuresp">
                <jdoc:include type="modules" name="position-1" />
            </div>

        </div>

        <!-- TopNav -->
        <?php if ($TopNavOn) : ?>
            <div id="topnav_wrap">
                <div id="topnav">
                    <jdoc:include type="modules" name="position-13" style="xhtml" />
                </div>
            </div>
        <?php endif; ?>

        <!-- Breadcrumbs -->
        <?php if ($this->countModules('position-2')) : ?>
            <div id="breadcrumbs">
                <jdoc:include type="modules" name="position-2" />
            </div>
        <?php endif; ?>

        <!-- Content/Menu Wrap -->
        <div id="content-menu_wrap_bg">
            <div id="content-menu_wrap">

                <!-- Left Menu -->
                <?php if ($LeftMenuOn) : ?>
                    <div id="leftmenu">
                        <jdoc:include type="modules" name="position-7" style="xhtml" />
                        <jdoc:include type="modules" name="position-4" style="xhtml" />
                        <jdoc:include type="modules" name="position-5" style="xhtml" />
                    </div>
                <?php endif; ?>

                <!-- Contents -->
                <div id="content-<?= $w ?>">
                    <jdoc:include type="message" />
                    <jdoc:include type="component" />
                </div>

                <!-- Right Menu -->
                <?php if ($RightMenuOn) : ?>
                    <div id="rightmenu">
                        <jdoc:include type="modules" name="position-6" style="xhtml" />
                        <jdoc:include type="modules" name="position-8" style="xhtml" />
                        <jdoc:include type="modules" name="position-3" style="xhtml" />
                    </div>
                <?php endif; ?>

                <?php if ($this->countModules('position-12')) : ?>
                    <div id="content-top">
                        <jdoc:include type="modules" name="position-12" style="xhtml" />
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Footer -->
        <?php if ($this->countModules('position-14')) : ?>
            <div id="footer_wrap">
                <div class="footer">
                    <jdoc:include type="modules" name="position-14" style="xhtml" />
                </div>
            </div>
        <?php endif; ?>

        <div id="box_wrap">
            <div class="overlayer">
                <!-- Banner/Links -->
                <?php if (
                    ($this->countModules('position-9')) ||
                    ($this->countModules('position-10')) ||
                    ($this->countModules('position-11'))
                ) : ?>

                    <div id="box_placeholder">
                        <div id="box1">
                            <jdoc:include type="modules" name="position-9" style="xhtml" />
                        </div>
                        <div id="box2">
                            <jdoc:include type="modules" name="position-10" style="xhtml" />
                        </div>
                        <div id="box3">
                            <jdoc:include type="modules" name="position-11" style="xhtml" />
                        </div>
                    </div>

                <?php endif; ?>

                <div id="push"></div>

                <!-- Page End -->
                <div id="copyright">
                    <div class="copyrightint">
                        Copyright &copy;<?= date('Y'); ?>
                        <strong><?= $sitename; ?></strong> - Todos os direitos reservados
                        <a href="http://www.sdrummond.com.br" title="Sdrummond Tecnologia" class="sd">
                            <img src="images/sd.png" alt="Sdrummond Tecnologia" title="Sdrummond Tecnologia" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>