<?php
//
global $wpdb, $wp_roles;
$table_name = $wpdb->base_prefix . 'ruolo_campo';
$roles = $wp_roles->get_names();
$cimy_fields = get_cimyFields();
$alternate = false;

global $wpdb;

function itafrontoffice_ruolocampi_register_role($table_name, $role_name, $fields_data_serialized = false) {
    global $wpdb;

    if ($fields_data_serialized === false) {
        $fields_data_serialized = serialize(array());
    }

    $wpdb->query($wpdb->prepare("INSERT INTO " . $table_name . " ( ruolo, campi ) VALUES ( %s, %s )", $role_name, $fields_data_serialized));
}

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "
        CREATE TABLE " . $table_name . " (
            ID int(11) NOT NULL AUTO_INCREMENT,
            ruolo varchar(20) NOT NULL,
            campi text NOT NULL,
            UNIQUE KEY ID (ID)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $fields_data = array();

    foreach ($cimy_fields as $key => $value) {
        $fields_data[$value['NAME']] = 0;
    }

    $fields_data_serialized = serialize($fields_data);

    foreach ($roles as $role_name => $role_info) {
        itafrontoffice_ruolocampi_register_role($table_name, $role_name, $fields_data_serialized);
    }
}
//
?><div class="wrap page-ruolocampi">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post">

        <table class="widefat" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 120px;">Ruolo</th>
                    <?php foreach ($cimy_fields as $key => $value) : ?>
                        <th style="text-align: center;"><?php echo $value['LABEL']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
                    <?php
                    $ruolo_campo_rec = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ruolo = %s", $role_name), 'ARRAY_A');

                    if (!$ruolo_campo_rec) {
                        itafrontoffice_ruolocampi_register_role($table_name, $role_name);
                        $ruolo_campo_rec = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ruolo = %s", $role_name), 'ARRAY_A');
                    }

                    $ruolo_campo_data = unserialize($ruolo_campo_rec['campi']);
                    ?>
                    <tr<?php echo $alternate ? ' class="alternate"' : ''; ?>>
                        <td><?php echo $role_info['name']; ?></td>
                        <?php foreach ($cimy_fields as $key => $value) : ?>
                            <td style="text-align: center;">
                                <input
                                    type="hidden"
                                    name="<?php printf('%s[%s.%s]', $options_slug, $role_name, $value['NAME']); ?>"
                                    value="0"
                                    />

                                <input
                                    type="checkbox"
                                    name="<?php printf('%s[%s.%s]', $options_slug, $role_name, $value['NAME']); ?>"
                                    value="1"
                                    <?php echo isset($ruolo_campo_data[$value['NAME']]) && ((int) $ruolo_campo_data[$value['NAME']]) === 1 ? 'checked' : ''; ?>
                                    />
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php $alternate = !$alternate; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(); ?>

    </form>
</div>