<?php

class Itafrontoffice_Post_Limited_Access {

    public function __construct() {
        add_action('load-post.php', array($this, 'caricaMetabox'));
        add_action('load-post-new.php', array($this, 'caricaMetabox'));

        add_filter('the_content', array($this, 'limitaAccesso'));
    }

    public function caricaMetabox() {
        add_action('add_meta_boxes', array($this, 'aggiungiMetabox'));

        add_action('save_post', array($this, 'salvaMetabox'), 10, 2);
    }

    public function aggiungiMetabox() {
        add_meta_box('itafrontoffice-post-limited-access', 'Limita accesso', array($this, 'visualizzaMetabox'), 'page', 'side', 'default');
    }

    public function visualizzaMetabox($post) {
        global $wp_roles;
        $roles = $wp_roles->get_names();

        $selected = get_post_meta($post->ID, 'itafrontoffice-limit-access', true) ?: array();

        wp_nonce_field(basename(__FILE__), 'itafrontoffice-post-limited-access-nonce');
        ?>
        <p>
        <p class="post-attributes-label-wrapper">
            <label class="post-attributes-label" for="itafrontoffice-limit-access">Limita l'accesso ai seguenti ruoli</label>
        </p>
        <?php foreach ($roles as $rname => $rinfo) : ?>
            <label for="itafrontoffice-limit-access-<?php echo $rname; ?>"><input name="itafrontoffice-limit-access[]" type="checkbox" id="itafrontoffice-limit-access-<?php echo $rname; ?>" value="<?php echo $rname; ?>" <?php echo in_array($rname, $selected) ? ' checked' : ''; ?>> <?php echo $rinfo; ?></label><br>
        <?php endforeach; ?>
        <p>Se non &egrave; selezionato nessun ruolo la pagina sar&agrave; visualizzata normalmente.</p>
        </p>
        <?php
    }

    function salvaMetabox($post_id, $post) {
        if (!isset($_POST['itafrontoffice-post-limited-access-nonce']) || !wp_verify_nonce($_POST['itafrontoffice-post-limited-access-nonce'], basename(__FILE__))) {
            return $post_id;
        }

        $post_type = get_post_type_object($post->post_type);

        if (!current_user_can($post_type->cap->edit_post, $post_id)) {
            return $post_id;
        }

        $new_meta_value = isset($_POST['itafrontoffice-limit-access']) && count($_POST['itafrontoffice-limit-access']) ? $_POST['itafrontoffice-limit-access'] : false;

        $meta_key = 'itafrontoffice-limit-access';

        $meta_value = get_post_meta($post_id, $meta_key, true);

        if ($new_meta_value && '' == $meta_value) {
            add_post_meta($post_id, $meta_key, $new_meta_value, true);
        } elseif ($new_meta_value && $new_meta_value !== $meta_value) {
            update_post_meta($post_id, $meta_key, $new_meta_value);
        } elseif (!$new_meta_value && $meta_value) {
            delete_post_meta($post_id, $meta_key, $meta_value);
        }
    }

    public function limitaAccesso($content) {
        global $post;

        if ($post->post_type !== 'page') {
            return $content;
        }

        $meta_value = get_post_meta($post->ID, 'itafrontoffice-limit-access', true);

        if (!$meta_value || !is_array($meta_value) || !count($meta_value)) {
            return $content;
        }

        $ruoloUtente = frontOfficeApp::$cmsHost->getRuoloUtente();

        if (!in_array($ruoloUtente, $meta_value)) {
            $html = new html();

            if (function_exists('itafrontoffice_get_option') && ($denied_message = itafrontoffice_get_option('denied_access_message'))) {
                $content = $html->getAlert(wpautop($denied_message), '', 'warning');
            } else {
                $content = $html->getAlert('Non si dispone delle autorizzazioni necessarie per accedere alla pagina.', 'Attenzione', 'warning');
            }
        }

        return $content;
    }

}

new Itafrontoffice_Post_Limited_Access;
