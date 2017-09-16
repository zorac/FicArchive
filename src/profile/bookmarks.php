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
### FILE: profile/bookmarks.php
###
### This page displays a list of the bookmarks which a user has assigned, and
### allows bookmarks to be removed.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('profile.php');
    require_once('search.php');

    $user = $_SESSION['user'];
    $page_title = "Bookmarks";
    include('header.php');

###############################################################################
# Update which bookmarks are public
###############################################################################
    if ($_POST['public']) {
        foreach (array('author', 'search', 'series', 'story') as $type) {
            mysql_query('
                UPDATE  fa_bookmark_' . $type . '
                SET     public = 0
                WHERE   user = ' . $user
            );

            foreach ($_POST as $key => $value) {
                if ($value && preg_match('/^' . $type . '_(\d+)$/', $key,
                        $matches)) {
                    mysql_query('
                        UPDATE  fa_bookmark_' . $type . '
                        SET     public = 1
                        WHERE   user = ' . $user . '
                          AND   ' . $type . ' = ' . $matches[1]
                    );
                }
            }
        }

        $message = '<p class="notice">Your public bookmarks have been ' .
            "updated.</p>\n";
###############################################################################
# Save a search.
###############################################################################
    } elseif ($_POST['save_search']) {
        $search = parse_search($_POST);

        if ($_POST['update']) {
            $result = mysql_query("
                UPDATE  fa_bookmark_search
                SET     query = '" .  mysql_escape_string(serialize(
                            $search['query'])) . "',
                        added = NOW()
                WHERE   search = " . (int)$_POST['search'] . '
                  AND   user = ' . $_SESSION['user']
            );

            if ($result) {
                $message = '<p class="notice">Your saved search has been ' .
                    "updated.</p>\n";
            } else {
                $message = '<p class="error">The saved search you were ' .
                    "trying to update could not be found.</p>\n";
            }
        } elseif ($_POST['name']) {
            $result = mysql_query('
                INSERT INTO     fa_bookmark_search
                                (user, name, query, added, public)
                VALUES          (' . $_SESSION['user'] . ", '" .
                    mysql_escape_string($_POST['name']) . "', '" .
                    mysql_escape_string(serialize($search['query'])) .
                    "', NOW(), " . ($_POST['public'] ? 1 : 0) . ')
            ');

            if ($result) {
                $message = '<p class="notice">Your search has been ' .
                    "successfully saved.</p>\n";
            } else {
                $message = '<p class="error">You already have a saved search' .
                    " with that name.</p>\n";
            }
        } else {
            $message = '<p class="error">You need to supply a name for a ' .
                "saved search.</p>\n";
        }
###############################################################################
# Add a normal bookmark
###############################################################################
    } elseif ($_GET['add']) {
        if ($_GET['author']) {
            $type = 'Author';
            $result = mysql_query('
                SELECT  author,
                        name
                FROM    fa_author
                WHERE   author = ' . (int)$_GET['author']
            );

            if (mysql_num_rows($result) == 1) {
                list($author, $name) = mysql_fetch_row($result);
                $text = 'author <a href="../author.php?author=' . $author
                    . '">' . $name . '</a>';
                mysql_query("
                    REPLACE INTO    fa_bookmark_author
                                    (user, author, added)
                    VALUES          ($user, $author, NOW())
                ");
            }
        } elseif ($_GET['series']) {
            $type = 'Series';
            $result = mysql_query('
                SELECT          fa_series.series,
                                fa_series.name,
                                fa_author.author,
                                fa_author.name
                FROM            fa_series
                  INNER JOIN    fa_author
                    ON          fa_author.author = fa_series.author
                WHERE           fa_series.series = ' . (int)$_GET['series']
            );

            if (mysql_num_rows($result) == 1) {
                list($series, $title, $author, $name)
                    = mysql_fetch_row($result);
                $text = '"<a href="../series.php?series=' . $series . '">'
                    . $title . '</a>" series by <a href="../author.php?author='
                    . $author . '">' . $name . '</a>';
                mysql_query("
                    REPLACE INTO    fa_bookmark_series
                                    (user, series, added)
                    VALUES          ($user, $series, NOW())
                ");
            }
        } elseif ($_GET['story']) {
            $type = 'Story';
            $result = mysql_query('
                SELECT          fa_story.story,
                                fa_story.name,
                                fa_author.author,
                                fa_author.name
                FROM            fa_story
                  INNER JOIN    fa_author
                    ON          fa_author.author = fa_story.author
                WHERE           fa_story.story = ' . (int)$_GET['story']
            );

            if (mysql_num_rows($result) == 1) {
                list($story, $title, $author, $name)
                    = mysql_fetch_row($result);
                $text = 'story "<a href="../story.php?story=' . $story . '">'
                    . $title . '</a>" by <a href="../author.php?author='
                    . $author . '">' . $name . '</a>';
                mysql_query("
                    REPLACE INTO    fa_bookmark_story
                                    (user, story, added)
                    VALUES          ($user, $story, NOW())
                ");
            }
        } else {
            $type = 'Item';
        }

        if ($text) {
            $message = '<p class="notice">The ' . $text
                . " has been added to your bookmarks.</p>\n";
        } else {
            $message = '<p class="error">The ' . $type
                . " you were trying to bookmark could not be found.</p>\n";
        }
###############################################################################
# Remove a bookmark
###############################################################################
    } elseif ($_GET['remove']) {
        if ($_GET['search']) {
            mysql_query("
                DELETE FROM fa_bookmark_search
                WHERE       user = $user
                  AND       search = " . (int)$_GET['search']
            );
            $deleted = mysql_affected_rows();
        } elseif ($_GET['author']) {
            mysql_query("
                DELETE FROM fa_bookmark_author
                WHERE       user = $user
                  AND       author = " . (int)$_GET['author']
            );
            $deleted = mysql_affected_rows();
        } elseif ($_GET['series']) {
            mysql_query("
                DELETE FROM fa_bookmark_series
                WHERE       user = $user
                  AND       series = " . (int)$_GET['series']
            );
            $deleted = mysql_affected_rows();
        } elseif ($_GET['story']) {
            mysql_query("
                DELETE FROM fa_bookmark_story
                WHERE       user = $user
                  AND       story = " . (int)$_GET['story']
            );
            $deleted = mysql_affected_rows();
        }

        if ($deleted) {
            $message = '<p class="notice">The bookmark has been successfully '
                . " removed.</p>\n";
        } else {
            $message = '<p class="error">The bookmark you were trying to '
                . " remove could not be found.</p>\n";
        }
    }
###############################################################################
# Display the bookmarks page
###############################################################################
?>
<h2>Your Bookmarks</h2>
<p>This page lists any authors, stories or series which you have bookmarked. In
    each category, the most recently added bookmarks are first. There are links
    to add bookmarks on every author, story and series page. Click on a
    category title to display a detailed, alphabetized list of bookmarks. Click
    on the X in front of a bookmark to delete it, and use the checkboxes to
    select which of your bookmarks to make public.</p>
<?=$message?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
<dl>
<dt><a href="recent_bookmarks.php">What's New?</a>
<dd>A list of the most recently updated stories matching your bookmarks
    (excluding saved searches).
<?php
    $result = mysql_query("
        SELECT      search,
                    name,
                    public
        FROM        fa_bookmark_search
        WHERE       user = $user
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<dt><a href="search_bookmarks.php">Saved Searches</a>', "\n";
        $found = 1;

        while (list($search, $name, $public) = mysql_fetch_row($result)) {
            echo '<dd>[<a href="bookmarks.php?remove=1&amp;search=', $search,
                '"> X </a>| <input type="checkbox" name="search_', $search,
                ($public ? '" checked="1"' : '"'),
                '/> ] <a href="../search.php?saved=', $search, '">',
                $name, '</a> [<a href="../search.php?saved=', $search,
                '&amp;edit=1"> Edit </a>]', "\n";
        }
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_author.author,
                        fa_author.name,
                        fa_bookmark_author.public
        FROM            fa_author
          INNER JOIN    fa_bookmark_author
            ON          fa_bookmark_author.author = fa_author.author
              AND       fa_bookmark_author.user = $user
        ORDER BY        fa_bookmark_author.added DESC
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<dt><a href="author_bookmarks.php">Authors</a>', "\n";
        $found = 1;

        while (list($author, $name, $public) = mysql_fetch_row($result)) {
            echo '<dd>[<a href="bookmarks.php?remove=1&amp;author=', $author,
                '"> X </a>| <input type="checkbox" name="author_', $author,
                ($public ? '" checked="1"' : '"'),
                '/> ] <a href="../author.php?author=', $author, '">',
                $name, "</a>\n";
        }
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_story.story,
                        fa_story.name,
                        fa_author.author,
                        fa_author.name,
                        fa_bookmark_story.public
        FROM            fa_story
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
          INNER JOIN    fa_bookmark_story
            ON          fa_bookmark_story.story = fa_story.story
              AND       fa_bookmark_story.user = $user
        WHERE           fa_story.hidden = 0
        ORDER BY        fa_bookmark_story.added DESC
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<dt><a href="story_bookmarks.php">Stories</a>', "\n";
        $found = 1;

        while (list($story, $title, $author, $name, $public)
                = mysql_fetch_row($result)) {
            echo '<dd>[<a href="bookmarks.php?remove=1&amp;story=', $story,
                '"> X </a>| <input type="checkbox" name="story_', $story,
                ($public ? '" checked="1"' : '"'),
                '/> ] <a href="../story.php?story=', $story, '">',
                $title, '</a> by <a href="../author.php?author=', $author,
                '">', $name, "</a>\n";
        }
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_series.series,
                        fa_series.name,
                        fa_author.author,
                        fa_author.name,
                        fa_bookmark_series.public
        FROM            fa_series
          INNER JOIN    fa_author
            ON          fa_author.author = fa_series.author
          INNER JOIN    fa_bookmark_series
            ON          fa_bookmark_series.series = fa_series.series
              AND       fa_bookmark_series.user = $user
        ORDER BY        fa_bookmark_series.added DESC
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<dt><a href="series_bookmarks.php">Series</a>', "\n";
        $found = 1;

        while (list($series, $title, $author, $name, $public)
                = mysql_fetch_row($result)) {
            echo '<dd>[<a href="bookmarks.php?remove=1&amp;series=', $series,
                '"> X </a>| <input type="checkbox" name="series_', $series,
                ($public ? '" checked="1"' : '"'),
                '/> ] The <a href="../series.php?series=', $series, '">',
                $title, '</a> series by <a href="../author.php?author=',
                $author, '">', $name, "</a>\n";
        }
    }

    mysql_free_result($result);
    echo "</dl>\n";

    if ($found) {
        echo '<input type="submit" name="public" value="Update Public ',
            'Bookmarks">', "\n</form>\n";
    } else {
        echo '<p class="warning">You do not have any bookmarks.</p>', "\n";
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
