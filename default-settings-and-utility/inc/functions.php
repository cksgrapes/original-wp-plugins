<?php


/**
 * add_image_sizeをまとめて設定
 */
function register_image_sizes ($sizes)
{
    $count = count($sizes);

    function register_item ($item)
    {
        if (!$item['crop']) {
            add_image_size($item['name'], $item['width'], $item['height']);
        } else {
            add_image_size($item['name'], $item['width'], $item['height'], true);
        }
    }

    if ($count === 1) {
        register_item($sizes[0]);
    } else {
        foreach ($sizes as $size) {
            register_item($size);
        }
    }
}

/**
 * wp_enqueue系をまとめて設定
 */
function org_scripts ($scripts)
{

    function type_css ($script)
    {
        return $script['type'] === 'css';
    }

    function type_js ($script)
    {
        return $script['type'] === 'js';
    }

    $arr_css = array_filter($scripts, 'type_css');
    $arr_js = array_filter($scripts, 'type_js');

    foreach ($arr_css as $item) {
        wp_enqueue_style($item['handle'], $item['src']);
    }

    foreach ($arr_js as $item) {
        wp_enqueue_script($item['handle'], $item['src'], [], null, true);
    }

    wp_deregister_script('jquery');
}