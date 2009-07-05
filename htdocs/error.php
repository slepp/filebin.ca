<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("template.inc.php");
require("filebin.inc.php");
session_start();

$sth = getDB()->prepare("SELECT error,error_message FROM upload_tracking WHERE upload_id=? ORDER BY created DESC LIMIT 1");
$sth->execute(array($_GET["id"]));
$row = $sth->fetch(PDO::FETCH_ASSOC);
$sth = null;

if(!$row['error']) {
    header("HTTP/1.0 302 Temporary Redirect");
    header("Location: http://filebin.ca/complete.php?id=".urlencode($_GET["id"]));
    exit;
}

pageHeader("FileBin - Your file upload has failed");
print "<div class='error'><p>Error: ".$row['error_message']."</p></div>";
mainForm();
loginForm();
pageFooter();
# FileUtil::removeUploadID($_GET["id"]);
?>
