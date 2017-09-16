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
### FILE: admin/admins.php
###
### This page displays the list of administrators and moderators, and allows
### the addition and removal of those statuses.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');

    if (!fa_has_admin_access()) admin_error('Access Denied',
        'This page is only available to administrators.');

    $page_title = 'Admin - Mods/Admins';
    include('header.php');

    if ($_POST['newmod'] && $_POST['username']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'mod'
            WHERE   (type = 'user'
                OR   type = 'verified')
              AND   name = '" . mysql_escape_string($_POST['username']) .
        "'");

        if (!mysql_affected_rows()) {
            $error = 'Add new Moderator failed.';
        }
    } elseif ($_REQUEST['touser']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'user'
            WHERE   type = 'mod'
              AND   user = " . (int)$_REQUEST['touser']
        );

        if (!mysql_affected_rows()) {
            $error = 'Demotion to Standard User failed.';
        }
    } elseif ($_REQUEST['tomod']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'mod'
            WHERE   user = " . (int)$_REQUEST['tomod']
        );

        if (!mysql_affected_rows()) {
            $error = 'Change to Moderator failed.';
        }
    } elseif ($_REQUEST['toadmin']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'admin'
            WHERE   type = 'mod'
              AND   user = " . (int)$_REQUEST['toadmin']
        );

        if (!mysql_affected_rows()) {
            $error = 'Promotion to Administrator failed.';
        }
    }
?>
<h2>Moderators and Administrators</h2>
<p>This page lists all users with administrator or moderator access (the only
    difference between the two being that moderators cannot view this page, and
    cannot view/change the account information of other moderators or
    administrators).</p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . "</p>\n") : '' )?>
<h3>Current Administrators</h3>
<?php
    $result = mysql_query("
        SELECT      user,
                    name
        FROM        fa_user
        WHERE       type = 'admin'
        ORDER BY    name
    ");

    while (list($user, $name) = mysql_fetch_row($result)) {
        echo '<div class="user"><a href="user.php?username=', $name,
            '"><strong>', $name, '</strong></a> [ <a href="admins.php?tomod=',
            $user, '">demote to moderator</a> ]</div>', "\n";
    }
?>
<h3>Existing Moderators</h3>
<?php
    $result = mysql_query("
        SELECT      user,
                    name
        FROM        fa_user
        WHERE       type = 'mod'
        ORDER BY    name
    ");

    while (list($user, $name) = mysql_fetch_row($result)) {
        echo '<div class="user"><a href="user.php?user=', $user, '"><strong>',
            $name, '</strong></a> [ <a href="admins.php?touser=', $user,
            '">demote to standard user</a> | <a href="admins.php?toadmin=',
            $user, '">promote to administrator</a> ]</div>', "\n";
    }
?>
<h3>Add New Moderator</h3>
<p>To promote an existing user to a moderator, enter the username below and hit
    the Promote button.</p>
<form action="admins.php" method="post">User: <input name="username"> <input type="submit" name="newmod" value="Promote"></form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
