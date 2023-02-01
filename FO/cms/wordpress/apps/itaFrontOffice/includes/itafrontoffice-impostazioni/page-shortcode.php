<?php
//

$results = get_posts(
    array(
        'numberposts' => -1,
        'post_status' => null,
        'post_type' => 'any'
    )
);

$items = array();
$shortcode_count = 0;

if ($results) {
    $plugin_shortcode = current(explode('_', $plugin));

    foreach ($results as $post) {
        $item = array(
            'title' => $post->post_title,
            'type' => $post->post_type,
            'url' => get_edit_post_link($post->ID),
            'shortcode' => array()
        );

        $matches = array();
        if (preg_match_all('/' . get_shortcode_regex() . '/', $post->post_content, $matches)) {
            foreach ($matches[0] as $shortcode) {
                if (strpos($shortcode, $plugin_shortcode) === 1 && preg_match('/ente=([a-z0-9]+)/i', $shortcode, $ente_match)) {
                    $item['shortcode'][] = str_replace('ente=' . $ente_match[1], 'ente=<u>' . $ente_match[1] . '</u>', $shortcode);
                    $shortcode_count++;
                }
            }
        }

        if (count($item['shortcode'])) {
            $items[] = $item;
        }
    }
}

//
?><div class="wrap">
    <h1 style="margin-bottom: 15px;"><?php echo get_admin_page_title(); ?></h1>

    <form method="post">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo $options_slug; ?>[shortcode_ente]">Nuovo ente</label>
                </th>
                <td>
                    <input type="text" id="<?php echo $options_slug; ?>[shortcode_ente]" name="<?php echo $options_slug; ?>[shortcode_ente]" size="6" />
                    <input type="submit" class="button button-primary" name="<?php echo $options_slug; ?>[action_change]" value="Modifica" />
                </td>
            </tr>
        </table>

        <?php if (count($items)) : ?>
            <h2>Trovati <?php echo count($items); ?> elementi con <?php echo $shortcode_count; ?> shortcode.</h2>
            <?php foreach ($items as $item) : ?>
                <div style="padding: .6em; line-height: 1.6em; border: 1px solid #bbb; margin-bottom: .6em; background-color: #e9e9e9;">
                    <b><a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a></b>
                    (<?php echo $item['type']; ?>)
                    <?php foreach ($item['shortcode'] as $shortcode) : ?>
                        <br />&raquo; <b>Shortcode</b> <?php echo $shortcode; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            Nessuno shortcode trovato.
        <?php endif; ?>
    </form>
</div>