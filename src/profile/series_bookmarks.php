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
### FILE: profile/series_bookmarks.php
###
### This page displays a list of series which the user has bookmarked.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('display.php');
    require_once('init.php');
    require_once('profile.php');

    $page_title = "Bookmarks - Series";
    include('header.php');

    echo "<h2>Bookmarked Series</h2>\n";

    $serieses = array();
    $user = $_SESSION['user'];

    $result = mysql_query("
        SELECT          fa_series.series    AS series_id,
                        fa_series.name      AS series_name,
                        fa_author.author    AS author_id,
                        fa_author.name      AS author_name
        FROM            fa_series
          INNER JOIN    fa_author
            ON          fa_author.author = fa_series.author
          INNER JOIN    fa_bookmark_series
            ON          fa_bookmark_series.series = fa_series.series
              AND       fa_bookmark_series.user = $user
        ORDER BY        fa_series.name
    ");

    while ($series = mysql_fetch_assoc($result)) {
        $series['stories'] = array();
        $serieses[$series['series_id']] = $series;
    }

    mysql_free_result($result);
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
                        fa_story.series         AS series_id,
                        fa_story.reviews        AS reviews
        FROM            fa_story
          INNER JOIN    fa_rating
            ON          fa_rating.rating = fa_story.rating
          INNER JOIN    fa_bookmark_series
            ON          fa_bookmark_series.series = fa_story.series
              AND       fa_bookmark_series.user = $user
        WHERE           fa_story.hidden = 0
        ORDER BY        fa_story.series_order
    ");

    if (mysql_num_rows($result) == 0) echo '<p class="warning">You have no ',
        "series bookmarked, or they have no available stories.</p>\n";

    while ($story = mysql_fetch_assoc($result)) {
         array_push($serieses[$story['series_id']]['stories'], $story);
    }

    mysql_free_result($result);

    while ($series = array_shift($serieses)) {
        echo series_display($series);
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
