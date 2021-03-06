<?php

function acym_route($url, $xhtml = true, $ssl = null)
{
    return acym_baseURI().$url;
}

function acym_addPageParam($url, $ajax = false, $front = false)
{
    preg_match('#^([a-z]+)(?:[^a-z]|$)#Uis', $url, $ctrl);

    if ($front) {
        if ($ajax) {
            $link = 'admin-ajax.php?page='.ACYM_COMPONENT.'_front&ctrl='.$url.'&action='.ACYM_COMPONENT.'_frontrouter&'.acym_noTemplate();
        } else {
            $link = 'admin.php?page='.ACYM_COMPONENT.'_front&ctrl='.$url;
        }
        $link = 'wp-admin/'.$link;
    } else {
        if ($ajax) {
            $link = 'admin-ajax.php?page='.ACYM_COMPONENT.'_'.$ctrl[1].'&ctrl='.$url.'&action='.ACYM_COMPONENT.'_router&'.acym_noTemplate();
        } else {
            $link = 'admin.php?page='.ACYM_COMPONENT.'_'.$ctrl[1].'&ctrl='.$url;
        }
    }

    return $link;
}

function acym_baseURI($pathonly = false)
{
    if (acym_isAdmin()) {
        return acym_rootURI().'wp-admin/';
    }

    return acym_rootURI();
}

function acym_rootURI($pathonly = false, $path = 'siteurl')
{
    return get_option($path).'/';
}

function acym_completeLink($link, $popup = false, $redirect = false, $forceNoPopup = false)
{
    if (($popup || acym_isNoTemplate()) && $forceNoPopup == false) {
        $link .= '&'.acym_noTemplate();
    }

    $link = acym_addPageParam($link);

    return acym_route($link);
}

function acym_prepareAjaxURL($url)
{
    return htmlspecialchars_decode(acym_route(acym_addPageParam($url, true)));
}

function acym_frontendLink($link, $complete = true)
{
    return acym_rootURI().acym_addPageParam($link, true, true);
}

function acym_getMenu()
{
    return get_post();
}
