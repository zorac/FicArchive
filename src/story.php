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
### FILE: story.php
###
### This page displays the details of a story.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('categories.php');
    require_once('date.php');
    require_once('init.php');
    require_once('numbers.php');
    require_once('pairings.php');

    if ($_REQUEST['random']) {
        $result = mysql_query('
            SELECT      story,
                        series
            FROM        fa_story
            WHERE       hidden = 0
            ORDER BY    RAND()
            LIMIT       1
        ');

        list($story, $series) = mysql_fetch_row($result);
        mysql_free_result($result);

        if ($series && ($_REQUEST['random'] == 'series')) {
            $result = mysql_query("
                SELECT      story
                FROM        fa_story
                WHERE       series = $series
                ORDER BY    series_order
                LIMIT       1
            ");

            list($story) = mysql_fetch_row($result);
            mysql_free_result($result);
        }
    } else {
        $story = (int)$_REQUEST['story'];
    }

    $result = mysql_query("
        SELECT          fa_story.story,
                        fa_story.name,
                        fa_author.author,
                        fa_author.name,
                        fa_rating.rating,
                        fa_rating.name,
                        fa_story.summary,
                        fa_story.chapter,
                        fa_story.file_ids,
                        fa_story.updated,
                        fa_story.size,
                        fa_story.reviews,
                        fa_series.series,
                        fa_series.name,
                        fa_story.series_order
        FROM            fa_story
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
          INNER JOIN    fa_rating
            ON          fa_rating.rating = fa_story.rating
          LEFT JOIN     fa_series
            ON          fa_series.series = fa_story.series
        WHERE           fa_story.story = $story
          AND           fa_story.hidden = 0
    ");

    if (mysql_num_rows($result) == 0) fa_error('Story not found',
        'The story you requested could not be located. Either it does not ' .
        'exist, or it has been removed from the site.');

    list($story, $story_name, $author, $author_name, $rating, $rating_name,
        $summary, $chapter, $has_files, $updated, $size, $reviews, $series,
        $series_name, $series_order) = mysql_fetch_array($result);
    mysql_free_result($result);

    $page_title = "Story Info - $story_name";
    include('header.php');

    echo '<h2>', $story_name, ' <nobr><em>by</em> <a href="author.php?author=',
        $author, '">', $author_name, "</a></nobr></h2>\n";

    if ($series) echo '<p>This is the ', strtolower(position_name(
        $series_order)), ' story in the "<a href="series.php?series=', $series,
        '">', $series_name, '</a>" series.</p>', "\n";

    if (fa_has_mod_access()) {
        echo '<p><strong>Admin</strong>: Edit this <a href="admin/story.php',
            '?story=', $story, '">story</a>';
        if ($series) echo ', <a href="admin/series.php?series=', $series,
            '">series</a>';
        echo ' or <a href="admin/author.php?author=', $author, '">author</a>.',
            "</p>\n";
    }

    if ($_SESSION['user']) {
        echo '<p>Add a bookmark for this <a href="profile/bookmarks.php',
            '?add=1&amp;story=', $story, '">story</a>';
        if ($series) echo ', <a href="profile/bookmarks.php?add=1&amp;series=',
            $series, '">series</a>';
        echo ' or <a href="profile/bookmarks.php?add=1&amp;author=', $author,
            '">author</a>.</p>', "\n";
    }

    echo "<table class=\"info\">\n";

    if ($rating > 0)
        echo '<tr><th>Rating</th><td><a href="rating.php?rating=',
            $rating, '">', $rating_name, "</a></td></tr>\n";

    $categories = get_story_categories($story, TRUE);
    if (count($categories) > 0)
        echo '<tr><th>Categor', ((count($categories) == 1) ? 'y' : 'ies'),
            '</th><td>', implode("<br>\n", $categories), "</td></tr>\n";

    $pairings = get_story_pairings($story, FALSE, $null);
    if (count($pairings) > 0)
        echo '<tr><th>Pairing', ((count($pairings) == 1) ? '' : 's'),
            '</th><td>', implode("<br>\n", $pairings), "</td></tr>\n";

    if ($summary) echo "<tr><th>Summary</th><td>$summary</td></tr>\n";

    echo '<tr><th>Reviews</th><td><a href="boards/review.php?story=', $story,
        '">For Readers</a> - ', (($reviews == 0) ? 'post the first!' :
        (strtolower(num_to_words($reviews)) . ' posted so far.')),
        '<br><a href="boards/review.php?files=', $story, '">',
        "For the Author</a> - may contain spoilers.</td></tr>\n";

    if ($has_files && $chapter) {
        echo '<tr><th>Read</th><td>';

        $result = mysql_query("
            SELECT          fa_file.number,
                            fa_file.file,
                            fa_file.name,
                            fa_rating.name,
                            fa_file.updated,
                            fa_file.size
            FROM            fa_file
              INNER JOIN    fa_rating
                ON          fa_rating.rating = fa_file.rating
            WHERE           fa_file.story = $story
            ORDER BY        fa_file.number
        ");

        while (list($number, $file, $name, $rating, $updated, $size) =
                mysql_fetch_array($result)) {
            echo '<div class="file"><a href="file.php?file=', $file, '">',
                chapter_name($name, $chapter, $number), '</a> (<span class="',
                'rating">', $rating, '</span>, <span class="date">',
                short_date($updated), '</span>, <span class="size">',
                nice_count($size), ' words</span>)</div>', "\n";
        }

        mysql_free_result($result);
    } elseif ($has_files) {
        list($number, $file) = explode(':', $has_files);
        echo '<tr><th>Updated</th><td>', long_date($updated, TRUE),
            "</td></tr>\n<tr><th>Length</th><td>", nice_count($size),
            " words</td></tr>\n", '<tr><td></td><td><a href="file.php?file=',
            $file, '">', "Click here</a> to read this story.</td></tr>\n";
    } else {
        echo "<tr><td></td><td>This story is not yet available.</td></tr>\n";
    }

    echo "</table>\n";

    include('footer.php');
###############################################################################
###############################################################################
?>
