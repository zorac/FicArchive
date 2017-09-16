<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2004,2005,2009 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE: file.php
###
### This page displays a story or chapter file. It also takes care to ensure
### that users are logged in, show the disclaimer and so on before they can
### access the text of a story.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('categories.php');
    require_once('config.php');
    require_once('date.php');
    require_once('init.php');
    require_once('numbers.php');

    if (array_key_exists('username', $_POST)) {
        fa_get_user('login');
    } elseif ($_SESSION['user']) {
        fa_get_user('user');
    }

    $fa_no_login = TRUE;
    $id = $_REQUEST['file'];

    if (preg_match('|(\d+/\w+)|', $id, $match)) {
        $result = mysql_query("
            SELECT  file
            FROM    fa_file
            WHERE   filename = '" . mysql_escape_string($match[0]) . "'
        ");

        if (mysql_num_rows($result) == 1) list($id) = mysql_fetch_row($result);
        mysql_free_result($result);
    } elseif ($_REQUEST['random'] == 'file') {
        $result = mysql_query('
            SELECT      file
            FROM        fa_file
            WHERE       hidden = 0
            ORDER BY    RAND()
            LIMIT       1
        ');

        list($id) = mysql_fetch_row($result);
        mysql_free_result($result);
    } elseif ($_REQUEST['random']) {
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

        $result = mysql_query("
            SELECT      file
            FROM        fa_file
            WHERE       story = $story
            ORDER BY    number
            LIMIT       1
        ");

        list($id) = mysql_fetch_row($result);
        mysql_free_result($result);
    }

    if (is_numeric($id)) {
        $result = mysql_query('
            SELECT          fa_story.story          AS story,
                            fa_story.name           AS title,
                            fa_author.author        AS author,
                            fa_author.name          AS author_name,
                            fa_author.email         AS author_email,
                            fa_rating.rating        AS rating,
                            fa_rating.name          AS rating_name,
                            fa_rating.age           AS rating_age,
                            fa_rating.description   AS rating_description,
                            fa_story.chapter        AS chapter,
                            fa_file.number          AS chapter_number,
                            fa_file.name            AS chapter_name,
                            fa_story.file_ids       AS files,
                            fa_story.summary        AS summary,
                            fa_story.pairing_names  AS pairings,
                            fa_story.category_ids   AS categories,
                            fa_file.notes           AS notes,
                            fa_story.series         AS series,
                            fa_story.series_order   AS number,
                            fa_series.name          AS series_name
            FROM            fa_file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story
                  AND       fa_story.hidden = 0
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              INNER JOIN    fa_rating
                ON          fa_rating.rating = fa_file.rating
              LEFT JOIN     fa_series
                ON          fa_series.series = fa_story.series
            WHERE           fa_file.file = ' . $id . '
        ');

        if (mysql_num_rows($result) == 1) {
            $file = mysql_fetch_assoc($result);

            $require_login = !$_SESSION['user'] || !$_SESSION['seen_password']
                || (get_age($_SESSION['dob']) < $file['rating_age'])
                || (($_SESSION['options']['password_always'] == 'password')
                    && !$fa_login_succeeded);

            if (array_key_exists('accept', $_POST)) {
                $seen_disclaimer = TRUE;
                if ($_SESSION['seen_disclaimer'] < $file['rating'])
                    $_SESSION['seen_disclaimer'] = (int)$file['rating'];
            }
        }

        mysql_free_result($result);
    }

    if (!$file) {
        fa_error('File not found', 'The story or chapter you requested could' .
            ' not be located. Either it does not exist, or it has been' .
            ' removed from the site.');
    } elseif ($require_login
            || (!$_SESSION['options']['per_session'] && !$seen_disclaimer)
            || ($_SESSION['seen_disclaimer'] < $file['rating'])) {
        $page_title = "Disclaimer";
        include('header.php');
        include('disclaimer.php');
    } else {
        $page_title = $file['title'];

        if ($file['chapter']) {
            $chapter_title = chapter_name($file['chapter_name'],
                $file['chapter'], $file['chapter_number']);
            $page_title .= ' - ' . $chapter_title;

            foreach (explode(',', $file['files']) as $t) {
                list($num, $fid) = explode(':', $t);

                if ($fid == $id) {
                    $seen = TRUE;

                    if ($prev) {
                        $prevlink = '<a href="file.php?file=' . $prev
                            . '"> Previous ' . $file['chapter'] . '</a>';
                    }
                } elseif ($seen) {
                    $seen = FALSE;

                    $nextlink .= '<a href="file.php?file=' . $fid . '"> Next '
                        . $file['chapter'] . '</a>';
                } else {
                    $prev = $fid;
                }
            }
        } else {
            $chapter_title = '';
        }

        if (!$prevlink && $file['series']) {
            $result = mysql_query('
                SELECT          fa_file.file
                FROM            fa_file
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_file.story
                      AND       fa_story.series = ' . $file['series'] . '
                      AND       fa_story.series_order < ' . $file['number'] . '
                ORDER BY        fa_story.series_order DESC,
                                fa_file.number DESC
                LIMIT           1
            ');

            if (list($prev) = mysql_fetch_row($result))
                $prevlink = '<a href="file.php?file=' . $prev
                    . '">Previous Story</a>';
            mysql_free_result($result);
        }

        if (!$nextlink && $file['series']) {
            $result = mysql_query('
                SELECT          fa_file.file
                FROM            fa_file
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_file.story
                      AND       fa_story.series = ' . $file['series'] . '
                      AND       fa_story.series_order > ' . $file['number'] . '
                ORDER BY        fa_story.series_order,
                                fa_file.number
                LIMIT           1
            ');

            if (list($next) = mysql_fetch_row($result))
                $nextlink = '<a href="file.php?file=' . $next
                    . '">Next Story</a>';
            mysql_free_result($result);
        }

        if ($prevlink) {
            if ($nextlink) {
                $prevnext = "<p>$prevlink - $nextlink</p>\n";
            } else {
                $prevnext = "<p>$prevlink</p>\n";
            }
        } elseif ($nextlink) {
            $prevnext = "<p>$nextlink</p>\n";
        }

        include('header.php');

        echo '<h2><a href="story.php?story=', $file['story'], '">',
            $file['title'], '</a> <nobr><em>by</em> ',
            '<a href="author.php?author=', $file['author'], '">',
            $file['author_name'], "</a></nobr>";
        if ($chapter_title) echo "<br>\n<small>$chapter_title</small>";
        echo "</h2>\n";

        if ($file['series']) echo '<p>This is the ', strtolower(position_name(
            $file['number'])), ' story in the "<a href="series.php?series=',
            $file['series'], '">', $file['series_name'], '</a>" series.</p>',
            "\n";

        if (fa_has_mod_access()) {
            echo '<p><strong>Admin</strong>: Edit this <a href="admin/',
                'file.php?file=', $id, '">file</a>, <a href="admin/story.php',
                '?story=', $file['story'], '">story</a>';
            if ($file['series']) echo ', <a href="admin/series.php?series=',
                $file['series'], '">series</a>';
            echo ' or <a href="admin/author.php?author=', $file['author'],
                '">author</a></p>';
        }

        $bookmark = '<p>Add a bookmark for this <a href="profile/bookmarks.php'
            . '?add=1&amp;story=' . $file['story'] . '">story</a>';
        if ($file['series']) $bookmark .= ', <a href="profile/bookmarks.php'
            . '?add=1&amp;series=' . $file['series'] . '">series</a>';
        $bookmark .= ' or <a href="profile/bookmarks.php?add=1&amp;author='
            . $file['author'] . '">author</a>.</p>' . "\n";
        echo $bookmark;

        $notes = array();

        if ($fa_disclaimer) $notes['Disclaimer'] = $fa_disclaimer;
        if ($file['summary']) $notes['Summary'] = $file['summary'];
        if ($file['pairings']) $notes['Pairings'] = $file['pairings'];
        if ($file['categories']) $notes['Categories'] =
            category_ids_to_names($file['categories']);
        if ($file['notes']) $notes['Notes'] = $file['notes'];

        if ($notes) {
            reset($notes);
            echo '<table class="info">', "\n";

            while (list($key, $value) = each($notes)) {
                echo "<tr><th>$key</th><td>$value</td></tr>\n";
            }

            echo "</table>\n";
        }

        echo "$prevnext<hr>\n";

        if (!@readfile('files/' . ($id % 10) . '/' . (($id / 10) % 10) . '/'
            . $id)) echo '<p class="error">The text of this ',
                ($file['chapter'] ? strtolower($file['chapter']) : 'story'),
                " could not be found!</p>\n";

        echo "<hr>\n$prevnext";

        if (!$fa_disable_posting) {
            echo '<p>Authors love reviews! You can <a href="boards/review.php',
                '?file=', $id, '&amp;reply=new">post feedback</a> on this ',
                ($file['chapter'] ? strtolower($file['chapter']) : 'story'),
                "'s ", '<a href="boards/review.php?file=', $id,
                '">feedback board</a>';
            if ($file['author_email']) echo ', or you can <a href="mailto:',
                $file['author_email'], '">email the author</a> directly';
            echo '. You can also <a href="boards/review.php?story=',
                $file['story'], '&amp;reply=new">post a spoiler-free ',
                "review</a> for the benefit of other readers on this story's ",
                '<a href="boards/review.php?story=', $file['story'],
                '">reader review board</a>.</p>', "\n";
        }

        echo $bookmark;
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
