<?php if ( isset( $_POST['headResource'] ) ) : ?>

	<?php
		$css = array();
		foreach (Conf::$jquery_plugin_css as $k => $v) {
			array_push( $css, '/public/libs/jquery.mobile/' . $k . '.' . $v . '.css' );
		}
		array_push( $css, '/public/libs/jquery.mobile-' . Conf::JQUERY_MOBILE_VERSION . '/jquery.mobile-' . Conf::JQUERY_MOBILE_VERSION . '.min.css' );
		array_push( $css, '/public/libs/jquery.mobile-' . Conf::JQUERY_MOBILE_VERSION . '/jquery.itaMobile.min.css' );
		array_push( $css, '/public/css/' . Conf::ITA_STYLE );
                array_push( $css, '/public/css/ita-base-style.css' );

		$js = array();
		array_push( $js, '/public/libs/jquery.mobile/jquery-' . Conf::JQUERY_VERSION . '.min.js' );
		array_push( $js, '/public/libs/jquery.mobile-' . Conf::JQUERY_MOBILE_VERSION . '/jquery.mobile-' . Conf::JQUERY_MOBILE_VERSION . '.min.js' );
		array_push( $js, '/public/js/itaUtil.js' );
		foreach (Conf::$jquery_plugin as $k => $v) {
			array_push( $js, '/public/libs/jquery.mobile/' . $k . '.' . $v . '.js' );
		}
		array_push( $js, '/public/libs/plupload-' . Conf::JQUERY_PLUPLOAD_VERSION . '/js/plupload.full.js' );
		array_push( $js, '/public/js/itaMobile-' . Conf::ITA_MOBILE_VERSION . '.js' );
		
		echo json_encode( array('js' => $js, 'css' => $css));
	?>

<?php else : ?>

	<title>itaMobile by Italsoft</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php
	foreach (Conf::$jquery_plugin_css as $k => $v) {
		echo '<link rel="stylesheet" type="text/css" href="./public/libs/jquery.mobile/' . $k . '.' . $v . '.css" media="screen" />' . "\n";
	}
	?>

	<script type="text/javascript" src="./public/libs/jquery.mobile/jquery-<?php echo Conf::JQUERY_VERSION ?>.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./public/libs/jquery.mobile-<?php echo Conf::JQUERY_MOBILE_VERSION ?>/jquery.mobile-<?php echo Conf::JQUERY_MOBILE_VERSION ?>.min.css" media="screen" />   
	<link rel="stylesheet" type="text/css" href="./public/libs/jquery.mobile-<?php echo Conf::JQUERY_MOBILE_VERSION ?>/jquery.itaMobile.min.css" media="screen" />   
	<script type="text/javascript" src="./public/libs/jquery.mobile-<?php echo Conf::JQUERY_MOBILE_VERSION ?>/jquery.mobile-<?php echo Conf::JQUERY_MOBILE_VERSION ?>.min.js"></script>
	<script type="text/javascript" src="./public/js/itaUtil.js"></script>

	<?php
	foreach (Conf::$jquery_plugin as $k => $v) {
		echo '<script type="text/javascript" src="./public/libs/jquery.mobile/' . $k . '.' . $v . '.js"></script>' . "\n";
	}
	?>

	<script type="text/javascript" src="./public/libs/plupload-<?php echo Conf::JQUERY_PLUPLOAD_VERSION ?>/js/plupload.full.js"></script>

	<link rel="stylesheet" class="ita-style" type="text/css" href="./public/css/<?php echo Conf::ITA_STYLE ?>" media="screen" />
        <link rel="stylesheet" class="ita-style" type="text/css" href="./public/css/ita-base-style.css" media="screen" />
	<script type="text/javascript" src="./public/js/itaMobile-<?php echo Conf::ITA_MOBILE_VERSION ?>.js"></script>

<?php endif; ?>