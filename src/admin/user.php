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
### FILE: admin/user.php
###
### This page displays the details of a user and allows certain changes to be
### made.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('alert.php');
    require_once('date.php');

    $result = mysql_query('
        SELECT  user,
                name,
                email,
                password,
                dob,
                type
        FROM    fa_user
        WHERE   user = ' . (int)$_REQUEST['user']
    );

    if (list($user, $name, $email, $password, $dob, $type) =
            mysql_fetch_row($result)) {
        if (($type == 'admin') || (($type == 'mod') && !fa_has_admin_access()))
            admin_error('Access Denied',
                "You do not have access to view this user's details.");

        $page_title = 'Admin - User - ' . $name;
        include('header.php');
?>
<h2>User Profile: <?=$name?></h2>
<?=display_backlinks()?>
<h3>User Details</h3>
<?php
        if ($_POST['password']) {
            $password = $_POST['password'];
            mysql_query("
                UPDATE  fa_user
                SET     password = '" . mysql_escape_string($password) . "'
                WHERE   user = $user
            ");
            echo '<p class="notice">The password for this user has been ',
                "changed.</p>\n";
        }

        if ($_GET['verify'] && ($type == 'user')) {
            mysql_query("
                UPDATE  fa_user
                SET     type = 'verified'
                WHERE   user = $user
            ");
            $type = 'verified';
            echo '<p class="notice">This user has now been flagged as ',
                "verified.</p>\n";
        }

        if (fa_has_admin_access()) {
            if ($_GET['disable'] && ($type != 'disabled')) {
                mysql_query("
                    UPDATE  fa_user
                    SET     type = 'disabled'
                    WHERE   user = $user
                ");
                $type = 'disabled';
                echo '<p class="notice">This user has now been disabled.</p>',
                    "\n";
            } if ($_GET['enable'] && ($type == 'disabled')) {
                mysql_query("
                    UPDATE  fa_user
                    SET     type = 'user'
                    WHERE   user = $user
                ");
                $type = 'user';
                echo '<p class="notice">This user has now been re-enabled.',
                    "</p>\n";
            }
        }

        if ($_GET['reminder'])
            send_password_reminder($email, $name, $password);
        if ($type == 'mod')
            echo "<p>This user is a <strong>moderator</strong></p>\n";
        if ($type == 'list')
            echo "<p>This user is a <strong>mailing list</strong></p>\n";
        echo '<p><a href="user.php?user=', $user, '&amp;reminder=1">Send a ',
            'password reminder email.</a>', "<br/>\n";

        if (fa_has_admin_access()) {
            if ($type == 'disabled') {
                echo '<a href="user.php?user=', $user, '&amp;enable=1">',
                    "Re-enable this user.</a><br/>\n";
            } else {
                echo '<a href="user.php?user=', $user, '&amp;disable=1">',
                    "Disable this user.</a><br/>\n";
            }
        }

        if ($type == 'user') {
            echo '<a href="user.php?user=', $user, '&amp;verify=1">Flag this ',
                'user as being verified.</a>';
        } elseif ($type == 'disabled') {
            echo 'This user is disabled.';
        } else {
            echo 'This user is verified.';
        }
?>
</p>
<table class="info">
<tr><th>Username</th><td><?=$name?></td></tr>
<tr><th>Email Address</th><td><a href="mailto:<?=$email?>"><?=$email?></a></td></tr>
<tr><th>Date of Birth</th><td><?=long_date($dob, TRUE)?></td></tr>
<tr><th>Password</th><td><?=$password?></td></tr>
</table>
<h3>Password Change</h3>
<p>If you need to update the password for a user, you can do so here.</p>
<form method="POST" action="user.php?user=<?=$user?>">
<input name="password" size="20">
<input type="submit" value="Change Password">
</form>
<?php
        $subscriptions = display_subscriptions($user);

        if ($subscriptions) {
            echo "<h3>Email Alerts</h3>\n";

            if ($_GET['cancel']) {
                mysql_query("
                    DELETE FROM fa_subscription
                    WHERE       user = $user
                ");

                echo '<p>All email alerts for this user have been cancelled.',
                    "</p>\n";
            } else {
                echo '<p>This user is subscribed to the following email ',
                    "alerts:</p>\n";

                foreach ($subscriptions as $subscription) {
                    echo '<div class="subscription">', $subscription[2],
                        "</div>\n";
                }

                echo '<p><a href="user.php?user=', $user, '&amp;cancel=1">',
                    "Cancel all email alerts.</a></p>\n";
            }
        }

        include('footer.php');
    } else {
        admin_error('User Not Found',
            'No user was found matching the supplied ID.');
    }
###############################################################################
###############################################################################
?>
