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
### FILE: profile/search_bookmarks.php
###
### This page displays a list of all saved searches.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('profile.php');
    require_once('search.php');

    $page_title = "Bookmarks - Searches";
    include('header.php');

    echo "<h2>Saved Searches</h2>\n";

    $user = $_SESSION['user'];

    $result = mysql_query("
        SELECT      search,
                    name,
                    query
        FROM        fa_bookmark_search
        WHERE       user = $user
        ORDER BY    name
    ");

    if (mysql_num_rows($result) == 0) {
        echo '<p class="warning">You have no saved searches.</p>', "\n";
    } else {
        echo "<dl>\n";

        while (list($search, $name, $query) = mysql_fetch_row($result)) {
            echo '<dt>', $name,' [<a href="../search.php?saved=', $search,
                '"> Search </a>|<a href="../search.php?saved=', $search,
                '&amp;edit=1"> Edit </a>]', "\n<dd>",
                display_search(unserialize($query));
        }

        echo "</dl>\n";
    }

    mysql_free_result($result);
    include('footer.php');
###############################################################################
###############################################################################
?>
