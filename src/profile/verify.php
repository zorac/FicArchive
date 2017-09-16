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
### FILE: profile/verify.php
###
### This page displays handles email address verification requests.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('login.php');

    $fa_no_login = TRUE;
    $username = ($_POST['username'] ? $_POST['username'] : $_GET['username']);

    if ($username && $_REQUEST['code']) {
        $result = mysql_query("
            SELECT  user,
                    type,
                    verify
            FROM    fa_user
            WHERE   name = '" . mysql_escape_string($username) . "'
        ");

        if (mysql_num_rows($result) == 1) {
            list($user, $type, $verify) = mysql_fetch_row($result);
            mysql_free_result($result);

            if ($type != 'user') {
                $page_title = 'Account Verification';
                include('header.php');
                echo "<h2>Account Already Verified</h2>\n";
            } elseif ($verify = $_REQUEST['code']) {
                $_SESSION['user'] = $user;

                mysql_query("
                    UPDATE  fa_user
                    SET     type = 'verified',
                            verify = ''
                    WHERE   user = $user
                ");

                fa_get_user('user');
                $page_title = 'Verification succeeded';
                include('header.php');
                echo "<h2>Verification Succeeded</h2>\n";
            } else {
                $error = TRUE;
            }

            if (!$error) {
?>
<p>Your email address has been successfully verified, allowing you full access
    to <?=$fa_site_name?>. You can <a href="./">view your profile</a> and
    customize your account, or you could jump right in and
    <a href="../search.php">search</a> or
    <a href="../byauthor.php">browse</a> for stories.</p>
<?php
                include('footer.php');
                exit(0);
            }
        } else {
            $error = TRUE;
        }
    }

    $page_title = 'Account Verification';
    include('header.php');
?>
<h2>Account Verification</h2>
<p>This page allows you to verify the email address on your <?=$fa_site_name?>
    account. You must have a verified email address before you can post to the
    review and discussion boards, for example.</p>
<?php
    if ($_SESSION['user']) {
        fa_get_user('user');

        if ($_SESSION['email']) {
            if ($_GET['send']) {
                $verify = create_verification_code($_SESSION['username']);

                mysql_query("
                    UPDATE  fa_user
                    SET     verify = '$verify'
                    WHERE   user = " . $_SESSION['user']
                );

                send_verification_code($_SESSION['email'],
                    $_SESSION['username'], '', $verify);

                echo '<p class="notice">A verification code has been sent to ',
                    "your email address.</p>\n";
            } else {
                echo '<p>If you don\'t have a registration email with your ',
                    'verification code, we can <a href="verify.php?send=',
                    'email">', "send you a new one</a>.</p>\n";
            }
        }
    }

    if ($error) echo '<p class="error">The username and verification code you '
        . ' supplied do not match our records. Please check the details and '
        . "try again.</p>\n";
?>
<form method="post" action="verify.php">
<table class="login">
<tr><th>Username</th><td><input name="username" value="<?=($_SESSION['username'] ? $_SESSION['username'] : $_REQUEST['username'])?>" size="40"></td></tr>
<tr><th>Verification Code</th><td><input name="code" value="<?=$_REQUEST['code']?>" size="40"></td></tr>
<tr><td></td><td><input type="submit" value="Verify Account">
</table>
</form>
<?php
    include('footer.php');
?>
