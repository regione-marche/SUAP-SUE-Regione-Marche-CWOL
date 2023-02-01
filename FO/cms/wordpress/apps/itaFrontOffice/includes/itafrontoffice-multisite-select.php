<?php

if (is_admin() && function_exists('is_multisite') && is_multisite()) {
//    add_action('admin_head', 'itafrontoffice_multisite_select_hide_classic');
    add_action('admin_bar_menu', 'itafrontoffice_multisite_select', 999);
}

function itafrontoffice_multisite_select_hide_classic() {
    $blogs = function_exists('get_sites') ? get_sites() : wp_get_sites();

    if (count($blogs) <= 1)
        return false;

    echo "<style>#wp-admin-bar-my-sites-list { display: none !important; }</style>";
}

function itafrontoffice_multisite_select($wp_admin_bar) {
    $blogs = function_exists('get_sites') ? get_sites() : wp_get_sites();

    if (!count($blogs)) {
        return false;
    }

    $current_blogurl = get_bloginfo('url');
    $html = '<form style="margin-top: -2px;" action="" method="post">';
    $html .= '<select onchange="location.href=this.value;">';
    $html .= '<option value="' . network_admin_url() . '" style="padding: 2px 5px;">Gestione network</option>';
    $html .= '<optgroup label="Elenco dei siti" style="padding: 5px; background-color: #f2f2f2; font-style: normal; font-size: .85em; margin: 4px 0;"></optgroup>';
    
    foreach ($blogs as $blog) {
        $html .= '<option ' . (!is_network_admin() ? selected($blog->siteurl, $current_blogurl, false) : '' ) . ' value="' . $blog->siteurl . '/wp-admin/" style="padding: 2px 5px;">' . $blog->blogname . '</option>';
    }
    
    $html .= '</select>';
    $html .= '</form>';

    $args = array(
        'id' => 'toolbar_multisite_select',
        'title' => $html
    );

    $wp_admin_bar->add_node($args);
}
