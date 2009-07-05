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
pageHeader('FileBin - Your Files', array('scriptaculous'=>true));
mainForm();
if(false /*!$_SESSION["acct_auth"]*/) {
    ?>
<div id="login">
	<form method="get" action="/oid_login.php">
	<div style="text-align:center;margin-top:1em;margin-bottom:1em">
	<label for="openid_url" style="text-align:right">Login with OpenID:</label>
	<input id="openid_url" type="text" name="openid_url" style="width:9em;background:url(http://static.pastebin.ca/imgs/openid-bg.png) no-repeat #efe;background-position:0 50%;padding-left:18px;" />
	<input type="submit" value="Login" class="sub" /></div>
    </form>
</div>
    <?php
} else {
    $s = array('t' => 't', 'u' => 'u', 'h' => 'h', 's' => 's');
    if(!isset($_GET["s"])) {
        $field = 'created DESC';
        $s['s'] = 'S';
    } else if($_GET["s"] == 't') {
        $field = 'tag ASC';
        $s['t'] = 'T';
    } else if($_GET["s"] == 'u') {
        $field = 'name ASC';
        $s['u'] = 'U';
    } else if($_GET["s"] == 'h') {
        $field = 'hits ASC';
        $s['h'] = 'H';
    } else if($_GET["s"] == 'T') {
        $field = 'tag DESC';
    } else if($_GET["s"] == 'U') {
        $field = 'name DESC';
    } else if($_GET["s"] == 'H') {
        $field = 'hits DESC';
    }
    ?>
	<div>
	<table class="turls" width="80%">
	<thead>
	<tr><th colspan="3">Your Active Files</th></tr>
	<tr><th><a href="myfiles.php?s=<?php print $s['t']?>">Tag</a></th><th><a href="myfiles.php?s=<?php print $s['u']?>">Name</a></th><th><a href="myfiles.php?s=<?php print $s['h']?>">Hits</a></th></tr>
	</thead>
	<tbody>
	<?php
    $c = new Creator($_SERVER["REMOTE_ADDR"], ($_SESSION["acct_auth"]?$_SESSION["acct_official"]:null));
    $i = 0;
    $ids = array();
    foreach (getDB()->query("SELECT filebin.*,s.hits FROM filebin JOIN statistics s ON s.file_id=filebin.file_id WHERE creator=".$c->id." ORDER BY $field") as $row) {
        $ids[] = $row['file_id'];
        print '<tr';
        if($i++%2 == 1)
          print ' style="background:#ccc"';
        print '>';
        print '<td><a href="http://filebin.ca/'.$row['tag'].'">'.$row['tag'].'</a></td>';
        #print '<td><span id="tid'.$row['file_id'].'" style="color:#00c">'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'</span>';
        print '<td><a href="http://filebin.ca/'.$row['tag'].'/'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'">'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'</a>';
        if(!empty($row['title'])) {
            print '<br />'.htmlentities($row['title'],ENT_COMPAT,'UTF-8');
        }
        print '</td>';
        print '<td style="text-align:right">'.$row['hits'].'</td>';
        print '<td><span id="tide'.$row['file_id'].'">Edit</span></td>';
        print '</tr>';
    }
    $sth = null;
    ?>
	</tbody>
	</table>
	<script type="text/javascript">
	<?php
/*    $i = 0;
    foreach($ids as $id) {
        print 'new Ajax.InPlaceEditor("tid'.$id.'","/editFileInPlace.php",{externalControl:"tide'.$id.'",okText:"Change",cancelText:"Cancel",cols:60,highlightendcolor:"';
        if($i++%2 == 1)
          print '#CCCCCC';
        else
          print '#EFF5EF';
        print '",callback:function(form,value){return "id='.$id.'&value=" + encodeURIComponent(value);}});';
    }*/
    ?>
    </script>
</div>
	<?php
    if($_SESSION['acct_username'] == 'xri://=!F0AA.4219.B594.355F') {
        print '<table>';
        foreach(getDB()->query("SELECT filebin.*,s.hits FROM filebin JOIN statistics s on s.file_id=filebin.file_id ORDER BY file_id") as $row) {
            print '<tr';
            if($i++%2 == 1)
              print ' style="background:#ccc"';
            print '>';
            print '<td><a href="http://filebin.ca/'.$row['tag'].'">'.$row['tag'].'</a></td>';
            #print '<td><span id="tid'.$row['file_id'].'" style="color:#00c">'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'</span>';
            print '<td><a href="http://filebin.ca/'.$row['tag'].'/'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'">'.htmlentities($row['name'],ENT_COMPAT,'UTF-8').'</a>';
            if(!empty($row['title'])) {
                print '<br />'.htmlentities($row['title'],ENT_COMPAT,'UTF-8');
            }
            print '</td>';
            print '<td style="text-align:right">'.$row['hits'].'</td>';
            print '</tr>';
        }
        print '</table>';
    }
}
loginForm();
pageFooter();
?>
