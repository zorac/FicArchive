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
### FILE: boards/review.php
###
### The review boards.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('comments.php');
    require_once('numbers.php');

    $search_page = 'boards';
    $search_params = "type=review";

    if ($_GET['file']) {
        $id = (int)$_GET['file'];
        $search_params = "type=file";

        $result = mysql_query("
            SELECT          fa_story.story      AS story,
                            fa_story.name       AS title,
                            fa_author.author    AS author,
                            fa_author.name      AS author_name,
                            fa_story.chapter    AS chapter,
                            fa_file.number      AS chapter_number,
                            fa_file.name        AS chapter_name
            FROM            fa_file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story
                  AND       fa_story.hidden = 0
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
            WHERE           fa_file.file = $id
        ");

        if (mysql_num_rows($result) == 1) {
            $type = 'file';
            $search_params = "file=$id";
            $file = mysql_fetch_assoc($result);
            $page_title = 'Feedback for ' . $file['title'];

            if ($file['chapter']) {
                $chapter_title = chapter_name($file['chapter_name'],
                    $file['chapter'], $file['chapter_number']);
                $page_title .= ' - ' . $chapter_title;
                $chapter = strtolower($file['chapter']);
            }

            include('header.php');
            echo '<h2><a href="../story.php?story=', $file['story'], '">',
                $file['title'], '</a> <nobr><em>by</em> ',
                '<a href="../author.php?author=', $file['author'], '">',
                $file['author_name'], '</a></nobr>';
            if ($chapter_title) echo "<br>\n<small>",
                '<a href="../file.php?file=', $id, '">', $chapter_title,
                '</a></small>';
            echo "</h2>\n", '<p>This is the feedback/review board for this ',
                ($chapter ? $chapter : 'story'), '. This is the place to ',
                'leave reviews for the author and for general discussion. ',
                'Spoilers are permitted';
            if ($chapter) echo ' for this and earlier ', $chapter,
                's only. You can view a <a href="review.php?files=',
                $file['story'], '">consolidated review board</a> with the ',
                'feedback for all the ', $chapter, 's of this story';
            echo '. There is also a <a href="review.php?story=',
                $file['story'], '">reader review board</a> where you can ',
                "leave spoiler-free reviews for other readers.</p>\n";
        }

        mysql_free_result($result);
    } elseif ($_GET['files']) {
        $id = (int)$_GET['files'];
        $search_params = "type=file";

        $result = mysql_query("
            SELECT          fa_story.story      AS story,
                            fa_story.name       AS title,
                            fa_story.chapter    AS chapter,
                            fa_author.author    AS author,
                            fa_author.name      AS author_name
            FROM            fa_story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
            WHERE           fa_story.story = $id
              AND           fa_story.hidden = 0
        ");

        if (mysql_num_rows($result) == 1) {
            $type = 'files';
            $search_params = "story=$id&amp;type=file";
            $story = mysql_fetch_assoc($result);
            $page_title = 'All Feedback for ' . $story['title'];
            $chapter = strtolower($story['chapter']);
            include('header.php');
            echo '<h2><a href="../story.php?story=', $story['story'], '">',
                $story['title'], '</a> <nobr><em>by</em> ',
                '<a href="../author.php?author=', $story['author'], '">',
                $story['author_name'], "</a></nobr></h2>\n<p>This is the ",
                'feedback/review board for this story';
            if ($chapter) echo ', collecting together all the feedback ',
                'boards for the individual ', $chapter, 's';
            echo '. This is the place to leave reviews for the author and ',
                'for general discussion';
            if ($chapter) echo ' - to leave a new review for a ', $chapter,
                ', click on the ', $chapter, "'s name/number";
            echo '. Spoilers are permitted';
            if ($chapter) echo ', but not for ', $chapter, 's later than the ',
                'one you are commenting on';
            echo '. There is also a <a href="review.php?story=',
                $story['story'], '">reader review board</a> where you can ',
                "leave spoiler-free reviews for other readers.</p>\n";
        }
    } elseif ($_GET['story']) {
        $id = (int)$_GET['story'];
        $search_params = "type=story";

        $result = mysql_query("
            SELECT          fa_story.story      AS story,
                            fa_story.name       AS title,
                            fa_author.author    AS author,
                            fa_author.name      AS author_name
            FROM            fa_story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
            WHERE           fa_story.story = $id
              AND           fa_story.hidden = 0
        ");

        if (mysql_num_rows($result) == 1) {
            $type = 'story';
            $search_params = "story=$id";
            $story = mysql_fetch_assoc($result);
            $page_title = 'Reviews for ' . $story['title'];
            include('header.php');
            echo '<h2><a href="../story.php?story=', $story['story'], '">',
                $story['title'], '</a> <nobr><em>by</em> ',
                '<a href="../author.php?author=', $story['author'], '">',
                $story['author_name'], "</a></nobr></h2>\n<p>This is the ",
                'reader review board for this story. This is the place to ',
                'leave reviews or story descriptions for other readers. ',
                'Please keep your reviews spoiler-free - you can discuss the ',
                'story and leave reviews for the author on the <a href="',
                'review.php?files=', $story['story'], '">feedback board</a>.',
                "</p>\n";
        }
    }

    if (!$type) fa_error('Review Board Not Found', 'No review board could be '
        . 'found matching the parameters you supplied.');
    do_comments('review', "review.php?$type=$id", $type, $id);
    include('footer.php');
###############################################################################
###############################################################################
?>
