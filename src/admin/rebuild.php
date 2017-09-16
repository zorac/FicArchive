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
### FILE: admin/rebuild.php
###
### This page allows for the rebuilding of parts of the database.
###
### Does not function correctly on large datasets at present.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('admin.php');
    require_once('comments.php');
    require_once('rebuild.php');

    $page_title = 'Admin - Database Rebuild';
    include('header.php');

    echo "<h2>Database Rebuild</h2>\n";

    if ($_REQUEST['rebuild']) {
        $backlinks[] = array('Main Rebuild Page', 'rebuild.php');
        echo display_backlinks();
        echo "<h3>Rebuilding Pairings</h3>\n<small>";

        $result = mysql_query('
            SELECT  pairing
            FROM    fa_pairing
        ');

        while (list($pairing) = mysql_fetch_row($result)) {
            rebuild_pairing($pairing);
            echo ".\n";
        }

        echo "</small>\n<h3>Rebuilding Stories</h3>\n<small>";

        $result = mysql_query('
            SELECT  story
            FROM    fa_story
        ');

        while (list($story) = mysql_fetch_row($result)) {
            rebuild_story($story);
            echo ".\n";
        }

        mysql_free_result($result);
        echo "\n</small>\n<p><strong>All cached information has been ",
            "rebuilt!</strong></p>\n";
    } elseif ($_REQUEST['boards']) {
        $backlinks[] = array('Main Rebuild Page', 'rebuild.php');
        echo display_backlinks();
        echo "<h3>Rebuilding Threads</h3>\n<small>";

        $result = mysql_query('
            SELECT DISTINCT type,
                            id
            FROM            fa_comment
        ');

        while (list($type, $id) = mysql_fetch_row($result)) {
            cleanup_thread($type, $id);
            echo ".\n";
        }

        mysql_free_result($result);
        echo "\n</small>\n<p><strong>All threading information has been ",
            "rebuilt!</strong></p>\n";
    } elseif ($_REQUEST['autoformat']) {
        $backlinks[] = array('Main Rebuild Page', 'rebuild.php');
        echo display_backlinks();
        echo "<h3>Rebuilding Auto-format</h3>\n<small>";

        $result = mysql_query('
            SELECT  comment,
                    body
            FROM    fa_comment
        ');

        while (list($id, $body) = mysql_fetch_row($result)) {
            mysql_query ('
                UPDATE  fa_comment
                SET     format = ' .
                    (preg_match("/(?<!<br>)(\r?\n|\r)/im", $body) ? 0 : 1) . "
                WHERE   comment = $id
            ");
            echo ".\n";
        }

        mysql_free_result($result);
        echo "\n</small>\n<p><strong>All auto-format flags have been reset!",
            "</strong></p>\n";
    } elseif ($_REQUEST['lastthread']) {
        $backlinks[] = array('Main Rebuild Page', 'rebuild.php');
        echo display_backlinks();
        echo "<h3>Rebuilding Last Threads</h3>\n<small>";

        $result = mysql_query("
            SELECT      user,
                        MAX(id)
            FROM        fa_last_comment
            WHERE       type = 'thread'
            GROUP BY    user
        ");

        while (list($user, $max) = mysql_fetch_row($result)) {
            mysql_query ("
                REPLACE INTO    fa_last_thread
                                (user, board, thread)
                VALUES          ($user, 1, $max)
            ");
            echo ".\n";
        }

        mysql_free_result($result);
        echo "\n</small>\n<p><strong>All last threads have been reset!",
            "</strong></p>\n";
    } else {
?>
<?=display_backlinks()?>
<p>The archive maintains a cache of certain pairing and story information in
    the database to speed up searches and the display of story listings. This
    information should be automatically updated whenever you make any changes,
    but if not, you can update all of the cached information in the database.</p>
<form action="rebuild.php" method="post"><input type="submit" name="rebuild" value="Rebuild cached information for all stories"></form>
<p>The discussion and review boards cache threading information to speed up the
    display of the boards. If the threading for a board gets messed up then
    this information will need to be recalculated.</p>
<form action="rebuild.php" method="post"><input type="submit" name="boards" value="Rebuild threading information for all boards"></form>
<p>Sometimes site updates will require updates to the contents of the database.
    These updates should only ever need to be run once, immediately after the
    upgrade:<br>
<a href="rebuild.php?autoformat=1">Reset comment auto-format flags</a><br>
<a href="rebuild.php?lastthread=1">Reset last thread counters</a></p>
<?php
    }

    include('footer.php')
###############################################################################
###############################################################################
?>
