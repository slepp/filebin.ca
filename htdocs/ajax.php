<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("filebin.inc.php");
if(!isset($_GET["dl"])) {
    exit;
}
$p = uploadprogress_get_info($_GET["dl"]);
if(empty($p)) {
    header("Content-Type: text/javascript");
    $sth = getDB()->prepare("SELECT error,error_message FROM upload_tracking WHERE upload_id=? ORDER BY created DESC LIMIT 1");
    $sth->execute(array($_GET["dl"]));
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $sth = null;
    if($row['error']) {
        print 'location.href = "http://filebin.ca/error.php?id='.$_GET['dl'].'";';
        exit;
    }
    $f = new File();
    $f->byUploadID($_GET["dl"]);
    if($f->valid) {
        print 'location.href = "http://filebin.ca/complete.php?id='.$_GET['dl'].'";';
    } else {
        print 'if(canForward || requestCount++ > 7) { location.href = "http://filebin.ca/complete.php?id='.$_GET['dl'].'"; }';
    }
    exit;
}
header("Content-Type: text/javascript");
print 'document.getElementById("progress").innerHTML = "'.sprintf("<div style='border:1px solid black;background:#ccf'>Progress: <b>%0.0f%%</b> complete (%s of %s bytes)<br />Time Remaining: <b>%0.1f</b> minutes at %0.2f KB/s<br /><div style='width:100%%;background:white;'><div style='width:".intval(($p['bytes_uploaded']/$p['bytes_total'])*100)."%%;background:blue;color:white;text-align:center'>".number_format($p['bytes_uploaded']/$p['bytes_total']*100,1)."%%</div></div></div>", $p['bytes_uploaded'] / $p['bytes_total'] * 100, kbFormat($p['bytes_uploaded']), kbFormat($p['bytes_total']), ($p['bytes_total'] - $p['bytes_uploaded']) / $p['speed_average'] / 60, $p['speed_average'] / 1024).'";';
print 'canForward = true;';
?>
