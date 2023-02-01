<?php
//

$wp_blogs = function_exists('get_sites') ? get_sites() : wp_get_sites();

$cimy_table_exists = false;
$cimy_table_popolated = false;

if (file_exists(WPMU_PLUGIN_DIR . '/cimy-user-extra-fields')) {
    global $wpdb;
    $table_name_fields = $wpdb->prefix . 'cimy_uef_fields';
    $table_name_wp_fields = $wpdb->prefix . 'cimy_uef_wp_fields';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_fields'") == $table_name_fields) {
        $cimy_table_exists = true;

        if (count($wpdb->get_results("SELECT ID FROM $table_name_fields"))) {
            $cimy_table_popolated = true;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_wp_fields'") == $table_name_wp_fields) {
        $cimy_table_exists = true;

        if (count($wpdb->get_results("SELECT ID FROM $table_name_wp_fields"))) {
            $cimy_table_popolated = true;
        }
    }
}

$is_logging_active = false;
$config_log_file = array();

$roles_ids = array();
$blocks_ids = array();

$config_file = file_get_contents(ITA_FRONTOFFICE_PLUGIN . '/config.inc.php');

if (defined('ITA_ERROR_HANDLER_FILE')) {
    if (file_exists(ITA_ERROR_HANDLER_FILE)) {
        $is_logging_active = true;

        $config_log_file = trim(file_get_contents(ITA_ERROR_HANDLER_FILE));
        $config_log_file = explode("\n", $config_log_file);
        $config_log_file = array_slice($config_log_file, -100, 100, true);
        $config_log_file = array_reverse($config_log_file);
    }
}

$ruoli_bloccati = get_site_option('itafrontoffice_impostazioni_blocco_signup', array());

