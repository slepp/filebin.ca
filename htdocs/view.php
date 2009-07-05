<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("template.inc.php");
require("filebin.inc.php");

$tag = $_GET["tag"];
$path = substr($_GET["path"],1);

$f = new File();
$f->byTag($tag);

if($f->isValid()) {
    $z = new ZipArchive();
    if($z->open($f->path) === TRUE) {
        if(substr($path, -1) == '/' || $path == "") {
            for($i = 0; $i < $z->numFiles; $i++) {
                $s = $z->statIndex($i);
                if( $path == "" || (substr_compare($path, $s['name'], 0, strlen($path), true) == 0 && $path != $s['name']))
                  $arr[] = $s['name'];
            }
            asort($arr);
            foreach($arr as $n) {
                print '<a href="http://filebin.ca/view/'.$tag.'/'.$n.'">'.$n.'</a><br />';
            }
        } else {
            if(preg_match('/\.jpe?g/i', $path)) {
                header("Content-Type: image/jpeg");
            }
            echo $z->getFromName($path);
        }
        $z = null;
    } else {
        $z = rar_open($f->path);
        if($z) {
            if(substr($path, -1) == '/' || $path == "") {
                foreach(rar_list($z) as $s) {
                    if( $path == "" || (substr_compare($path, $s->name, 0, strlen($path), true) == 0 && $path != $s->name))
                      $arr[] = $s->name;
                }
                asort($arr);
                foreach($arr as $n) {
                    print '<a href="http://filebin.ca/view/'.$tag.'/'.$n.'">'.$n.'</a><br />';
                }
            } else {
                rar_entry_get($z,$path)->extract(false, "/tmp/rar.extr.1");
                $finfo = finfo_open(FILEINFO_MIME);
                header("Content-Type: ".finfo_open($finfo, "/tmp/rar.extr.1"));
                finfo_close($finfo);
                @readfile("/tmp/rar.extr.1");
                unlink("/tmp/rar.extr.1");
            }
            # rar_close($z);
        }
        $z = null;
    }
} else {
    print "No such file?";
}
?>
