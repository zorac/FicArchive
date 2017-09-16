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
### FILE: byauthor.php
###
### This page displays series and stories grouped by author.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');

    $fa_last_letter = 'author';

    require_once('init.php');
    require_once('letters.php');
    require_once('display.php');

    $page_title = "Stories by Author - $letter_selected";
    include('header.php');
?>
<h2>Stories by Author</h2>
<p><form method="post" action="search.php">
<input name="author" size="40">
<input type="hidden" name="sort" value="author">
<input type="submit" name="search" value="Author Quicksearch">
</form></p>
<?php
    if ($fa_random_letter) echo "<p>You have been randomly taken to the page ",
        "for authors whose names begin with $fa_random_letter.</p>\n";
    echo "$letter_links\n";

    $authors = array();
    $serieses = array();

    $result = mysql_query("
        SELECT          fa_series.series        AS series_id,
                        fa_series.name          AS series_name,
                        fa_author.name          AS author_name
        FROM            fa_series
          INNER JOIN    fa_author
            ON          fa_author.author = fa_series.author
              AND       fa_author.name $letter_match
    ");

    while ($series = mysql_fetch_assoc($result)) {
        $series['stories'] = array();
        $serieses[$series['series_id']] = $series;

        if (!array_key_exists($series['author_name'], $authors)) {
            $authors[$series['author_name']] = array();
        }

        $authors[$series['author_name']][$series['series_name']]
            =& $serieses[$series['series_id']];
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_story.story          AS story_id,
                        fa_story.name           AS story_name,
                        fa_author.name          AS author_name,
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
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
              AND       fa_author.name $letter_match
        WHERE           fa_story.hidden = 0
        ORDER BY        fa_story.series_order
    ");

    if (mysql_num_rows($result) == 0) {
        echo "<p>No stories found.</p>\n";
    }

    while ($story = mysql_fetch_assoc($result)) {
        if ($story['series_id']) {
            array_push($serieses[$story['series_id']]['stories'], $story);
        } else {
            if (!array_key_exists($story['author_name'], $authors)) {
                $authors[$story['author_name']] =
                    array($story['story_name'] => $story);
            } else {
                $authors[$story['author_name']][$story['story_name']]
                    = $story;
            }
        }
    }

    mysql_free_result($result);
    $author_names = array_keys($authors);
    natcasesort($author_names);

    while ($author = array_shift($author_names)) {
        echo '<h3 class="author"><a href="author.php?name=',
            urlencode($author), '">', $author, "</a></h3>\n";
        $item_names = array_keys($authors[$author]);
        natcasesort($item_names);

        while ($item = $authors[$author][array_shift($item_names)]) {
            if (array_key_exists('series_name', $item)) {
                echo series_display($item);
            } else {
                echo story_display($item);
            }
        }
    }

    echo $letter_links;
    include('footer.php');
###############################################################################
###############################################################################
?>
