<script type="text/javascript">
	(function(){
<?php
$ditta = "";
if (isset($_GET['ditta'])) {
    $ditta = ",ditta:'" . $_GET['ditta'] . "'";
} elseif (isset($_POST['ditta'])) {
    $ditta = ",ditta:'" . $_POST['ditta'] . "'";
} elseif (isset($_GET['organization'])) {
    $ditta = ",ditta:'" . $_GET['organization'] . "'";
}
echo "itaGo('ItaCall','',{bloccaui:false,event:'onload',access:'validatemobile'$ditta});";
?>
	}());
</script>

