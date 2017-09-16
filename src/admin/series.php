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
### FILE: admin/series.php
###
### This page displays the details of a series and allows those details to be
### updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');

    function swap_stories($a, $b) {
        global $stories;

        $t = $stories[$a - 1];
        $stories[$a - 1] = $stories[$b - 1];
        $stories[$b - 1] = $t;

        mysql_query('
            UPDATE  fa_story
            SET     series_order = ' . $a . '
            WHERE   story = ' . $stories[$a - 1][0]
        );

        mysql_query('
            UPDATE  fa_story
            SET     series_order = ' . $b . '
            WHERE   story = ' . $stories[$b - 1][0]
        );
    }

    $backlinks[] = array('All Authors', 'authors.php');

    if ($_POST['add']) {
        $title = trim($_POST['name']);

        if (!$title) admin_error('No Title Supplied',
            'You must supply a title to add a new series.');

        $result = mysql_query('
            SELECT  author,
                    name
            FROM    fa_author
            WHERE   author = ' . (int)$_POST['author']
        );

        if (mysql_num_rows($result) == 1) {
            list($author, $name) = mysql_fetch_row($result);
            mysql_free_result($result);
        } else {
            admin_error('No Author Specified', 'No author ID was given when ' .
                'attempting to create a series.');
        }

        $result = mysql_query("
            INSERT INTO fa_series
                        (author, name)
            VALUES      ($author, '" . mysql_escape_string($title) . "')
        ");

        if ($result) {
            $series = mysql_insert_id();
        } else { #TODO link
            admin_error('Title Already Exists',
                'This author already has a series with that title.');
        }
    } else {
        $result = mysql_query('
            SELECT          fa_series.series,
                            fa_series.name,
                            fa_author.author,
                            fa_author.name
            FROM            fa_series
              INNER JOIN    fa_author
                ON          fa_author.author = fa_series.author
            WHERE           fa_series.series = ' . (int)$_REQUEST['series']
        );

        if (list($series, $title, $author, $name) = mysql_fetch_row($result)) {
            mysql_free_result($result);
        } else {
            admin_error('Series Not Found',
                'No series was found matching the supplied ID.');
        }

        $backlinks[] = array('Author: ' . $name, 'author.php?author='
            . $author);
        $stories = array();

        $result = mysql_query("
            SELECT      story,
                        name
            FROM        fa_story
            WHERE       series = $series
            ORDER BY    series_order
        ");

        while ($story = mysql_fetch_row($result)) {
            $stories[] = $story;
        }

        if ($_POST['update']) {
            $new_title = trim($_POST['name']);

            if ($new_title) {
                $result = mysql_query("
                    UPDATE  fa_series
                    SET     name = '" . mysql_escape_string($new_title) . "'
                    WHERE   series = $series
                ");

                if ($result) {
                    $title = $new_title;
                } else {
                    $error = 'The series could not be renamed.';
                }
            } else {
                $error = 'No new title was supplied.';
            }
        } elseif ($_POST['modify']) {
            $len = count($stories);

            foreach ($_POST as $key => $value) {
                if ($value && preg_match(
                        '/^(first|last|prev|next|insert|delete)-(\d+)$/',
                        $key, $matches)) {
                    list($match, $action, $num) = $matches;
                }
            }

            if (($action == 'insert') && ($num > 0) && ($num <= ($len + 1))) {
                $result = mysql_query('
                    SELECT  story,
                            name
                    FROM    fa_story
                    WHERE   story = ' . (int)$_POST['story'] . '
                      AND   author = ' . $author . '
                      AND   series IS NULL
                ');

                if (mysql_num_rows($result) == 1) {
                    $t = mysql_fetch_array($result);
                    array_splice($stories, ($num - 1), 0, array($t));

                    mysql_query("
                        UPDATE  fa_story
                        SET     series_order = series_order + 1
                        WHERE   series = $series
                          AND   series_order >= $num
                    ");

                    mysql_query("
                        UPDATE  fa_story
                        SET     series = $series,
                                series_order = $num
                        WHERE   story = " . $t[0]
                    );
                } else {
                    $error = 'You must select a story to add it to the series.';
                }

                mysql_free_result($result);
            } elseif (($action == 'delete') && ($num > 0) && ($num <= $len)) {
                array_splice($stories, ($num - 1), 1);

                mysql_query("
                    UPDATE  fa_story
                    SET     series = NULL,
                            series_order = NULL
                    WHERE   series = $series
                      AND   series_order = $num
                ");

                mysql_query("
                    UPDATE  fa_story
                    SET     series_order = series_order - 1
                    WHERE   series = $series
                    AND     series_order > $num
                ");
            } elseif (($action == 'first') && ($num > 1) && ($num <= $len)) {
                list($t) = array_splice($stories, ($num - 1), 1);
                array_unshift($stories, $t);

                mysql_query("
                    UPDATE  fa_story
                    SET     series_order = 0
                    WHERE   series = $series
                      AND   series_order = $num
                ");

                mysql_query("
                    UPDATE  fa_story
                    SET     series_order = series_order + 1
                    WHERE   series = $series
                    AND     series_order < $num
                ");
            } elseif (($action == 'prev') && ($num > 1) && ($num <= $len)) {
                swap_stories($num, $num - 1);
            } elseif (($action == 'next') && ($num > 0) && ($num < $len)) {
                swap_stories($num, $num + 1);
            } elseif (($action == 'last') && ($num > 0) && ($num < $len)) {
                list($t) = array_splice($stories, ($num - 1), 1);
                array_push($stories, $t);

                mysql_query('
                    UPDATE  fa_story
                    SET     series_order = ' . ($len + 1) . "
                    WHERE   series = $series
                      AND   series_order = $num
                ");

                mysql_query("
                    UPDATE  fa_story
                    SET     series_order = series_order - 1
                    WHERE   series = $series
                    AND     series_order > $num
                ");
            }
        }
    }

    $page_title = "Admin - Series - $title";
    include('header.php');
?>
<h2>Series info: <?=$title?></h2>
<p><a href="../series.php?series=<?=$series?>">View the public page for this series</a></p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . "</p>\n") : '')?>
<h3>Update Series Details</h3>
<p>You can amend the series title if you feel the need.</p>
<form action="series.php?series=<?=$series?>" method="post">
<table class="info">
<tr><th>Title</th><td><input name="name" value="<?=$title?>" size="60"></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update Series"></td></tr>
</table>
</form>
<h3>Stories</h3>
<p>You can add to, remove or re-order the stories which make up this series.</p>
<form action="series.php?series=<?=$series?>" method="post">
<input type="hidden" name="modify" value="1">
<?php
    $result = mysql_query("
        SELECT      story,
                    name
        FROM        fa_story
        WHERE       author = $author
        AND         series IS NULL
        ORDER BY    name
    ");

    if (mysql_num_rows($result) == 0) {
        echo "<p>There are no stories for this author which are not already ",
            "part of a series.</p>";
    } else {
        echo '<table class="info">', "\n<tr><th>Story to add</th><td>",
            '<select name="story"><option value="">-- Select a Story --',
            "</option>\n";

        while (list($story, $name) = mysql_fetch_row($result)) {
            echo '<option value="', $story, '">', $name, "</option>\n";
        }

        echo "</select></td></tr>\n<tr><td></td><td>",
            '<input type="submit" name="insert-', (count($stories) + 1),
            '" value="Add as Last Story"></td></tr>', "\n</table>\n";
        if ($stories) echo "<p>\n";
        $can_add = TRUE;
    }

    if ($stories) {
        echo '<table class="reorder">', "\n";
        reset($stories);
        $i = 0;

        while (list($story, $name) = current($stories)) {
            next($stories);
            $i++;
            echo '<tr><td>';
            if ($i > 2) echo '<input type="submit" name="first-', $i,
                '" value="&lt;&lt;">';
            echo '</td><td>';
            if ($i > 1) echo '<input type="submit" name="prev-', $i,
                '" value="&lt;">';
            echo '</td><td>';
            if ($can_add) echo '<input type="submit" name="insert-', $i,
                '" value="Add"></td><td>';
            echo '<a href="story.php?story=', $story, '">', $name,
                '</a></td><td><input type="submit" name="delete-', $i,
                '" value="Del"></td><td>';
            if ($i < count($stories)) echo '<input type="submit" name="next-',
                $i, '" value="&gt;">';
            echo '</td><td>';
            if ($i < (count($stories) - 1)) echo '<input type="submit" ',
                'name="last-', $i, '" value="&gt;&gt;">';
            echo "</td></tr>\n";
        }

        echo "</table>\n";
    }

    mysql_free_result($result);
    echo "</form>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
