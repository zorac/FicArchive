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
### FILE: admin/board.php
###
### This page displays the details of a message board and allows those details
### to be updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');
    require_once('numbers.php');

    $backlinks[] = array('Message Board Admin', 'boards.php');

    if ($_POST['add']) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $threads = 0;
        $comments = 0;

        if (!$name) admin_error('No Name Supplied',
            'You must supply a name to create a new message board.');

        $result = mysql_query("
            INSERT INTO fa_board
                        (name, description)
            VALUES      ('" . mysql_escape_string($name) . "', '"
                            . mysql_escape_string($description) . "')
        ");

        if ($result && (mysql_affected_rows() == 1)) {
            $board = mysql_insert_id();
        } else {
            $result = mysql_query("
                SELECT  board
                FROM    fa_board
                WHERE   name = '" . mysql_escape_string($name) . "'
            ");

            list($board) = mysql_fetch_row($result);
            mysql_free_result($result);

            admin_error('Message Board Already Exists', '<a href="' .
                $_SERVER['PHP_SELF'] . '?board=' . $board .
                '">A message board with that name already exists.</a>');
        }
    } else {
        $result = mysql_query('
            SELECT  board,
                    name,
                    description
            FROM    fa_board
            WHERE   fa_board.board = ' . (int)$_REQUEST['board']
        );

        if (list($board, $name, $description) = mysql_fetch_row($result)) {
            mysql_free_result($result);
            $result = mysql_query("
                SELECT  count(thread)
                FROM    fa_thread
                WHERE   board = $board
            ");

            list($threads) = mysql_fetch_row($result);
            mysql_free_result($result);
            $result = mysql_query("
                SELECT          count(fa_comment.comment)
                FROM            fa_comment
                  INNER JOIN    fa_thread
                    ON          fa_thread.thread = fa_comment.id
                      AND       fa_thread.board = $board
                WHERE           fa_comment.type = 'thread'
            ");

            list($comments) = mysql_fetch_row($result);
            mysql_free_result($result);
        } else {
            admin_error('Message Board Not Found',
                'The message board you requested does not exist.');
        }

        if ($_POST['update']) {
            $new_name = trim($_POST['name']);
            $new_description = trim($_POST['description']);

            if ($new_name) {
                $result = mysql_query("
                    UPDATE  fa_board
                    SET     name = '" . mysql_escape_string($new_name)
                            . "', description = '"
                            . mysql_escape_string($new_description) . "'
                    WHERE   board = $board
                ");

                if ($result) {
                    $name = $new_name;
                    $description = $new_description;
                } else {
                    $result = mysql_query("
                        SELECT  board
                        FROM    fa_board
                        WHERE   name = '" . mysql_escape_string($new_name) . "'
                    ");

                    list($new_board) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    $error = '<a href="' . $_SERVER['PHP_SELF'] . '?board='
                        .  $new_board . '">A message board with the name '
                        .  $new_name . ' already exists.</a>';
                }
            } else {
                $error = "No new name was supplied.";
            }
        } elseif ($_POST['remove']) {
            if ($threads == 0) {
                mysql_query("
                    DELETE FROM fa_board
                    WHERE       board = $board
                ");

                $page_title = 'Admin - Message Board Removed';
                include('header.php');
                echo "<h2>Message Board Removed</h2>\nThe message board ",
                    "'$name' was successfully removed.\n", display_backlinks();
                include('footer.php');
                exit(0);
            } else {
                $error = 'This message board cannot be removed as it contains '
                    . $threads . ' threads.';
            }
        }
    }

    $page_title = "Admin - Message Board - $name";
    include('header.php');
?>
<h2>Message Board Info: <?=$name?></h2>
<p><a href="../boards/board.php?board=<?=$board?>">View this message board</a></p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . '</p>') : '')?>
<h3>Update Message Board Details</h3>
<p>Please avoid changing the names of message boards which are in use (other
    than to correct typographical or spelling errors).</p>
<form action="board.php?board=<?=$board?>" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" value="<?=$name?>" size="60"></td></tr>
<tr class="multiline"><th>Description</th><td><textarea name="description" rows="5" cols="60"><?=$description?></textarea></td></tr>
<tr><th>Threads</th><td><?=nice_count($threads)?></td></tr>
<tr><th>Comments</th><td><?=nice_count($comments)?></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update this Message Board"></td></tr>
</table>
</form>
<h3>Remove this Message Board</h3>
<p>You can only remove a message board if there are no threads in it.</p>
<?php
    if ($threads == 0) {
?>
<form action="board.php?board=<?=$board?>" method="post">
<input type="submit" name="remove" value="Remove this Message Board">
</table>
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
