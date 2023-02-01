<?php

class Itafrontoffice_Custom_Meta_Widget {

    public function __construct() {
        add_action('widgets_init', array($this, 'replace_meta_widget'));
    }

    public function replace_meta_widget() {
        wp_unregister_sidebar_widget('meta');
        $widget_ops = array('classname' => 'widget_meta', 'description' => __("Log in/out, admin, feed and WordPress links"));
        wp_register_sidebar_widget('meta', __('Meta'), array($this, 'modified_meta_widget'), $widget_ops);
    }

    public function modified_meta_widget($args) {
        global $current_user, $user_login, $user_ID;

        extract($args);

        $title = "Utente";

        echo $before_widget;
        echo $before_title . $title . $after_title;

        get_currentuserinfo();
        echo is_user_logged_in() ? "Benvenuto " . $current_user->user_login . "," : "Per accedere ad alcuni servizi &egrave; necessario registrarsi.";

        echo "<ul style=\"margin-top: 10px;\">";

        if ('' != $user_ID) {
            echo "<li><a href=\"" . get_site_url() . "/wp-admin/profile.php\">Profilo utente</a></li>";
        }
        ?><li><?php wp_loginout() ?></li><?php
        wp_meta();

        echo "</ul>";

        echo $after_widget;
    }

}

new Itafrontoffice_Custom_Meta_Widget();
