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
### FILE: boards/thread.php
###
### This page displays the contents of a discussion board thread.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('comments.php');

    $search_page = 'boards';
    $thread = (int)$_GET['thread'];
    $moveto = (int)$_POST['moveto'];

    if ($thread > 0) {
        if (fa_has_mod_access() && ($moveto > 0)) {
            $result = mysql_query("
                SELECT  board
                FROM    fa_board
                WHERE   board = $moveto
            ");

            if (mysql_num_rows($result) == 0) {
                fa_error('Board not found', 'You are trying to move a thread '
                    . 'to a board which does not exist.');
            } else {
                mysql_free_result($result);
                mysql_query("
                    UPDATE      fa_thread
                    SET         board = $moveto
                    WHERE       thread = $thread
                ") or die(mysql_error());
            }
        }

        $result = mysql_query("
            SELECT          fa_board.board,
                            fa_board.name,
                            fa_user.user,
                            fa_user.name,
                            fa_user.avatar,
                            INET_NTOA(fa_thread.ipaddr),
                            fa_thread.time,
                            fa_thread.deleted,
                            fa_thread.sticky,
                            fa_thread.subject,
                            fa_thread.body
            FROM            fa_thread
              INNER JOIN    fa_board
                ON          fa_board.board = fa_thread.board
                  AND       fa_board.board > 0
              INNER JOIN    fa_user
                ON          fa_user.user = fa_thread.user
            WHERE           fa_thread.thread = $thread
        ");

        if (mysql_num_rows($result) == 1) {
            list($board, $name, $user, $username, $avatar, $ipaddr, $time,
                $deleted, $sticky, $subject, $body) = mysql_fetch_row($result);

            if (fa_has_mod_access()) {
                if ($_REQUEST['deletethread'] && !$deleted) {
                    mysql_query("
                        UPDATE  fa_thread
                        SET     deleted = 1,
                                sticky = 0
                        WHERE   thread = $thread
                    ");

                    mysql_query("
                        UPDATE  fa_comment
                        SET     deleted = (deleted | 4)
                        WHERE   type = 'thread'
                          AND   id = $thread
                    ") or die(mysql_error());

                    $deleted = 1;
                    $sticky = 0;
                } elseif ($_REQUEST['undeletethread'] && $deleted) {
                    mysql_query("
                        UPDATE  fa_thread
                        SET     deleted = 0
                        WHERE   thread = $thread
                    ");

                    mysql_query("
                        UPDATE  fa_comment
                        SET     deleted = (deleted & ~4)
                        WHERE   type = 'thread'
                          AND   id = $thread
                    ");

                    $deleted = 0;
                }
            }

            if (fa_has_admin_access()) {
                if ($_REQUEST['stickythread'] && !$sticky) {
                    mysql_query("
                        UPDATE  fa_thread
                        SET     sticky = 1
                        WHERE   thread = $thread
                    ");

                    $sticky = 1;
                } elseif ($_REQUEST['unstickythread'] && $sticky) {
                    mysql_query("
                        UPDATE  fa_thread
                        SET     sticky = 0
                        WHERE   thread = $thread
                    ");

                    $sticky = 0;
                }
            }

            if (!$deleted || fa_has_mod_access()) {
                $search_params = "thread=$thread";
                $page_title = "Discussions - $name";

                include('header.php');
                echo '<h2><a href="board.php?board=', $board, '">',
                    $name, "</a></h2>\n";
                if ($avatar) echo '<img class="avatar" src="../avatars/',
                    $avatar, '">', "\n";
                echo '<h3><a href="../profile.php?user=', $user, '">',
                    $username, '</a>';
                if (($ipaddr != '0.0.0.0') && fa_has_mod_access()) echo
                    " <small>(IP address $ipaddr)</small>";
                echo ': ', $subject, "</h3>\n";
                if ($body) echo "<p>$body</p>\n";
            }
        }

        mysql_free_result($result);
    }

    $action = "thread.php?thread=$thread";

    if (!$subject) {
        fa_error('Discussion Thread Not Found', 'No discussion thread could '
            . 'be found matching the parameters you supplied.');
    } elseif ($deleted) {
        if (fa_has_mod_access()) {
            echo '<p class="warning">This thread has been deleted. You can ',
                '<a href="', $action, '&amp;undeletethread=1">undelete it</a>',
                ".</p>\n";
            do_comments('comment', $action, 'thread', $thread);
        } else {
            fa_error('Discussion Thread Deleted', 'This discussion thread has '
                . 'been deleted by the moderators.');
        }
    } else {
        if (fa_has_admin_access() && $sticky) echo '<p class="notice">This ',
            'thread is sticky. You can <a href="', $action,
            '&amp;unstickythread=1">make it un-sticky</a></p>', "\n";
        do_comments('comment', $action, 'thread', $thread);
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
