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
### FILE: profile/avatar.php
###
### This page allows the user to select an avatar.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('profile.php');

    $avatars = array();

    if ($dir = opendir('../avatars')) {
        while ($file = readdir($dir)) {
            if (is_file("../avatars/$file")) $avatars[$file] = 1;
        }

        closedir($dir);
    }

    if ($avatars[$_GET['avatar']] || $_GET['noavatar']) {
        $avatar = $_GET['noavatar'] ? '' : $_GET['avatar'];
        mysql_query("
            UPDATE  fa_user
            SET     avatar = '" . mysql_escape_string($avatar) . "'
            WHERE   user = " . $_SESSION['user']
        );
    } else {
        $result = mysql_query('
            SELECT  avatar
            FROM    fa_user
            WHERE   user = ' . $_SESSION['user']
        );

        if (mysql_num_rows($result) > 0) list($avatar) =
            mysql_fetch_row($result);
        mysql_free_result($result);
    }

    $page_title = 'User Profile - ' . $_SESSION['username'] . ' - Avatar';
    include('header.php');
    echo '<h2>User Profile: ', $_SESSION['username'], "</h2>\n",
        '<p><a href="./">Back to the main Profile page</a></p>', "\n",
        "<h3>Avatar</h3>\n";
?>
<p>Here you can select an avatar which will appear on your
    <a href="../profile.php">public profile page</a> and next on any review or
    <a href="../boards/">discussion board</a> postings you may make.</p>
<p><a href="avatar.php?noavatar=1">Click here if you wish to have no avatar at all.</a></p>
<table class="avatars">
<?php
    $i = 1;

    foreach (array_keys($avatars) as $file) {
        if ($i == 1) echo '<tr>';

        if ($file == $avatar) {
            echo '<td class="selected">';
        } else {
            echo '<td>';
        }

        echo '<a href="avatar.php?avatar=', $file, '"><img src="../avatars/',
            $file, '"></a></td>';

        if ($i == 4) {
            echo "</tr>\n";
            $i = 1;
        } else {
            $i++;
        }
    }

    if ($i > 1) echo "</tr>\n";
    echo "</table>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
