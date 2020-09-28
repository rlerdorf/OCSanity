<?php
$uploadDir = '../uploads';
header('Content-type: application/json');
if (!empty($_FILES)) {
    $tmpFile = $_FILES['file']['tmp_name'];
    $contents = file_get_contents($tmpFile);
    if(preg_match("@&lt;\s*script\s*&gt;@i", $contents)) {
        echo json_encode(['file' => '/']);
    } else {
        $filename = tempnam($uploadDir, basename($_POST['ruleset']));
        move_uploaded_file($tmpFile,$filename);
        echo json_encode(['file' => basename($filename)]);
    }
}
