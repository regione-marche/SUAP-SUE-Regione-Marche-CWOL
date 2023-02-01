<?php
//

$ente_selezionato = false;
$ente_configurazione = false;
$enti = $this->_get_enti_plugin($plugin);

$action = $_POST[$options_slug];

if (isset($action['action_new']) && isset($action['plugin_ente']) && !empty($action['plugin_ente'])) {
    if (in_array($options['plugin_ente'], $enti)) {
        add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Ente già esistente.', 'error');
    } else {
        $ente_selezionato = $action['plugin_ente'];
        $ente_configurazione = $this->_get_config_path($plugin);
    }
}

if (isset($action['action_select']) && isset($action['plugin_selezione']) && !empty($action['plugin_selezione'])) {
    $ente_selezionato = $action['plugin_selezione'];
    $ente_configurazione = $this->_get_config_path($plugin, $ente_selezionato);

    if (!$ente_configurazione) {
        $ente_selezionato = false;
        add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Codice ente non valido!', 'error');
    }
}

$is_editing = $ente_selezionato !== false;
$is_existing = in_array($ente_selezionato, $enti);

//
?><div class="wrap">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post">
        <table class="form-table">
            <?php if (!$is_editing) : ?>

                <tr valign="top">
                    <th scope="row"><label for="<?php echo $options_slug; ?>[plugin_ente]">Nuovo ente</label></th>
                    <td><input type="text" id="<?php echo $options_slug; ?>[plugin_ente]" name="<?php echo $options_slug; ?>[plugin_ente]" size="6" /> <input type="submit" class="button" name="<?php echo $options_slug; ?>[action_new]" value="Crea" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="<?php echo $options_slug; ?>[plugin_selezione]">Modifica ente</label></th>
                    <td>
                        <select id="<?php echo $options_slug; ?>[plugin_selezione]" name="<?php echo $options_slug; ?>[plugin_selezione]">
                            <option value="">-- seleziona --</option>
                            <?php foreach ($enti as $ente) : ?>
                                <option value="<?php echo $ente; ?>"<?php echo $ente_selezionato === $ente ? ' selected' : ''; ?>><?php echo $ente; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" class="button" name="<?php echo $options_slug; ?>[action_select]" value="Seleziona" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="<?php echo $options_slug; ?>[plugin_delete]">Eliminazione ente</label></th>
                    <td>
                        <select id="<?php echo $options_slug; ?>[plugin_delete]" name="<?php echo $options_slug; ?>[plugin_delete]">
                            <option value="">-- seleziona --</option>
                            <?php foreach ($enti as $ente) : ?>
                                <option value="<?php echo $ente; ?>"><?php echo $ente; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="submit" class="button" style="background-color: #c3634e; border: 1px solid #893e2c; color: #ffffff;" name="<?php echo $options_slug; ?>[action_delete]" value="Elimina" />
                    </td>
                </tr>

            <?php else : ?>

                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo $options_slug; ?>[plugin_config]">
                            <?php if ($is_existing) : ?>
                                <?php echo basename($ente_configurazione); ?><br />
                                <span style="font-weight: normal;">(ente <?php echo $ente_selezionato; ?>)</span>
                            <?php else : ?>
                                Creazione nuovo ente <?php echo $ente_selezionato; ?><br />
                                <span style="font-weight: normal;"><?php echo 'config.inc.' . $ente_selezionato . '.php'; ?></span>
                            <?php endif; ?>
                        </label>
                    </th>
                    <td>
                        <input type="hidden" name="<?php echo $options_slug; ?>[plugin_config_ente]" value="<?php echo $ente_selezionato; ?>" />
                        <textarea id="<?php echo $options_slug; ?>[plugin_config]" name="<?php echo $options_slug; ?>[plugin_config]" cols="100" rows="10" style="font-family: monospace;"><?php echo file_get_contents($ente_configurazione); ?></textarea>

                        <p class="submit">
                            <a href="" class="button">Indietro</a>
                            <?php submit_button(($is_existing ? 'Salva' : 'Crea Ente'), 'primary', $options_slug . '[submit]', false); ?>
                        </p>

                        <script>
                            CodeMirror.fromTextArea(document.querySelector('[name="<?php echo $options_slug; ?>[plugin_config]"]'), {
                                lineNumbers: true
                            });
                        </script>
                    </td>
                </tr>

            <?php endif; ?>
        </table>
    </form>
</div>