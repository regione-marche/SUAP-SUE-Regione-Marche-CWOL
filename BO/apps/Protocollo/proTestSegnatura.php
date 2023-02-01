<?php
$root = 'Segnatura';
$xml=  file_get_contents('/users/itaEngine/apps/Protocollo/Segnatura_nuova2.xml');
$old = new DOMDocument;
if(!$old->loadXML($xml)){
    die('non caricato xml da controllare');
}else{

}

$creator = new DOMImplementation;
$doctype = $creator->createDocumentType($root, null, '/users/itaEngine/apps/Protocollo/Segnatura-2009-03-31.dtd');
$new = $creator->createDocument(null, null, $doctype);
$new->encoding = "ISO-8859-1";

$oldNode = $old->getElementsByTagName($root)->item(0);
$newNode = $new->importNode($oldNode, true);
$new->appendChild($newNode);
if ($new->validate()) {
    echo "Valid";
} else {
    echo "Not valid";
}

print_r("<pre>");
print_r(htmlspecialchars($xml));
print_r("<pre>");
?>
