<?php
$domain = $_GET['domain'];
$token = $_GET['token'];
$resourceId = $_GET['resourceid'];
$classificazione = $_GET['classificazione'];

function getPluginRoot() {        
    return base64_decode($_GET['pluginroot']);
}

function getEndpoint() {
    return base64_decode($_GET['endpoint']);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <title>Dictionary</title>        
        <link rel="stylesheet" type="text/css" href="<?php echo getPluginRoot(); ?>/common/bootstrap/css/bootstrap-3.3.7.min.css">  
        <link rel="stylesheet" type="text/css" href="<?php echo getPluginRoot(); ?>/dictionary/style.css">  
        <script>
            // Valorizzare queste variabili perchè servono all'interno di pluginBase.js
            window.ooPluginConfig = {
                type: 'dictionary',
                domain: '<?php echo $domain; ?>',
                token: '<?php echo $token; ?>',
                resourceid: '<?php echo $resourceId; ?>',
                classificazione: '<?php echo $classificazione; ?>',
                pluginroot: '<?php echo getPluginRoot(); ?>',
                endpoint: '<?php echo getEndpoint(); ?>'
            };                    
        </script>
        <script type="text/javascript" src="<?php echo getPluginRoot(); ?>/common/pluginBase.js"></script>
        <script type="text/javascript" src="<?php echo getPluginRoot(); ?>/common/jquery/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="<?php echo getPluginRoot(); ?>/common/bootstrap/js/bootstrap-3.3.7.min.js"></script>
        <script type="text/javascript" src="<?php echo getPluginRoot(); ?>/dictionary/dictionary.js"></script>
    </head>
    <body>
        <div id="main" class="container-fluid">            
        </div>        
    </body>
</html>