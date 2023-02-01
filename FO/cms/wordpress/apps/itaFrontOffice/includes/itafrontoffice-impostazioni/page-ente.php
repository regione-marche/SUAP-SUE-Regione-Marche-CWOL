<?php
//

$tinymce_settings = array(
    'media_buttons' => false,
    'textarea_rows' => 4,
    'editor_class' => 'width: 300px;'
);

//
?><div class="wrap">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post" enctype="multipart/form-data">

        <table class="form-table">
            <?php foreach ($this->ente_options as $key => $option) : ?>
                <tr valign="top">
                    <th scope="row"><label for="<?php printf('%s[%s]', $options_slug, $key); ?>"><?php echo $option['label']; ?></label></th>
                    <td>
                        <input type="text" name="<?php printf('%s[%s]', $options_slug, $key); ?>" id="<?php printf('%s[%s]', $options_slug, $key); ?>" size="50" value="<?php echo htmlentities($options[$key], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" />
                        <p class="description" id="tagline-description">Shortcode [<?php printf("%s_ente_%s", $this->shortcode_slug, $key); ?>].</p>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr valign="top">
                <th scope="row"></th>
                <td>
                    <?php submit_button(); ?>
                </td>
            </tr>
        </table>

    </form>
</div>