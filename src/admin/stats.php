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
### FILE: admin/stats.php
###
### This page displays a selection of statistical information.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');
    require_once('numbers.php');

    $stats = array();

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_author
    ');

    list($stats['Authors']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_series
    ');

    list($stats['Series']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_story
    ');

    list($stats['Stories']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  SUM(size),
                COUNT(size)
        FROM    fa_file
    ');

    list($stats['Words'], $stats['Files/Chapters']) = mysql_fetch_row($result);
    mysql_free_result($result);
    $stats[] = '-';

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_person
    ');

    list($stats['Characters']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_nickname
    ');

    list($stats['Character Names']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(DISTINCT(person_ids))
        FROM    fa_pairing
    ');

    list($stats['Pairings']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_pairing
    ');

    list($stats['Pairing Names']) = mysql_fetch_row($result);
    mysql_free_result($result);
    $stats[] = '-';

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_board
    ');

    list($stats['Discussion Boards']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_thread
    ');

    list($stats['Discussion Board Threads']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_comment
    ');

    list($stats['Discussion/Review Board Comments']) = mysql_fetch_row($result);
    mysql_free_result($result);
    $stats[] = '-';

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_user
    ');

    list($stats['Registered Users']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query("
        SELECT  COUNT(*)
        FROM    fa_user
        WHERE   type = 'verified'
    ");

    list($stats['Verified Users']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = mysql_query("
        SELECT  COUNT(*)
        FROM    fa_user
        WHERE   type = 'mod'
          OR    type = 'admin'
    ");

    list($stats['Mods/Admins']) = mysql_fetch_row($result);
    mysql_free_result($result);

    $page_title = 'Admin - Statistics';
    include('header.php');
?>
<h2>Pointless Statistics</h2>
<p>This page lists a few interesting statics about the site, just for the fun of it.</p>
<?=display_backlinks()?>
<table class="info">
<?php
    foreach ($stats as $name => $count) {
        if ($count == '-') {
            echo '<tr><td colspan="2"><hr></td></tr>', "\n";
        } else {
            echo "<tr><th>$name</th><td>", nice_count($count), "</td></tr>\n";
        }
    }

    echo "</table>\n";
    include('footer.php')
###############################################################################
###############################################################################
?>
