<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("filebin.inc.php");
require("clamd.inc.php");
session_start();
ignore_user_abort();

$randCharArray = 'abcdefghjkmnopqrstuvwxyz'; #ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$randStartChar = 'abcdefghjkmnopqrstuvwxyz';

function randomTag($len = 6) {
        global $randCharArray, $randStartChar;
        $str = "";
        if($len == -1)
            $len = mt_rand(6,8);
        $len-=2;
        $str .= substr($randStartChar, mt_rand(0,strlen($randStartChar)), 1);
        for($i = 0; $i < $len; $i++) {
                    $str .= substr($randCharArray, mt_rand(0,strlen($randCharArray)), 1);
        }
        $str .= substr($randCharArray, mt_rand(0,strlen($randCharArray)), 1);
        return $str;
}

function logError($err) {
    print $err;
    $sth = getDB()->prepare("INSERT INTO upload_tracking (upload_id,file_id,error,error_message) VALUES (?,?,'t',?)");
    $sth->execute(array($_POST["UPLOAD_IDENTIFIER"], 6, $err));
    exit;
}

if(isset($_FILES["file"])) {
    $f = $_FILES["file"];

    if(!is_uploaded_file($f['tmp_name'])) {
        logError("No file was uploaded. Please try again.");
        exit;
    }
    
    $err = "";
    switch($f['error']) {
     case UPLOAD_ERR_OK: break;
     case UPLOAD_ERR_INI_SIZE: logError("Uploaded file exceeds the server side limit."); break;
     case UPLOAD_ERR_FORM_SIZE: logError("The form specified a maximum size which is smaller than the file provided."); break;
     case UPLOAD_ERR_PARTIAL: logError("Only a part of the file was received. Please try again."); break;
     case UPLOAD_ERR_NO_FILE: logError("No file was uploaded."); break;
     case UPLOAD_ERR_NO_TMP_DIR: logError("Temporary upload directory is unavailable."); break;
     case UPLOAD_ERR_CANT_WRITE: logError("Failed to write to the disk."); break;
     case UPLOAD_ERR_EXTENSION: logError("The file was stopped by an extension."); break;
     default: logError("Unhandled file upload error."); break;
    }
    
    $upload_dir = FILEBIN_STORE_PATH;
    $upload_name = hash_file('sha1', $f['tmp_name']);
    $upload_file = $upload_dir . "/" . $upload_name;

    if(!file_exists($upload_file)) {
        if(!move_uploaded_file($f['tmp_name'], $upload_file)) {
            logError("Could not move uploaded file. Sorry.");
            exit;
        }
        chmod($upload_file, 0644);
    }
    
    if(filesize($upload_file) == 0) {
        logError("File is empty.");
        exit;
    }
    
    if(!scanFile($upload_file)) {
        rename($upload_file, $upload_file."-virus");
        logError("File did not pass the virus scan.");
        exit;
    }
    
    $fd = fopen("/tmp/upload.log", "a");
    fwrite($fd, "Upload: ".serialize($_FILES)." with ".serialize($_POST)."\n");
    fclose($fd);
    print_r($_FILES);
    
    $file = new File();
    $file->byPath($upload_file);
    if($file->isValid()) {
        $file->incrementShrinks();
    } else {
        $file->path = $upload_file;
        $file->tag = randomTag();
        $file->params{'name'} = preg_replace(',[^a-zA-Z0-9_:;!@#$%^+=.~-],','',$f['name']);
        $file->params{'content_type'} = $f['type'];
        $file->params{'size'} = filesize($upload_file);
        $file->creator = new Creator($_SERVER["REMOTE_ADDR"], ($_SESSION["acct_auth"]?$_SESSION["acct_official"]:null));
        $file->store();
    }
    $sth = getDB()->prepare("INSERT INTO upload_tracking (upload_id,file_id,error) VALUES (?,?,'f')");
    $sth->execute(array($_POST["UPLOAD_IDENTIFIER"], $file->id));
}
?>
