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
### FILE: series.php
###
### This page displays thge details of a series.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('init.php');
    require_once('display.php');

    if ($_REQUEST['random']) {
        $result = mysql_query('
            SELECT      series
            FROM        fa_series
            ORDER BY    RAND()
            LIMIT       1
        ');

        list($series) = mysql_fetch_row($result);
        mysql_free_result($result);
    } else {
        $series = (int)$_REQUEST['series'];
    }

    $result = mysql_query("
        SELECT          fa_series.series,
                        fa_series.name,
                        fa_author.author,
                        fa_author.name
        FROM            fa_series
          INNER JOIN    fa_author
            ON          fa_author.author = fa_series.author
        WHERE           fa_series.series = $series
    ");

    if (mysql_num_rows($result) == 0) fa_error('Series not found',
        'The series you requested could not be located. Either it does not ' .
        'exist, or it has been removed from the site.');

    list($series, $series_name, $author, $author_name)
        = mysql_fetch_array($result);
    mysql_free_result($result);

    $page_title = "Series Info - $series_name";
    include('header.php');

    echo '<h2>', $series_name, ' <em>by</em> <a href="author.php?author=',
        $author, '">', $author_name, "</a></h2>\n";

    if (fa_has_mod_access())
        echo '<p><strong>Admin</strong>: Edit this <a href="admin/series.php',
            '?series=', $series, '">series</a> or <a href="admin/author.php',
            '?author=', $author, '">author</a>.</p>';

    if ($_SESSION['user']) echo '<p>Add a bookmark for this <a href="profile/',
        'bookmarks.php?add=1&amp;series=', $series,
        '">series</a> or <a href="profile/bookmarks.php?add=1&amp;author=',
        $author, '">author</a>.</p>', "\n";

    $result = mysql_query("
        SELECT          fa_story.story          AS story_id,
                        fa_story.name           AS story_name,
                        fa_rating.name          AS rating_name,
                        fa_story.updated        AS updated,
                        fa_story.size           AS size,
                        fa_story.category_ids   AS category_ids,
                        fa_story.pairing_names  AS pairing_names,
                        fa_story.summary        AS summary,
                        fa_story.chapter        AS chapter,
                        fa_story.file_ids       AS file_ids,
                        fa_story.reviews        AS reviews
        FROM            fa_story
          INNER JOIN    fa_rating
            ON          fa_rating.rating = fa_story.rating
        WHERE           fa_story.series = $series
          AND           fa_story.hidden = 0
        ORDER BY        fa_story.series_order
    ");

    if (mysql_num_rows($result) > 0) {
        echo "<h3>Stories in this series:</h3>\n";
    }

    while ($story = mysql_fetch_assoc($result)) {
        echo story_display($story);
    }

    mysql_free_result($result);

    include('footer.php');
###############################################################################
###############################################################################
?>
