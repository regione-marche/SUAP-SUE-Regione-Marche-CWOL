<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15" />

<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<meta name="google" value="notranslate" />
<link  id="favicon" href="./favicon.ico" rel="shortcut icon" type="image/x-icon"  />

<title><?php echo defined('ITA_PAGE_TITLE') && ITA_PAGE_TITLE ? ITA_PAGE_TITLE : 'Italsoft'; ?></title>

<!--<link rel="stylesheet" type="text/css" href="./public/css/normalize.css" />-->
<link rel="stylesheet" type="text/css" href="public/css/fonts.css" />

<script type="text/javascript" src="./public/libs/jquery/jquery-<?php echo Conf::JQUERY_VERSION ?>.min.js"></script>
<?php
foreach (Conf::$jquery_plugin as $k => $v) {
    echo '<script type="text/javascript" src="./public/libs/jquery/' . $k . '.' . $v . '.js"></script>' . "\n";
}
foreach (Conf::$jquery_plugin_css as $k => $v) {
    echo '<link rel="stylesheet" type="text/css" href="./public/libs/jquery/' . $k . '.' . $v . '.css" />' . "\n";
}
foreach (Conf::$js_scripts as $k => $v) {
    echo '<script type="text/javascript" src="./public/js/' . $k . '.' . $v . '.js"></script>' . "\n";
}
?>

<script type="text/javascript" src="./public/libs/jqueryui-<?php echo Conf::JQUERY_UI_VERSION ?>/js/jquery-ui-<?php echo Conf::JQUERY_UI_VERSION ?>.custom.min.js"></script>
<script type="text/javascript" src="./public/libs/jqueryui-<?php echo Conf::JQUERY_UI_VERSION ?>/i18n/jquery.ui.datepicker-it.js" ></script>

<?php if (version_compare(Conf::JQUERY_UI_VERSION, '1.11.3', '>=')) : ?>
    <link rel="stylesheet" type="text/css" href="./public/libs/jqueryui-<?php echo Conf::JQUERY_UI_VERSION ?>/jquery-ui.css" />   
<?php endif; ?>

<?php
$theme = Conf::JQUERY_UI_STYLE;

if (defined('ITA_THEME')) {
    $theme = ITA_THEME;
}

if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
}
?>
<link rel="stylesheet" class="ui-theme"  type="text/css" href="./public/libs/jqueryui-<?php echo Conf::JQUERY_UI_VERSION ?>/themes/<?php echo $theme ?>/jquery-ui-<?php echo Conf::JQUERY_UI_VERSION ?>.custom.css" />   
<script type="text/javascript" src="./public/libs/jqueryui-<?php echo Conf::JQUERY_UI_VERSION ?>/themeswitchertool/myswitcher.js"></script>

<link rel="stylesheet" class="ui-theme"  type="text/css" href="./public/libs/jquery-ui-iconfont/jquery-ui-1.11.icon-font.min.css" />

<link rel="stylesheet" type="text/css" href="./public/libs/jqGrid-<?php echo Conf::JQUERY_JQGRID_VERSION ?>/css/ui.jqgrid.css" />
<script type="text/javascript" src="./public/libs/jqGrid-<?php echo Conf::JQUERY_JQGRID_VERSION ?>/js/i18n/grid.locale-it.js" ></script>
<script type="text/javascript" src="./public/libs/jqGrid-<?php echo Conf::JQUERY_JQGRID_VERSION ?>/js/jquery.jqGrid.src.js"></script>
<script type="text/javascript" src="./public/libs/jqgrid-plugins/jQuery.jqGrid.setColWidth.js"></script>

<?php if (defined("Conf::JQUERY_FULLCALENDAR_VERSION")) { ?>
    <link rel='stylesheet' href="./public/libs/fullcalendar-<?php echo Conf::JQUERY_FULLCALENDAR_VERSION ?>/fullcalendar.min.css" />
    <script src='./public/libs/fullcalendar-<?php echo Conf::JQUERY_FULLCALENDAR_VERSION ?>/fullcalendar.min.js'></script>
    <!-- <script src='./public/libs/fullcalendar-<?php echo Conf::JQUERY_FULLCALENDAR_VERSION ?>/gcal.js'></script> -->
    <script src='./public/libs/fullcalendar-<?php echo Conf::JQUERY_FULLCALENDAR_VERSION ?>/lang/it.js'></script>
<?php } ?>

<link rel="stylesheet" type="text/css" href="./public/libs/colorpicker-<?php echo Conf::JQUERY_COLORPICKER_VERSION ?>/jquery.colorpicker.css" />
<script type="text/javascript" src="./public/libs/colorpicker-<?php echo Conf::JQUERY_COLORPICKER_VERSION ?>/i18n/jquery.ui.colorpicker-en.js"></script>
<script type="text/javascript" src="./public/libs/colorpicker-<?php echo Conf::JQUERY_COLORPICKER_VERSION ?>/jquery.colorpicker.js"></script>
<script type="text/javascript" src="./public/libs/colorpicker-<?php echo Conf::JQUERY_COLORPICKER_VERSION ?>/parts/jquery.ui.colorpicker-memory.js"></script>

<link rel="stylesheet" type="text/css" href="./public/libs/jquery/jquery.imgareaselect.0910/css/imgareaselect-default.css" />

<script type="text/javascript" src="./public/libs/plupload-<?php echo Conf::JQUERY_PLUPLOAD_VERSION ?>/js/plupload.full.js"></script>

<script type="text/javascript" src="./public/libs/tinymce-<?php echo Conf::JQUERY_TINYMCE_VERSION ?>/jquery.tinymce.js"></script>

<script type="text/x-jtk-templates" src="public/libs/jsplumb/flowchart/templates.html?t=<?php echo time(); ?>"></script>
<link rel="stylesheet" class="ita-style" type="text/css" href="./public/libs/jsplumb/jsplumbtoolkit-defaults.css" />
<link rel="stylesheet" class="ita-style" type="text/css" href="./public/libs/jsplumb/flowchart/style.css" />

<link rel="stylesheet" class="ita-style" type="text/css" href="./public/css/<?php echo Conf::ITA_STYLE ?>?t=<?php echo time(); ?>" />
<script type="text/javascript" src="./public/js/itaEngine-<?php echo Conf::ITA_ENGINE_VERSION ?>.js?t=<?php echo time(); ?>"></script>
<script type="text/javascript" src="./public/js/itaUtil.js?t=<?php echo time(); ?>"></script>

<style>
    html, body { font-family: "Open Sans", Roboto, Verdana, Arial, sans-serif; font-size: 12px; }
    .ui-widget,
    .ui-widget input,
    .ui-widget select,
    .ui-widget textarea,
    .ui-widget button { font-family: inherit; font-size: inherit; }
</style>
