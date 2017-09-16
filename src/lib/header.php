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
### FILE:       lib/header.php
###
### This file contains a header to include on all pages.
###############################################################################
###############################################################################
    require_once('config.php');
    require_once('init.php');
    require_once('date.php');

    if (!$fa_done_header) {
        $fa_done_header = 1;

        if ($search_page == 'boards') {
            $fa_search_page = $fa_server_path . '/boards/search.php';
        } else {
            $fa_search_page = $fa_server_path . '/search.php';
        }

        if ($search_params) $fa_search_page .= '?' . $search_params;
?>
<html>
<head>
<title><?=$page_title ? "$fa_site_name - $page_title" : $fa_site_name?></title>
<?php
        $session_theme = $_SESSION['options']['theme'];
        if (!$session_theme || !array_key_exists($session_theme, $fa_themes))
            $session_theme = key($fa_themes);
        echo '<link rel="stylesheet" title="', $session_theme, '" href="',
            $fa_server_path, '/', $fa_themes[$session_theme]['file'], '">',
            "\n";

        foreach ($fa_themes as $theme_name => $theme) {
            if ($theme_name != $session_theme)
                echo '<link rel="alternate stylesheet" title="', $theme_name,
                '" href="', $fa_server_path, '/', $theme['file'], '">', "\n";
        }

        echo $add_to_head;
?>
</head>
<body>
<div id="fa_header" class="inverse">
<h1><?=$fa_site_name?></h1>
<?php
        if ($_SESSION['user']) {
            echo '<p>Logged in as user <strong><a href="', $fa_server_path,
                '/profile/">', $_SESSION['username'], '</a></strong>';

            if (fa_has_admin_access()) {
                echo ' - You are an <strong><a href="', $fa_server_path,
                    '/admin/">Administrator</a></strong>';
            } elseif (fa_has_mod_access()) {
                echo ' - You are a <strong><a href="', $fa_server_path,
                    '/admin/">Moderator</a></strong>';
            } elseif ($_SESSION['type'] == 'list') {
                echo ' - This user is a <strong><a href="', $fa_server_path,
                    '/alerts.php">mailing list</a></strong>';
            }

            $date_user = explode('-', $_SESSION['dob']);
            $date_local = localtime();

            if (($date_user[1] == ($date_local[4] + 1))
                    && ($date_user[2] == $date_local[3])) {
                echo ' - We wish you a <strong>Happy Birthday!</strong>';
            }

            echo "</p>\n";
        } elseif (!$fa_no_login) {
            preg_match('/^(.*?)(&?logout=1)?$/', $_SERVER['QUERY_STRING'],
                $matches);
            $query = str_replace('&', '&amp;', $matches[1]);
            $query = $query ? "?$query" : '';
            echo '<p>';
            if ($fa_login_failed) echo '<strong>Your login failed - please ',
                'check your username and password and try again.</strong><br>';
            echo '<form action="', $_SERVER['PHP_SELF'], $query,
                '" method="post">Username: <input name="username" value="',
                $_REQUEST['username'], '"> Password: <input type="password" ',
                'name="password"> <input class="submit" name="login" ',
                'type="submit" value="Log In"></form></p>';
        }
?>
<p>
<a href="<?=$fa_server_path?>/">Home</a> |
Stories by <a href="<?=$fa_server_path?>/byauthor.php">Author</a>,
<a href="<?=$fa_server_path?>/bytitle.php">Title</a> or
<a href="<?=$fa_server_path?>/bydate.php">Date</a> |
<a href="<?=$fa_search_page?>">Search</a> |
<a href="<?=$fa_server_path?>/boards/board.php?board=1">Discussion Boards</a>
<?php
    if ($_SESSION['user']) {
        echo '| <a href="', $fa_server_path, '/profile/">Profile</a> |',
            "\n", '<a href="', $fa_server_path, '/profile/bookmarks.php">',
            "Bookmarks</a>\n";
        if (fa_has_mod_access()) echo ' | <a href="', $fa_server_path,
            '/admin/">Admin</a>';
    } else {
        echo '| <a href="', $fa_server_path,
            '/profile/register.php">Register</a>', "\n";
    }
?>
</p>
</div>
<?php
        if ($fa_login_failed && !$fa_no_login && $fa_login_fail_text)
            echo '<p class="warning">', $fa_login_fail_text, "</p>\n";
    }
?>
