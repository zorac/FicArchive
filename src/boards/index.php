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
### FILE: boards/index.php
###
### This page displays the list of discussion boards.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');

    $search_page = 'boards';
    $search_params = "type=thread";
    $page_title = 'Discussion Boards';
    include('header.php');
?>
<h2>Discussion Boards</h2>
<p>Welcome to the <?=$fa_site_name?> discussion boards! Click on the board name
    to view the current threads on that board.</p>
<?php
    $result = mysql_query('
        SELECT      board,
                    name,
                    description
        FROM        fa_board
        ORDER BY    name
    ');

    if (mysql_num_rows($result) > 0) {
        echo "<dl>\n";

        while (list($id, $name, $description) = mysql_fetch_row($result)) {
            echo '<dt><a href="board.php?board=', $id, '">', $name,
                "</a></dt>\n<dd>$description</dd>\n";
        }

        echo "</dl>\n";
    } else {
        echo '<p class="warning">No discussion boards have been set up on ',
            "this site.</p>\n";
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
