<?php

print_r('<pre>');
print_r($_POST);
print_r($_FILES);
print_r('</pre>');

function detectRequestBody() {
    $rawInput = fopen('php://input', 'r');
    $tempStream = fopen('php://temp', 'r+');
    stream_copy_to_stream($rawInput, $tempStream);
    rewind($tempStream);

    return $tempStream;
}