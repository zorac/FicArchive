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
### FILE: admin/users.php
###
### This page allows the database of users to be searched.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');

    $page_title = 'Admin - Users';
    include('header.php');
?>
<h2>User Administration</h2>
<p>This area allows you to look up the details for a user, reset their password
    and so on.</p>
<?php
    echo display_backlinks();

    if ($_POST['username'] || $_POST['email']) {
        $where = array();
        if ($_POST['username']) $where[] = "name LIKE '%" .
            mysql_escape_string($_POST['username']) . "%'";
        if ($_POST['email']) $where[] = "email LIKE '%" .
            mysql_escape_string($_POST['email']) . "%'";

        $result = mysql_query('
            SELECT      user,
                        name,
                        email
            FROM        fa_user
            WHERE       ' . implode('
              AND       ', $where) . '
            ORDER BY    name
        ');

        if (mysql_num_rows($result) == 0) {
?>
<h3>No Users Found</h3>
<p class="error">No users were found matching your search criteria.</p>
<?php
        } else {
            echo '<h3>', mysql_num_rows($result), " Users Found</h3>\n";

            while (list($user, $name, $email) = mysql_fetch_row($result)) {
                echo '<div class="user"><a href="user.php?user=', $user,
                    '"><strong>', $name, '</strong></a> &lt;<a href="mailto:',
                    $email, '">', $email, "</a>&gt;</div>\n";
            }
        }
    } else {
?>
<h3>Look Up a User</h3>
<p>You can look up a user either by their username or the email address they
    registered with. This will search for any usernames or email addresses
    containg the text you put in (you could put '@yahoo.com' in the email box,
    for example).</p>
<form action="users.php" method="post">
<table class="login">
<tr><th>Username</th><td><input name="username"></td></tr>
<tr><th>Email Address</th><td><input name="email"></td></tr>
<tr><td colspan="2"><input type="submit" name="request" value="Look Up User"></td></tr>
</table>
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
