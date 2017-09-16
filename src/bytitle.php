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
### FILE: bytitle.php
###
### This page displays stories ordered by title.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');

    $fa_last_letter = 'title';

    require_once('init.php');
    require_once('letters.php');
    require_once('display.php');

    $page_title = "Stories by Title - $letter_selected";
    include('header.php');
?>
<h2>Stories by Title</h2>
<p><form method="post" action="search.php">
<input name="title" size="40">
<input type="hidden" name="sort" value="title">
<input type="submit" name="search" value="Title Quicksearch">
</form></p>
<?php
    if ($fa_random_letter) echo "<p>You have been randomly taken to the page ",
        "for stories which have titles beginning with $fa_random_letter.</p>\n";
    echo "$letter_links\n";

    $result = mysql_query("
        SELECT          fa_story.story          AS story_id,
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
        WHERE           fa_story.name $letter_match
          AND           fa_story.hidden = 0
        ORDER BY        fa_story.name
    ");

    if (mysql_num_rows($result) == 0) {
        echo "<p>No stories found.</p>\n";
    }

    while ($story = mysql_fetch_assoc($result)) {
        echo story_display($story);
    }

    mysql_free_result($result);
    echo $letter_links;
    include('footer.php');
###############################################################################
###############################################################################
?>
