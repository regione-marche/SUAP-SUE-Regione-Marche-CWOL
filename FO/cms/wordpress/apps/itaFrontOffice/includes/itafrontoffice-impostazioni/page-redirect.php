<?php
if (count($options) === 0) {
    $options = get_option('theme_my_login_redirection');
}
?><div class="wrap">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post" enctype="multipart/form-data">
        <script>
            function redirectShowUrl(el) {
                el.nextElementSibling.style.display = 'none';
                el.nextElementSibling.nextElementSibling.style.display = 'none';

                if (el.value === 'custom') {
                    el.nextElementSibling.style.display = 'inline-block';
                } else if (el.value === 'copy') {
                    el.nextElementSibling.nextElementSibling.style.display = 'inline-block';
                }
            }
        </script>

        <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
            <h2 style="margin: 10px 0 0;"><?php echo $role_info['name']; ?></h2>

            <table class="form-table">
                <?php foreach (array('login', 'logout') as $type) : ?>
                    <?php
                    $opt_type = sprintf('%s[%s][%s_type]', $options_slug, $role_name, $type);
                    $opt_url = sprintf('%s[%s][%s_url]', $options_slug, $role_name, $type);
                    $opt_copy = sprintf('%s[%s][%s_copy]', $options_slug, $role_name, $type);
                    ?>
                    <tr valign="top">
                        <th scope="row" style="padding: 5px 0;">
                            <label for="<?php echo $opt_type; ?>">
                                <?php echo $type == 'login' ? 'Accesso' : 'Uscita'; ?>
                            </label>
                        </th>
                        <td style="padding: 5px 0;">
                            <select onchange="redirectShowUrl(this)" name="<?php echo $opt_type; ?>" id="<?php echo $opt_type; ?>">
                                <?php foreach (array('default' => 'Predefinito', 'referer' => 'Referer', 'custom' => 'Personalizzato', 'copy' => 'Uguale a...') as $k => $v) : ?>
                                    <option value="<?php echo $k; ?>"<?php echo $options[$role_name][$type . '_type'] == $k ? ' selected' : ''; ?>><?php echo $v; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input size="100" value="<?php echo $options[$role_name][$type . '_url']; ?>" type="text" style="vertical-align: middle; display: inline-block;" name="<?php echo $opt_url; ?>" value="">
                            <select name="<?php echo $opt_copy; ?>" id="<?php echo $opt_copy; ?>">
                                <?php foreach (get_editable_roles() as $cp_name => $cp_info): if ($cp_name == $role_name) continue; ?>
                                    <option value="<?php echo $cp_name; ?>"<?php echo $options[$role_name][$type . '_copy'] == $cp_name ? ' selected' : ''; ?>><?php echo $cp_info['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <script>redirectShowUrl(document.getElementById('<?php echo $opt_type; ?>'));</script>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row" style="padding: 5px 0;"></th>
                <td style="padding: 5px 0;">
                    <?php submit_button(); ?>
                </td>
            </tr>
        </table>
    </form>
</div>