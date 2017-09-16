<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2004,2005 Mark Rigby-Jones <mark@rigby-jones.net>
###
### This program is free software; you can redistribute it and/or
### modify it under the terms of the GNU General Public License
### as published by the Free Software Foundation; either version 2
### of the License, or (at your option) any later version.
###
### This program is distributed in the hope that it will be useful,
### but WITHOUT ANY WARRANTY; without even the implied warranty of
### MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
### GNU General Public License for more details.
###
### You should have received a copy of the GNU General Public License
### along with this program; if not, write to the Free Software
### Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
###############################################################################
###############################################################################
### FILE:       lib/login.php
###
### This file provides handy functions for working with logins.
###############################################################################
###############################################################################
    require_once('init.php');

###############################################################################
# FUNCTION:     print_login_form
#
# ARGS: action  Optional self-URL
#
# RETURNS:      Nothing
#
# This function displays a login form.
###############################################################################
    function print_login_form($action) {
?>
<form action="<?=($action ? $action : $_SERVER['PHP_SELF'])?>" method="post">
<table class="login">
<tr><th>Username</th><td><input name="username" value="<?=$_REQUEST['username']?>"></td></tr>
<tr><th>Password</th><td><input type="password" name="password"></td></tr>
<tr><td colspan="2"><input type="submit" name="login" value="Log In"></td></tr>
</table>
</form>
<?php
    }

###############################################################################
# FUNCTION:     print_request_form
#
# ARGS:         None
#
# RETURNS:      Nothing
#
# This function displays a password reminder request form.
###############################################################################
    function print_request_form() {
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
<table class="login">
<tr><th>Username</th><td><input name="username" value="<?=($_SESSION['user'] ? $_SESSION['username'] : $_REQUEST['username'])?>"></td></tr>
<tr><th>Email Address</th><td><input name="email" value="<?=$_SESSION['email']?>"></td></tr>
<tr><td colspan="2"><input type="submit" name="request" value="Send Password Request"></td></tr>
</table>
</form>
<?php
    }

###############################################################################
# FUNCTION:     send_password_reminder
#
# ARGS: email   EMail addess to send to
#       username The username
#       password The password
#
# RETURNS:      Nothing
#
# This function sends a password reminder email.
###############################################################################
    function send_password_reminder($email, $username, $password) {
        global $fa_site_name, $fa_server_secure, $fa_server_name,
            $fa_server_path, $fa_email_name, $fa_email_address;

        mail($email, "$fa_site_name password reminder",
"Somebody (presumably you) requested a username and password reminder for
your account on $fa_site_name.

The details are as follows:
Username: $username
Password: $password

You can log on to the site at the following URL:
http" . ($fa_server_secure ? 's' : '') . '://' . $fa_server_name
            . $fa_server_path . "/login.php\n",
            "From: $fa_email_name <$fa_email_address>");

        echo '<p class="notice">A password reminder has been sent to your ',
            "email address.</p>\n";
    }

###############################################################################
# FUNCTION:     create_verification_code
#
# ARGS: username The username
#
# RETURNS:      A verification code
#
# This function generates a verification code.
###############################################################################
    function create_verification_code($username) {
        return md5($username . ' ' . microtime() . ' ' . mt_rand() . ' ' .
            rand());
    }

###############################################################################
# FUNCTION:     send_verification_code
#
# ARGS: email   EMail addess to send to
#       username The username
#       password The password (optional)
#       code    The verification code
#
# RETURNS:      Nothing
#
# This function sends an email with a verification code.
###############################################################################
    function send_verification_code($email, $username, $password, $code) {
        global $fa_site_name, $fa_server_secure, $fa_server_name,
            $fa_server_path, $fa_email_name, $fa_email_address;

        $verify = "To verify your email address, please follow the link below:
http" . ($fa_server_secure ? 's' : '') . '://' . $fa_server_name
    . $fa_server_path . "/profile/verify.php?username=$username&code=$code

You can also log on to the site, visit your profile page and enter the code
there. Your verification code is: $code\n";

        if ($password) {
            mail($email, "$fa_site_name registration confirmation",
"Congratulations! You have successfuly registered for a new account on
$fa_site_name.

Your account details are as follows:
Username: $username
Password: $password

$verify", "From: $fa_email_name <$fa_email_address>");
        } else {
            mail($email, "$fa_site_name verification code",
"Somebody (presumably you) requested a verification code for your account on
$fa_site_name.

$verify", "From: $fa_email_name <$fa_email_address>");
        }
    }
###############################################################################
###############################################################################
?>
