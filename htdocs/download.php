<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

# This does peculiar things with Zip files if On
ini_set('zlib.output_compression','Off');

require("filebin.inc.php");

$f = new File();
$f->byTag($_GET["tag"]);
if($f->isValid()) {
    $etag = hash('sha1', $f->path).'-'.filemtime($f->path);
    if(isset($_SERVER["HTTP_IF_NONE_MATCH"])) {
        if($etag == $_SERVER["HTTP_IF_NONE_MATCH"]) {
            header("HTTP/1.0 304 Not Modified");
            exit;
        }
    }
    
    if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) && $_SERVER["HTTP_IF_MODIFIED_SINCE"] == gmdate("D, d M Y H:i:s", filemtime($f->path)) . " GMT") {
            header("HTTP/1.0 304 Not Modified");
            exit;
    }
    
    if(preg_match('/\.txt$/', $_GET["path"])) {
        header("Content-Type: text/plain");
    } else if($_GET["bin"] == "y") {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"".basename($_GET["path"])."\"");
    } else {
        header("Content-Type: ".$f->params{'content_type'});
        header("Content-Disposition: attachment; filename=\"".basename($_GET["path"])."\"");
    }
    header("Content-Length: ".$f->params{'size'});
    header("ETag: ".$etag);
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($f->path)) . " GMT");
    header("Expires: ".gmdate("D, d M Y H:i:s", filemtime($f->path)+(60*60*24*30)) . " GMT");
    
    $f->incrementHits();
    readfile($f->path);
    exit;
}
header("HTTP/1.0 404 Not Found");
print "Nothing found at that tag.";
?>
