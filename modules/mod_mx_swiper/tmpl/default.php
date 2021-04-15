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

$doc = JFactory::getDocument();

defined('_JEXEC') or die('Restricted access');
$cacheFolder = JURI::base(true) . '/cache/';
$modID = $module->id;
$modPath = JURI::base(true) . '/modules/mod_mx_swiper/';
$document = JFactory::getDocument();
$jqueryload = $params->get('jqueryload');
$customone = $params->get('customone');
$hgstyle = $params->get('hgstyle');
$ist_style = $params->get('ist_style');
$bgm_color = $params->get('bgm_color');
$ol_height = $params->get('ol_height');
$sliderid = $params->get('sliderid');
$get_style = $params->get('get_style');
$get_layout = $params->get('get_layout');
$ga_items = $params->get('ga_items');
$darklayer = $params->get('darklayer');
$categoryFilter = $params->get('categoryFilter');
$image = $params->get('image');
$author = $params->get('author');

$document->addStyleSheet($modPath . 'assets/css/style.css');
$document->addScript($modPath . 'assets/js/swiper.js');
$document->addStyleDeclaration('.slider{ height:' . $params->get('styleh') . ' !important; }');
if ($darklayer) $document->addStyleDeclaration('.slider .swiper-container .swiper-slide:after{content: ""; width: 100%; height: 100%; position: absolute; left: 0; bottom: 0; z-index: 3; background-color: rgba(0,0,0,0.5)}');

$modpath = JURI::root(true) . '/modules/' . $module->module;
?>


<div id="slides" class="scrollme">
    <section class="slider">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($ga_items as $item) : ?>
                    <div class="swiper-slide bg-image swiper-slide swiper-slide-next" data-background="<?php echo JURI::root(); ?><?php echo $item->ol_image; ?>" data-stellar-background-ratio="0.5" style="width: 1920px; background-image: url(<?php echo JURI::root(); ?><?php echo $item->ol_image; ?>); background-position: center center;">
                        <div class="inner <?= ($item->position == 'R' )? 'box-text-right' : '' ?>">
                            <?php if (!empty($item->ol_title)) : ?>
                                <h2 data-swiper-parallax="-400" style="transition-duration: 0ms; transform: translate3d(-400px, 0px, 0px);">
                                    <?php echo $item->ol_title; ?>
                                </h2>
                                <div data-swiper-parallax="-400" style="transition-duration: 0ms; transform: translate3d(-400px, 0px, 0px);" class="line-title"></div>
                            <?php endif; ?>
                            <p data-swiper-parallax="-400" style="transition-duration: 0ms; transform: translate3d(-400px, 0px, 0px);"><?php echo $item->ol_text; ?></p>
                            <?php if (!empty(trim($item->ol_target_url))) : ?><a href="<?= $item->ol_target_url ?>" class="btn btn-info"><?= (trim($item->ol_info)) ? $item->ol_info : 'Saiba mais...' ?></a><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($params->get('pagination')) : ?><div class="swiper-pagination"></div><?php endif; ?>
            <?php if ($params->get('navigation')) : ?>
                <div class="swiper-button-prev"><span>PREV</span><img src="<?php echo JURI::root(); ?>modules/mod_mx_swiper/assets/images/arrow-left.svg" alt="Image"></div>
                <span class="swiper-button-line"></span>
                <div class="swiper-button-next"><span>NEXT</span><img src="<?php echo JURI::root(); ?>modules/mod_mx_swiper/assets/images/arrow-right.svg" alt="Image"></div>
            <?php endif; ?>
        </div>
    </section>
</div>


<script>
    (function($) {
        $(document).ready(function() {
            var swiper = new Swiper('.swiper-container', {
                speed: 1000,
                parallax: true,
                mousewheel: false,
                effect: '<?= $params->get('sl_effect') ?>',
                keyboard: {
                    enabled: <?php echo $params->get('sl_keyboard'); ?>,
                },
                loop: true,
                autoplay: {
                    delay: <?= $params->get('delay') ?>,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    type: 'bullets',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    }(jQuery));
</script>