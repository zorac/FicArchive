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
### FILE: author.php
###
### Thhis page displays the details of an author and their stories/series.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('init.php');
    require_once('display.php');

    if ($_REQUEST['random']) {
        $where = 'ORDER BY RAND() LIMIT 1';
    } elseif ($_REQUEST['name']) {
        $where = "WHERE name = '" . mysql_escape_string($_REQUEST['name'])
            . "'";
    } else {
        $where = 'WHERE author = ' . (int)($_REQUEST['author']);
    }

    $result = mysql_query("
        SELECT  author,
                name,
                email
        FROM    fa_author
        $where
    ");

    if (mysql_num_rows($result) == 0) fa_error('Author not found',
        'The author you requested could not be located. Either they do not ' .
        'exist, or they have been removed from the site.');

    list($author, $author_name, $author_email) = mysql_fetch_array($result);
    mysql_free_result($result);

    $page_title = "Author Profile - $author_name";
    include('header.php');

    echo "<h2>Author Profile: $author_name</h2>\n";

    if ($_SESSION['user']) {
        if (fa_has_mod_access())
            echo '<p><strong>Admin</strong>: Edit this <a href="admin/',
                'author.php?author=', $author, '">author</a>.</p>', "\n";
?>
<p><a href="profile/bookmarks.php?add=1&amp;author=<?=$author?>">Add a bookmark for this author.</a></p>
<table class="info">
<tr><th>Email Address</th><td><a href="mailto:<?=$author_email?>"><?=$author_email?></a></td></tr>
<tr><th>Recent Reviews</th><td><a href="boards/recent.php?author=<?=$author?>&amp;type=review">For Readers</a>.<br>
<a href="boards/recent.php?author=<?=$author?>&amp;type=feedback">For the Author</a> - may contain spoilers.</td></tr>
</table>
<?php
    } else {
        echo "<p>Profile information is only visible if you log in.</p>\n";
    }

    $items = array();
    $serieses = array();

    $result = mysql_query("
        SELECT  series  AS series_id,
                name    AS series_name
        FROM    fa_series
        WHERE   author = $author
    ");

    while ($series = mysql_fetch_assoc($result)) {
        $series['stories'] = array();
        $serieses[$series['series_id']] = $series;
        $items[$series['series_name']] =& $serieses[$series['series_id']];
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
        WHERE           fa_story.author = $author
          AND           fa_story.hidden = 0
        ORDER BY        fa_story.series_order
    ");

    if (mysql_num_rows($result) > 0) {
        echo "<h3>Stories:</h3>\n";
    }

    while ($story = mysql_fetch_assoc($result)) {
        if ($story['series_id']) {
            array_push($serieses[$story['series_id']]['stories'], $story);
        } else {
            $items[$story['story_name']] = $story;
        }
    }

    mysql_free_result($result);
    $item_names = array_keys($items);
    natcasesort($item_names);

    while ($item = $items[array_shift($item_names)]) {
        if (array_key_exists('series_name', $item)) {
            echo series_display($item);
        } else {
            echo story_display($item);
        }
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
