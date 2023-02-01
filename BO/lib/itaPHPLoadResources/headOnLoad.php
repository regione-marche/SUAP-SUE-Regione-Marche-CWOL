<script type="text/javascript">
    $(function () {
<?php
$tipoAccesso = "";
if (isset($_GET['access'])) {
    $tipoAccesso = $_GET['access'];
}

if (isset($_COOKIE['redirectTmpToken'])) {
    echo "tmpToken = '{$_COOKIE['redirectTmpToken']}';";
    unset($_COOKIE['redirectTmpToken']);
    setcookie('redirectTmpToken', '', -3600);
}

if (isset($_COOKIE['redirectToken'])) {
    $_GET['accesstoken'] = $_COOKIE['redirectToken'];
    $_GET['access'] = 'direct';
    $_GET['accessorg'] = $_GET['ditta'];
    $tipoAccesso = 'direct';
    unset($_COOKIE['redirectToken']);
    setcookie('redirectToken', '', time() - 3600);
}

if ($tipoAccesso == 'j-net') {
    $token = $_GET['TOKEN'];
    $access = $_GET['access'];
    $ditta = $_GET['ditta'];
    echo "itaGo('ItaCall','',{TOKEN:'$token',bloccaui:false,event:'onload',access:'$access',ditta:'$ditta'});";
} else if ($tipoAccesso == 'direct') {
    $token = $_GET['accesstoken'];
    $access = $_GET['access'];
    $ditta = $_GET['accessorg'];
    $return = "";
    if (isset($_GET['accessreturn']) && $_GET['accessreturn']) {
        $return = ",accessreturn:'" . $_GET['accessreturn'] . "'";
    }
    unset($_GET['accesstoken']);
    unset($_GET['access']);
    unset($_GET['accessorg']);
    unset($_GET['accessreturn']);
    $extraParms = "";
    foreach ($_GET as $key => $value) {
        $extraParms .= ",$key:'$value'";
    }
    echo "itaGo('ItaCall','',{accesstoken:'$token',bloccaui:false,event:'onload',access:'$access',accessorg:'$ditta'$return$extraParms});";
    echo "$('#desktop').trigger('itaEngineStart');";
} else if ($tipoAccesso == 'admin') {
    $access = $_GET['access'];
    $return = "";
    echo "itaGo('ItaCall','',{bloccaui:false,event:'onload',access:'admin'});";
} else {
    $ditta = "";
    if (isset($_GET['ditta'])) {
        $ditta = ",ditta:'" . $_GET['ditta'] . "'";
    } elseif (isset($_GET['organization'])) {
        $ditta = ",ditta:'" . $_GET['organization'] . "'";
    }

    echo "itaGo('ItaCall','',{bloccaui:false,event:'onload',access:'validate'$ditta});";
}
?>

    });
</script>
