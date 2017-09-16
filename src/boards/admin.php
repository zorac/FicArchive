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
### FILE: boards/admin.php
###
### The moderators' private message board.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');
    require_once('comments.php');

    $search_page = 'boards';
    $search_params = 'board=admin';

    $backlinks = array(array('Administration Home', '../admin/'));
    $page_title = "Admin - Discussions";
    $thread = (int)$_GET['thread'];

    if ($thread > 0) {
        $result = mysql_query("
            SELECT          fa_user.user,
                            fa_user.name,
                            fa_thread.time,
                            fa_thread.subject,
                            fa_thread.body
            FROM            fa_thread
              INNER JOIN    fa_user
                ON          fa_user.user = fa_thread.user
            WHERE           fa_thread.thread = $thread
              AND           fa_thread.board = 0
        ");

        if (mysql_num_rows($result) != 1) admin_error('Discussion Not Found',
            'No discussion thread could be found matching the parameters you '
            . 'supplied.');

        list($user, $username, $time, $subject, $body) =
            mysql_fetch_row($result);
        mysql_free_result($result);

        $search_params = "thread=$thread";
        $backlinks[] = array('Discussion Threads', 'admin.php');
        if ($_GET['reply'] || $_POST['post'])
            $backlinks[] = array('All Comments', "admin.php?thread=$thread");

        include('header.php');
        echo "<h2>Moderators' Discussion Board</h2>", display_backlinks(),
            '<h3><a href="../profile.php?user=', $user, '">', $username,
            '</a>: ', $subject, "</h3>\n";
        if ($body) echo "<p>$body</p>\n";
        do_comments('comment', "admin.php?thread=$thread", 'thread', $thread);
    } else {
        if ($_GET['new'] || $_POST['post'])
            $backlinks[] = array('Discussion Threads', 'admin.php');

        include('header.php');
        echo "<h2>Moderators' Discussion Board</h2>", display_backlinks();
        do_threads(0, "admin.php", "admin.php?");
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
