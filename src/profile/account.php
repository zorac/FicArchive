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
### FILE: profile/account.php
###
### This page displays basic account details and allows email/password changes.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('date.php');
    require_once('config.php');
    require_once('init.php');
    require_once('login.php');
    require_once('profile.php');

    $page_title = 'User Profile - ' . $_SESSION['username'] . ' - Account';
    include('header.php');
    echo '<h2>User Profile: ', $_SESSION['username'], "</h2>\n",
        '<p><a href="./">Back to the main Profile page</a></p>', "\n",
        "<h3>Account Details</h3>\n";

    if ($_POST['update']) {
        $query = array();

        if ($_POST['newpass1'] && ($_POST['newpass1'] != $_POST['password'])) {
            $query[] = "password = '" .
                mysql_escape_string($_POST['newpass1']) . "'";
        }

        if ($_POST['email'] && ($_POST['email'] != $_SESSION['email'])) {
            $query[] = "email = '" . mysql_escape_string($_POST['email'])
                . "'";
            $verify = create_verification_code($_SESSION['username']);
            $query[] = "verify = '$verify'";
            if ($_SESSION['type'] == 'verified') $query[] = "type = 'user'";
        }

        if (($_POST['newpass1'] || $_POST['newpass2'])
                && ($_POST['newpass1'] != $_POST['newpass2'])) {
            echo '<p class="error">New passwords do not match.</p>', "\n";
        } elseif ($_POST['email'] && !preg_match($fa_regex_email,
                $_POST['email'])) {
            echo '<p class="error">New email address is invalid.</p>', "\n";
        } elseif ($query) {
            mysql_query('
                UPDATE  fa_user
                SET     ' . implode(',
                        ', $query) . '
                WHERE   user = ' . $_SESSION['user'] . "
                  AND   password = '" . $_POST['password'] ."'
            ");

            if (mysql_affected_rows() == 1) {
                echo '<p class="notice">Your account details have been ',
                    "updated.</p>\n";

                if ($verify) {
                    $_SESSION['email'] = $_POST['email'];
                    send_verification_code($_SESSION['email'],
                        $_SESSION['username'], '', $verify);
                }
            } else {
                echo '<p class="error">The password you entered is incorrect.',
                    "</p>\n";
            }
        }
    }
?>
<p>If you wish to change your email address or password, you need to enter your
    existing password. A new password should be entered twice to ensure
    correctness.<?=(fa_is_verified() ? ' If you change your email address, you will need to re-verify your account.' : '')?>
    Under normal circumstances, your username and date of birth may not be
    changed - if you need to do so, please
    <a href="mailto:<?=$fa_email_address?>">contact the moderators</a>.</p>
<form method="post" action="account.php">
<table class="info">
<tr><th>Username</th><td><?=$_SESSION['username']?></td></tr>
<tr><th>Date of Birth</th><td><?=long_date($_SESSION['dob'], TRUE)?></td></tr>
<tr><th>Email Address</th><td><input name="email" value="<?=$_SESSION['email']?>" size="30"></td></tr>
<tr><th>Current Password</th><td><input type="password" name="password"></td></tr>
<tr><th>New Password</th><td><input type="password" name="newpass1"></td></tr>
<tr><th>Repeat Password</th><td><input type="password" name="newpass2"></td></tr>
<tr><td class="checkbox"><input type="checkbox" name="verify"<?=(fa_is_verified() ? ' checked' : '')?>></td><td>Send me a new verification code if my email address changes.</td></tr>
<tr><td></td><td><input type="submit" name="update" value="Change password or email address"></td></tr>
<?php
    if ($fa_privacy_url) echo '<tr><td></td><td><a href="', $fa_privacy_url,
        '">View our Privacy Policy</a></td></tr>', "\n";
    echo "</table>\n</form>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
