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
### FILE: admin/boards.php
###
### This page displays the list of message boards.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');

    $page_title = 'Admin - Boards';
    include('header.php');
?>
<h2>Message Boards</h2>
<p>This page would allow you to add and edit message boards.</p>
<?=display_backlinks()?>
<?php
    $result = mysql_query("
        SELECT          board,
                        name,
                        description
        FROM            fa_board
        ORDER BY        name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Existing Message Boards</h3>
<p>These are the existing message boards. Click on the board's name for more
    details or to edit it.</p>
<dl>
<?php
        while (list($id, $name, $description) = mysql_fetch_row($result)) {
            echo '<dt><a href="board.php?board=', $id, '">', $name,
                "</a>\n<dd>$description\n";
        }

        echo "</dl>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Message Board</h3>
<p>If you want to add a new message board, enter the name and description below
    and hit Add.</p>
<form action="board.php" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" size="60"></td></tr>
<tr class="multiline"><th>Description</th><td><textarea name="description" rows="5" cols="60"></textarea></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add new Message Board"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
