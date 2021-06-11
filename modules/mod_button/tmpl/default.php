<?php
$link = ($params->get('type_link') == 'menu_item') ? JRoute::_('index.php?Itemid=' . $params->get('menu')) : $params->get('external_url');
$class = (!empty($params->get('class_button'))) ? 'btn-' . $params->get('class_button') : '';
$title = (!empty($params->get('title'))) ? '<span>' . $params->get('title') . '</span>' : '';
$image = '';
if ($params->get('type_image') == 'icon' && !empty(trim($params->get('icon')))) {
    $image = '<i class="' . $params->get('icon') . '"></i>';
} elseif ($params->get('type_image') == 'image' && !empty(trim($params->get('image')))) {
    $image = '<img src="' . $params->get('image') . '" alt="' . $title . '" />';
}

?>
<div id="modbutton" class="<?= $params->get('header_class') ?>">
    <a href="<?= $link ?>" class="btn <?= $class ?>">
        <?= $image ?> <?= $title ?>
    </a>
</div>