<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("template.inc.php");
session_start();

require_once "oid_common.php";

// Render a default page if we got a submission without an openid
// value.
if (empty($_GET['openid_url'])) {
    $error = "Expected an OpenID URL.";
    print "$error";
    exit(0);
}

if(!preg_match('/(^[=@$+!]|\.|^http:\/\/)/',$_GET["openid_url"])) {
  $_GET["openid_url"] = 'http://idbin.ca/'.urlencode($_GET["openid_url"]);
}

$scheme = 'http';
if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
    $scheme .= 's';
}

$openid = $_GET['openid_url'];
$process_url = sprintf("$scheme://%s/oid_finish.php?redir=%s",
                       $_SERVER['SERVER_NAME'], urlencode($_SERVER["HTTP_REFERER"])
                       );

$trust_root = sprintf("$scheme://%s",$_SERVER['SERVER_NAME']); #,$_SERVER['SERVER_PORT']);

// Begin the OpenID authentication process.
$auth_request = $consumer->begin($openid);

// Handle failure status return values.
if (!$auth_request) {
    if(!preg_match('/(^[=@$+!]|^http:\/\/)/',$_GET["openid_url"])) {
        $_GET["openid_url"] = 'http://idbin.ca/'.urlencode($_GET["openid_url"]);
        $openid = $_GET['openid_url'];
        $auth_request = $consumer->begin($openid);
        if(!$auth_request) {
            pageHeader(_("authentication error"));
            pageSidebar();
            makeSection(_("sorry, authentication error stage 2"));
            print '<p>Sorry, there was a general authentication error. Please go back and check your username for accuracy. It should be a valid OpenID username.</p>';
            print '<p>If you need an account, <a href="/oid.php">you can get a new OpenID here</a>.</p>';
            pageFooter();
            exit(0);
        }
    } else {
        pageHeader(_("authentication error"));
        pageSidebar();
        makeSection(_("sorry, authentication error"));
        print '<p>Sorry, there was a general authentication error. Please go back and check your username for accuracy. It should be a valid OpenID username.</p>';
        print '<p>If you need an account, <a href="/oid.php">you can get a new OpenID here</a>.</p>';
        pageFooter();
        exit(0);
    }
}

// Redirect the user to the OpenID server for authentication.  Store
// the token for this authentication so we can verify the response.
$redirect_url = $auth_request->redirectURL($trust_root,
                                           $process_url);

header("Location: ".$redirect_url);
?>
