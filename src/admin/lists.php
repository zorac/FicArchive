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
### FILE: admin/lists.php
###
### This page displays the list of mailing lists and allows updates to them.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('alert.php');

    $page_title = 'Admin - Mailing Lists';
    include('header.php');

    if ($_POST['newlist'] && $_POST['username']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'list'
            WHERE   type = 'user'
              AND   name = '" . mysql_escape_string($_POST['username']) .
        "'");

        if (!mysql_affected_rows()) {
            $error = 'Add new Mailing List failed.';
        }
    } elseif ($_REQUEST['touser']) {
        mysql_query("
            UPDATE  fa_user
            SET     type = 'user'
            WHERE   type = 'list'
              AND   user = " . (int)$_REQUEST['touser']
        );

        if (!mysql_affected_rows()) {
            $error = 'Reversion to Standard User failed.';
        }
    }
?>
<h2>Mailing Lists</h2>
<p>Mailing lists are users which have been set up specifically to send alerts
    to an external mailing list. Such alerts will not have the usual
    unsubscribe details attached as they would not be relevant.</p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . "</p>\n") : '' )?>
<h3>Existing Mailing Lists</h3>
<?php
    $result = mysql_query("
        SELECT      user,
                    name,
                    email
        FROM        fa_user
        WHERE       type = 'list'
        ORDER BY    name
    ");

    while (list($user, $name, $email) = mysql_fetch_row($result)) {
        echo '<div class="user"><a href="user.php?user=', $user,
            '"><strong>', $name, '</strong></a> &lt;<a href="mailto:', $email,
            '">', $email, '</a>&gt; [ <a href="lists.php?touser=', $user,
            '">Revert to Ordinary User</a> ]';

        foreach (display_subscriptions($user) as $subscription) {
            echo '<div class="subscription">', $subscription[2], "</div>\n";
        }

        echo '</div>';
    }
?>
<h3>Add New Mailing List</h3>
<p>To convert an existing user into a mailing list, enter the username below
    and hit the Convert button.</p>
<form action="lists.php" method="post">User: <input name="username"> <input type="submit" name="newlist" value="Convert"></form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
