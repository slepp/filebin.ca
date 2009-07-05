<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

require("template.inc.php");
pageHeader(_("FileBin Account Signup"));
?>
	<div style="background:#eef;border:1px solid black;margin:1.5em;padding:1em">
	<h2>Where to Get an Account</h2>
<?php
print '<p>'._("The FileBin uses <a href=\"http://openid.net/\">OpenID</a> accounts for authentication. You can use any regular OpenID, LID or i-Name to login.")."</p>";
print '<p>'._("If you do not already have an OpenID, you may:")."</p>";
?>
<ul>
<li>Use a free <a href="http://idbin.ca/?action=register">IDbin provided OpenID</a>. You may use IDbin usernames with many other OpenID enabled websites as well.</li>
<li>Use a free, public OpenID service, such as <a href="https://myvidoop.com/register/affiliate/15">Vidoop</a>, <a href="https://www.myopenid.com/affiliate_signup?affiliate_id=812&amp;openid.sreg.optional=fullname,nickname,language,country,timezone,email">MyOpenID</a>, or <a href="http://openid.net/wiki/index.php/Public_OpenID_providers">any other OpenID provider</a>.</li>
<li>Use a registered <a href="http://inames.net/register.html">i-Name</a> (<a href="http://idbin.ca/?action=register">IDbin provides a free <b>@bin</b> i-Name</a>).</li>
</ul>
<?php
# print '<p>'._("If you have more than one OpenID, it is possible to associate all of your OpenIDs to a single Pastebin account. Simply <a href=\"/login.php\">login with one of your OpenIDs</a>, go to your settings page, and you will be able to associate other identifiers.").'</p>';

?>
	<h2>Simple Account Creation</h2>
	<p>If you read the above and don't really care, go ahead and just fill in the fields below to get an account with the FileBin:</p>
	<form method="post" action="http://idbin.ca/" class="generalform">
	<fieldset><legend>new account signup</legend>
	<input type="hidden" name="action" value="register" />
	<input type="hidden" name="return_to" value="http://<?php print $_SERVER["SERVER_NAME"]?>/oid_login.php" />
	<table>
	  <tr>
	    <td align="right">Your Username:</td>
	    <td><input type="text" name="username" value="" /></td>
	  </tr>
	  <tr>
	    <td align="right">Password:</td>
	    <td><input type="password" name="pass1" value="" /></td>
	  </tr>
	  <tr>
	    <td align="right">Confirm Password:</td>
	    <td><input type="password" name="pass2" value="" /></td>
	  </tr>
	  <tr>
	    <td></td><td><img class="captcha" src="http://idbin.ca/?action=captcha" /><br/>
	    Please enter the text in the image exactly as shown.
	  </td>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td><input type="text" name="captcha_text" value="" />
	    </td>
	  </tr>
	</table>
	<input type="submit" value="Sign Up" name="save_profile" />
	<p><i>Note:	After submission, if the account is properly created, you will be automatically logged in.</i></p>
	</fieldset>
	</form>
	</div>
<?php
pageFooter();
?>
