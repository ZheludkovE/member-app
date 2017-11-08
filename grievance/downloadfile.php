<?php
require 'DBQuery.php';
require '../db.php';

$query = new DBQuery(getDB());
if (isset($_GET['id']))
{
    $unsecure = substr($_GET['id'], 4);
    $id = base64_decode($unsecure);  
    $file = $query->getGrievanceFile($id, $extension, $fileName, $fileSize);
    
    if ($file != null) {  
        switch ($extension)
        {
        case "application/pdf":
        header("Content-type: application/pdf");
        break;
        // add more headers for other content types here
        default;
        header("Content-type: application/octet-stream");
        break;
        }
        header("Content-Disposition: attachment; filename=".$fileName."");
        header("Content-length: $fileSize");
        ob_clean();
        flush();
	echo$file;
    } else {
        echo "File not found";
    }
}