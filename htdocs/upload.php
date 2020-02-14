<?php
$uploadDir = '../uploads';
header('Content-type: application/json');
if (!empty($_FILES)) {
 	$tmpFile = $_FILES['file']['tmp_name'];
 	$filename = $uploadDir.'/'.time().'-'. $_FILES['file']['name'];
 	move_uploaded_file($tmpFile,$filename);
    echo json_encode(['file' => basename($filename)]);
}
