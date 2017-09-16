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
### FILE: boards/board.php
###
### This page displays a list of threads on a message board.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('comments.php');

    $search_page = 'boards';
    $board = (int)$_GET['board'];

    if ($board > 0) {
        $result = mysql_query("
            SELECT  name,
                    description
            FROM    fa_board
            WHERE   board = $board
        ");

        if (mysql_num_rows($result) == 1) {
            list($name, $description) = mysql_fetch_row($result);
            $search_params = "board=$board";
            $page_title = "Discussions - $name";

            include('header.php');
            echo "<h2>$name</h2>\n<p>$description</p>\n";
            echo '<p><a href="./">Back to the list of discussion boards.</a>',
                "</p>\n";
        }

        mysql_free_result($result);
    }

    if (!$name) fa_error('Discussion Board Not Found', 'No discussion board '
        . 'could be found matching the parameters you supplied.');
    do_threads($board, "board.php?board=$board", "thread.php?");
    include('footer.php');
###############################################################################
###############################################################################
?>