//
?><div class="wrap">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post">

        <?php if ($is_logging_active) : ?>
            <?php add_thickbox(); ?>

            <div id="itafrontoffice-logs" style="display:none;">
                <div style="margin: 0 -15px;">
                    <?php
                    $background = '';
                    foreach ($config_log_file as $i => $line) {
                        $background = $background === '' ? 'background-color: wheat;' : '';
                        printf('<p style="padding: 0; margin: 0; font-family: monospace; padding: 3px 6px; %s">%s</p>', $background, htmlentities($line));
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="<?php echo $options_slug; ?>[config]">config.inc.php</label></th>
                <td>
                    <textarea name="<?php echo $options_slug; ?>[config]" cols="100" rows="10" style="font-family: monospace;"><?php echo $config_file; ?></textarea>
                    <p class="description" id="tagline-description" style="margin-top: 12px;">
                        La configurazione &egrave; valida per tutti i siti.

                        <?php if ($is_logging_active) : ?>
                            <a name="Visualizzazione logs" title="<?php echo $config_match[1]; ?>" href="#TB_inline?x=y&width=1280&height=600&inlineId=itafrontoffice-logs" class="thickbox button" style="vertical-align: initial; float: right;">Visualizza i logs</a>
                        <?php endif; ?>
                    </p>
                    <script>
                        CodeMirror.fromTextArea(document.querySelector('[name="<?php echo $options_slug; ?>[config]"]'), {
                            lineNumbers: true
                        });
                    </script>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="<?php echo $options_slug; ?>[repository]">URL repository Italsoft</label></th>
                <td>
                    <input name="<?php echo $options_slug; ?>[repository]" id="<?php echo $options_slug; ?>[repository]" type="text" size="60" value="<?php echo $options['repository']; ?>" />

                    <div style="margin: 2em 0 1.5em;">
                        <label>
                            <input name="<?php echo $options_slug; ?>[remove_pec]" id="<?php echo $options_slug; ?>[remove_pec]" type="checkbox" value="1"<?php echo $options['remove_pec'] == 1 ? ' checked' : ''; ?> />
                            <span>Ripristina diciture "Indirizzo email" (invece di "Indirizzo PEC")</span>
                        </label>
                    </div>

                    <?php submit_button(); ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label>Gestione dei ruoli Italsoft</label></th>
                <td>
                    <div style="display: inline-block; vertical-align: top;">
                        <?php foreach ($this->italsoft_roles as $slug => $name) : ?>
                            <?php $roles_ids[] = 'roles[' . $slug . ']'; ?>
                            <label><input type="checkbox" id="roles[<?php echo $slug; ?>]" name="roles[<?php echo $slug; ?>]"<?php echo wp_roles()->is_role($slug) ? ' checked' : '' ?>> <?php echo $name; ?></label><br />
                        <?php endforeach; ?>
                    </div>

                    <div style="display: inline-block; vertical-align: top; padding-left: 10px;">
                        <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
                            <?php if (!in_array($role_name, array_keys($this->italsoft_roles)) && $role_name != 'administrator') : $flag_roles = true; ?>
                                <?php $roles_ids[] = 'roles[' . $role_name . ']'; ?>
                                <label><input type="checkbox" id="roles[<?php echo $role_name; ?>]" name="roles[<?php echo $role_name; ?>]" checked> <?php echo $role_info['name']; ?></label><br />
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (isset($flag_roles)): ?>
                            <br><i>Una volta rimossi i ruoli di Wordpress non sono recuperabili!<br>In caso di necessit&agrave; vanno ricreati manualmente.</i>
                        <?php endif; ?>
                    </div>

                    <br><br>

                    <?php $this->ajax_button('Aggiorna ruoli', 'crea_ruoli', $page, array('form' => $roles_ids)); ?>
                    <?php $this->ajax_button('Allinea i ruoli in tutti i siti', 'allinea_ruoli', $page); ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label>Blocca registrazione manuale</label></th>
                <td>
                    <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
                        <?php if ($role_name != 'administrator') : ?>
                            <?php
                            $block_id = 'blocca_signup[' . $role_name . ']';
                            $blocks_ids[] = $block_id;
                            ?>
                            <label><input type="checkbox" id="<?php echo $block_id; ?>" name="<?php echo $block_id; ?>"<?php echo in_array($role_name, $ruoli_bloccati) ? ' checked' : ''; ?>> <?php echo $role_info['name']; ?></label><br />
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <br>

                    <?php $this->ajax_button('Aggiorna blocco', 'blocca_signup', $page, array('form' => $blocks_ids)); ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label>Allinea utenti</label></th>
                <td>
                    <?php $this->ajax_button('Allinea utenti nel sito...', 'allinea_utenti_sito', $page, array('response-id' => 'allinea-sito-response', 'form' => array('allinea-sito-select'))); ?>

                    <select style="vertical-align: initial;" id="allinea-sito-select">
                        <option value="">-- seleziona --</option>
                        <?php foreach ($wp_blogs as $blog) : ?>
                            <option value="<?php echo $blog->blog_id; ?>"><?php echo $blog->blogname; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <p id="allinea-sito-response" class="itafrontoffice-ajax-response"></p>

                    <br /><br />

                    <?php $this->ajax_button('Allinea utenti in tutti i siti', 'allinea_utenti', $page); ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label>Cambia tema</label></th>
                <td>
                    Cambia il tema in tutti i sottositi con il seguente:

                    <select style="vertical-align: initial;" id="tema-select">
                        <option value="">-- seleziona --</option>
                        <?php foreach (wp_get_themes() as $theme) : $themes[] = $theme; ?>
                            <option value="<?php echo $theme->get_stylesheet(); ?>"><?php echo $theme->get_stylesheet(); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php $this->ajax_button('Applica', 'cambia_tema', $page, array('response-id' => 'cambia-tema-response', 'form' => array('tema-select'))); ?>

                    <p id="cambia-tema-response" class="itafrontoffice-ajax-response"></p>
                </td>
            </tr>

            <?php if (file_exists(WPMU_PLUGIN_DIR . '/cimy-user-extra-fields')) : ?>
                <tr valign="top">
                    <th scope="row"><label>Dati per Cimy</label></th>
                    <td>
                        <?php if (!$cimy_table_exists) : ?>
                            Tabelle mancanti! Crearle da "Impostazioni > Cimy User Extra Fields".
                        <?php else : ?>
                            <?php if ($cimy_table_popolated) : ?>
                                Dati gi&agrave; presenti. Per reimportare nuovamente i dati svuotare le tabelle
                                '<?php echo $table_name_fields; ?>' e '<?php echo $table_name_wp_fields; ?>'.
                            <?php else : ?>
                                <?php $this->ajax_button('Importa dati', 'cimy_data_import', $page); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>

            <tr valign="top">
                <th scope="row"><label>Varie</label></th>
                <td>
                    <?php $this->ajax_button('Lancia operazioni post-update', 'post_update_script', $page); ?>
                </td>
            </tr>
        </table>
    </form>
</div>