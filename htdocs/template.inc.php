<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

function pageHeader($title = "FileBin.ca -- Your Place for Files", $arr = null) {
    header("Content-Type: text/html; charset=UTF-8");
    print '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
    ?>
<html xmlns="http://www.w3.org/1999/xhtml" 
      xmlns:svg="http://www.w3.org/2000/svg"
      xmlns:xlink="http://www.w3.org/1999/xlink">
<head>
	<title><?php print $title?></title>
	<link rel="stylesheet" type="text/css" href="/filebin.css" />
	<script type="text/javascript" src="/prototype.js"></script>
	<?php
    if(is_array($arr) && $arr['scriptaculous']) {
        ?>
	<script type="text/javascript" src="/scriptaculous.js?load=effects,controls"></script>
        <?php
    }
    if(is_array($arr) && !empty($arr['robots'])) {
        ?>
        <meta name="robots" content="<?php print $arr['robots']?>" />
        <?php
    }
    ?>
</head>
<body>
<div style="background:#eff5ef">
<h1><?php print $title?></h1>
<?php
}

function pageFooter() {    
    ?>
	<div id="other" style="text-align:center;font-size:80%;color:#333;margin-bottom:0.6em">
	Other Projects:	<a href="http://pastebin.ca/">Pastebin</a> | <a href="http://imagebin.ca/">Imagebin</a> | <a href="http://turl.ca/">TURL - Tiny URLs</a>
	</div>
	<div id="footer" style="text-align:center;background:#dde">
	&copy; 2009 <a href="http://blog.slepp.ca/">Stephen Olesen</a> - a member of the <a href="http://pastebin.ca/">pastebin</a> project group
	</div>
	</div>
	</body>
</html>
	<?php
}

function mainForm($arr = null) {
    $uid = uniqid();
    if($arr == null)
      $arr = array('tag' => '', 'url' => '');
?>
        <script>
            var a;
            var canForward;
            var requestCount;
            canForward = false;
            requestCount = 0;
            function submitHandling() {
                          $('progress').innerHTML = 'Connecting...';
                          $('progress').style.display = 'block';
                          a = new Ajax.PeriodicalUpdater('updater', '/ajax.php?dl=<?php print $uid ?>', { method: 'get', frequency:	2, decay: 1 });
                        }
        </script>
        <div id="form" style="text-align:center">
        <fieldset style="width:80%;margin-left:auto;margin-right:auto;border:3px solid #ddd;background:#e0e0ff"><legend style="background:#c0c0ff;border-left:2px solid blue;border-right:2px solid blue;margin-bottom:1em;">Upload a File</legend>
            <p>Upload any file. Must be less than 50 megabytes. Files will be kept in a rotating pool of space, and may be removed at any time.</p>
            <form method="post" action="upload.php" enctype="multipart/form-data" onsubmit="submitHandling(this); return true;" target="target_upload">
            <iframe id="target_upload" name="target_upload" src="" style="width:1px;height:1px;border:1px;display:none"></iframe>
            <input type="hidden" name="MAX_FILE_SIZE" value="62428800" />
            <input type="hidden" name="UPLOAD_IDENTIFIER" value="<?php print $uid ?>" />
            <table style="margin-left:auto;margin-right:auto">
            <tr><td>File to upload:	</td><td><input type="file" name="file" size="50" /></td></tr>
            <tr><td colspan="2" style="text-align:center"><input type="submit" value="Upload" class="sub" /></td></tr>
            <tr><td colspan="2"><p id="progress" style="display:none"></p></td></tr>
            </table>
            </form>
    	</fieldset>
        </div>
        <p id="updater" style="display:none"></p>
    <?php
}

function loginForm() {
    if(!$_SESSION["acct_auth"]) {
    ?>
<div id="login">
	<form method="get" action="/oid_login.php">
	<div style="text-align:center;margin-top:1em;margin-bottom:1em">
	<label for="openid_url" style="text-align:right">Login with OpenID:</label>
	<input id="openid_url" type="text" name="openid_url" style="width:9em;background:url(http://static.pastebin.ca/imgs/openid-bg.png) no-repeat #efe;background-position:0 50%;padding-left:18px;" />
	<input type="submit" value="Login" class="sub" /><br />
	<a href="/oid.php" class="faded">Want a free account?</a>
	</div>
    </form>
</div>
    <?php
    } else {
    ?>
<div id="account">
	<fieldset style="width:80%;margin-left:auto;margin-right:auto;margin-top:1em;border:3px solid #ddd;background:#e0e0ff"><legend style="background:#c0c0ff;border-left:2px solid blue;border-right:2px solid blue;margin-bottom:1em;">Your Filebin Account</legend>
	<p style="text-align:center"><a href="/myfiles.php">Your Files</a><br /><a href="/oid_logout.php">Logout of FileBin</a></p>
	</fieldset>
</div>
	<?php
    }
}
?>
