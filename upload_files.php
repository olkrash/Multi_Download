<?php
session_start();

function sendFile()
{
    $sessionID = session_id();
    $path = "upload/$sessionID";
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    /* Getting file name */
    $filename = $_FILES['file']['name'];

    /* Getting File size */
    $filesize = $_FILES['file']['size'];

    /* Location */
    $location = $path . "/" . $filename;

    $return_arr = [];

    /* Upload file */
    if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
        chmod($location, 0777);
        $return_arr = array("name" => $filename, "size" => $filesize, "src" => $location);
    }

    return json_encode($return_arr);
}

echo sendFile();