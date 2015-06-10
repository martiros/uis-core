<?php

function sc_string($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function sc_string_decode($string)
{
    return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
}

function sc_url_param($string)
{
    return rawurlencode($string);
}

function uis_dump()
{
    $args = func_get_args();
    foreach ($args as $argIndex => $arg) {
        echo '<br/><b>PARAM-'.$argIndex.'</b><br/>';
        echo '<pre>';
        print_r($arg);
        echo '</pre>';
    }
    die();
}

function uis_app_name()
{
    return app('uis.app')->getName();
}

// Storage functions

function images_path()
{
    return public_path().'/'.'images';
}

/**********************************************************************************************************************/
/**********************************************************************************************************************/
/**********************************************************************************************************************/

function show_date(DateTime $date, $showTime = true)
{
    return $date->format('H:i d/m/Y');
}

function script($file, $minGroup = 'default', $attributes = ['type' => 'text/javascript'])
{
    app()->make('uis.core.page.scripts')->appendFile($file, $minGroup, $attributes);
}

function sc_attr($attr)
{
    return sc_string($attr);
}

function style($path)
{
    \UIS\Helper\HeadStyle::getInstance()->appendFile($path);
}

function script_generate()
{
    return \UIS\Helper\HeadScript::getInstance()->generate();
}

function style_generate()
{
    return \UIS\Helper\HeadStyle::getInstance()->generate();
}
