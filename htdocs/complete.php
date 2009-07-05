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

if($row['error']) {
        header("HTTP/1.0 302 Temporary Redirect");
        header("Location: http://filebin.ca/error.php?id=".urlencode($_GET["id"]));
        exit;
}

$f = new File();
$cnt = 0;
do {
    $f->byUploadID($_GET["id"]);
    if(!$f->valid)
      sleep(1);
    if($cnt++ > 3)
      break;
} while(!$f->valid);
if(!$f->valid) {
    print "Sorry, error occurred.";
    exit;
}
pageHeader("FileBin - Your file has been uploaded");
print "<div class='ok'><p>Your file is available at <a href='http://filebin.ca/{$f->tag}/{$f->params{name}}'>http://filebin.ca/{$f->tag}/{$f->params{name}}</a> or <a href='http://filebin.ca/{$f->tag}'>http://filebin.ca/{$f->tag}</a></p></div>";
mainForm();
loginForm();
pageFooter();
# FileUtil::removeUploadID($_GET["id"]);
?>
