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

pageHeader("FileBin.ca - The Place for Your Files");
mainForm();

$sth = getDB()->prepare("SELECT count(*),sum(size) FROM filebin WHERE active");
$sth->execute();
$r = $sth->fetch();

print "<div style=\"text-align:center\"><p>The FileBin has ".number_format($r['count'])." files totaling ".kbFormat($r['sum']).".</p></div>";

loginForm();
pageFooter();
?>
