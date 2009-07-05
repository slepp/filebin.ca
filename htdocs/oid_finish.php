<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require('template.inc.php');
session_start();

require_once "oid_common.php";

// Complete the authentication process using the server's response.
$response = $consumer->complete($_GET["openid_return_to"]);

if ($response->status == Auth_OpenID_CANCEL) {
    // This means the authentication was cancelled.
    print 'Verification canceled.';
    exit;
} else if ($response->status == Auth_OpenID_FAILURE) {
    print "OpenID authentication failed: " . $response->message;
    exit;
} else if ($response->status == Auth_OpenID_SUCCESS) {
    // This means the authentication succeeded.
    $openid = $response->identity_url;
    if ($response->endpoint->canonicalID) {
        $canon = $response->endpoint->canonicalID;
    }

    $sreg = $response->extensionResponse('sreg');

    $addition = false;
    if($_SESSION["acct_auth"] && $_SESSION["acct_official"] != ($canon?$canon:$openid)) {
        $addition = true;
    }
    
    $_SESSION["acct_auth"] = true;
    $_SESSION["acct_username"] = $openid;
    $_SESSION["acct_sreg"] = $sreg;
    
    if(isset($canon))
      $_SESSION["acct_canon"] = $canon;
    else
      unset($_SESSION["acct_canon"]);
    
    $_SESSION["acct_official"] = ($_SESSION["acct_canon"]?$_SESSION["acct_canon"]:$_SESSION["acct_username"]);
    
    if(isset($_GET["redir"])) {
        if(preg_match(',/login\.php$,',$_GET["redir"])) {
            header("Location: http://".$_SERVER["SERVER_NAME"]);
        } else if(preg_match(',/oid\.php$,',$_GET["redir"])) {
            header("Location: http://".$_SERVER["SERVER_NAME"]."/settings.php");
        } else {
            header("Location: ".$_GET["redir"]);
        }
    } else {
        header("Location: http://".$_SERVER["SERVER_NAME"]);
    }
} else {
    print "Sorry, there was a general authentication failure.. <a href=\"/login.php\">Please try again.</a>";
    exit;
}
?>
