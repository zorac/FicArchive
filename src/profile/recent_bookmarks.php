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
### FILE: profile/recent_bookmarks.php
###
### This page displays the most recent stories matching a user's bookmarks.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');

    require_once('display.php');
    require_once('init.php');
    require_once('profile.php');

    $page_title = "Bookmarks - Recent";
    include('header.php');
    echo "<h2>What's New in Your Bookmarks?</h2>\n";
    $user = $_SESSION['user'];

    $perpage = 25;
    $page = (int)$_REQUEST['page'];
    if ($page < 1) $page = 1;
    $skip = ($page - 1) * $perpage;

    $result = mysql_query("
        SELECT DISTINCT SQL_CALC_FOUND_ROWS
                        fa_story.story          AS story_id,
                        fa_story.name           AS story_name,
                        fa_author.author        AS author_id,
                        fa_author.name          AS author_name,
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
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
          INNER JOIN    fa_rating
            ON          fa_rating.rating = fa_story.rating
          LEFT JOIN     fa_bookmark_author
            ON          fa_bookmark_author.author = fa_story.author
              AND       fa_bookmark_author.user = $user
          LEFT JOIN     fa_bookmark_series
            ON          fa_bookmark_series.series = fa_story.series
              AND       fa_bookmark_series.user = $user
          LEFT JOIN     fa_bookmark_story
            ON          fa_bookmark_story.story = fa_story.story
              AND       fa_bookmark_story.user = $user
        WHERE           fa_story.hidden = 0
          AND           (fa_bookmark_author.author IS NOT NULL
            OR           fa_bookmark_series.series IS NOT NULL
            OR           fa_bookmark_story.story IS NOT NULL)
        ORDER BY        fa_story.updated DESC
        LIMIT           $skip, $perpage
    ");

    $results = mysql_found_rows();
    $pages = ceil($results / $perpage);

    if ($results == 0) {
        echo '<p class="warning">You have no bookmarked stories.</p>', "\n";
    } elseif (mysql_num_rows($result) == 0) {
        echo '<p class="warning">There are no more bookmarked stories. ',
            '<a href="recent_bookmarks.php?page=', $pages, '">Go back</a> to ',
            "the last page of stories.</p>\n";
    } else {
        $links = ($pages > 1) ? ('<p class="commentlinks">' . page_links($page,
            $pages, 'recent_bookmarks.php?') . "</p>\n") : '';
        echo $links;

        while ($story = mysql_fetch_assoc($result)) {
            echo story_display($story);
        }

        echo $links;
    }

    mysql_free_result($result);
    include('footer.php');
###############################################################################
###############################################################################
?>
