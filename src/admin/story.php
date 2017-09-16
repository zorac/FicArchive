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
### FILE: admin/story.php
###
### This page displays the details of a story and allows it to be updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('categories.php');
    require_once('date.php');
    require_once('init.php');
    require_once('numbers.php');
    require_once('pairings.php');
    require_once('rebuild.php');

    function add_new_pairings($story, $pairings) {
        global $error;

        $pairdetails = find_exact_pairings($pairings);

        foreach (array_keys($pairdetails['pairings']) as $pairing) {
            mysql_query("
                INSERT INTO fa_story_pairing
                VALUES      ($story, $pairing)
            ");
        }

        if ($pairdetails['badpairs']) {
            $error = "Some pairings could not be added (";
            $error .= implode(", ", array_keys($pairdetails['badpairs']));
            $error .= ") because the following nicknames were not found: ";
            $error .= implode(", ", array_keys($pairdetails['badnicks']));
        }

        return $pairdetails['pairings'];
    }

    function add_story_form($addas) {
        global $chapter, $story;
?>
<form action="file.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="story" value="<?=$story?>">
<table class="info">
<tr><th>File to Upload</th><td><input type="file" name="filename" size="40"></td></tr>
<?php
        if ($addas) {
?>
<tr><th>Chapter Name</th><td><input name="name" size="50"></td></tr>
<?php
        }
?>
<tr class="multiline"><th>Author Notes</th><td><textarea name="notes" cols="50" rows="5"></textarea></td></tr>
<tr><th>Rating</th><td><select name="rating">
<?php
        $result = mysql_query('
            SELECT      rating,
                        name
            FROM        fa_rating
            ORDER BY    rating DESC
        ');

        while (list($rating, $name) = mysql_fetch_row($result)) {
            echo '<option value="', $rating, '">', $name, "</option>\n";
        }

        mysql_free_result($result);
        echo "</select></td></tr>\n";

        if ($addas) {
            echo '<tr><th>Add As</th><td><select name="addas">';

            foreach ($addas as $number) {
                echo '<option value="', $number, '">', chapter_name('',
                    $chapter, $number), '</option>';
            }

            echo "</select></td></tr>\n";
        }
?>
<tr><td></td><td><input type="submit" name="add" value="Add File"></tr>
</table>
</form>
<?php
    }

    function change_type_form($value, $text) {
        global $story;
?>
<form action="story.php?story=<?=$story?>" method="post">
<input type="hidden" name="makechaptered" value="<?=$value?>">
<input type="submit" value="<?=$text?>">
</form>
<?php
    }

    $backlinks[] = array('All Authors', 'authors.php');

    if ($_POST['add']) {
        $title = trim($_POST['name']);
        $summary = trim($_POST['summary']);

        if (!$title) {
            admin_error('header.php',
                'You must supply a title to add a new story.');
        }

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
            mysql_free_result($result);
            admin_error('No Author Specified',
                'No author ID was given when attempting to create a story.');
        }

        $result = mysql_query("
            INSERT INTO fa_story
                        (author, name, summary)
            VALUES      ($author, '" . mysql_escape_string($title)
                        . "', '" . mysql_escape_string($summary) . "')
        ");

        if ($result) {
            $story = mysql_insert_id();
        } else {
            admin_error('Title Already Exists',
                'This author already has a story with that title.');
        }

        $categories = find_categories($_POST);

        foreach (array_keys($categories) as $category) {
            mysql_query("
                INSERT INTO fa_story_category
                VALUES      ($story, $category)
            ");
        }

        $pairings = add_new_pairings($story, $_POST['pairings']);
        rebuild_story($story);
    } else {
        $result = mysql_query('
            SELECT          fa_story.story,
                            fa_story.name,
                            fa_story.summary,
                            fa_author.author,
                            fa_author.name,
                            fa_rating.name,
                            fa_story.updated,
                            fa_story.chapter,
                            fa_story.hidden,
                            fa_series.series,
                            fa_series.name
            FROM            fa_story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              LEFT JOIN     fa_rating
                ON          fa_rating.rating = fa_story.rating
              LEFT JOIN     fa_series
                ON          fa_series.series = fa_story.series
            WHERE           fa_story.story = ' . (int)$_REQUEST['story']
        );

        if (list($story, $title, $summary, $author, $name, $rating, $updated,
                $chapter, $hidden, $series, $series_name)
                    = mysql_fetch_row($result)) {
            mysql_free_result($result);
        } else {
            admin_error('Story Not Found',
                'No story could be found with the ID supplied.');
        }

        $backlinks[] = array('Author: ' . $name, 'author.php?author='
            . $author);
        if ($series) $backlinks[] = array('Series: ' . $series_name,
            'series.php?series=' . $series);
        $categories = get_story_categories($story, FALSE);
        $pairings = get_story_pairings($story, FALSE, $null);

        if ($_POST['update']) {
            $new_title = trim($_POST['name']);
            $new_summary = trim($_POST['summary']);

            if ($new_title) {
                $result = mysql_query("
                    UPDATE fa_story
                    SET    name = '" . mysql_escape_string($new_title) . "',
                           summary = '" . mysql_escape_string($new_summary) . "'
                    WHERE  story = $story
                ");

                if ($result) {
                    $title = $new_title;
                    $summary = $new_summary;
                } else {
                    $error = 'The story could not be renamed.';
                }
            } else {
                $error = 'No new title was supplied.';
            }

            $new_categories = find_categories($_POST);

            foreach (array_keys($new_categories) as $category) {
                if (!$categories[$category]) {
                    $categories[$category] = 1;

                    mysql_query("
                        INSERT INTO fa_story_category
                        VALUES      ($story, $category)
                    ");
                }
            }

            foreach (array_keys($categories) as $category) {
                if (!$new_categories[$category]) {
                    $categories[$category] = 0;

                    mysql_query("
                        DELETE FROM fa_story_category
                        WHERE       story = $story
                          AND       category = $category
                    ");
                }
            }

            $new_pairings = find_pairings($_POST);

            foreach (array_keys($pairings) as $pairing) {
                if (!$new_pairings[$pairing]) {
                    unset($pairings[$pairing]);

                    mysql_query("
                        DELETE FROM fa_story_pairing
                        WHERE       story = $story
                          AND       pairing = $pairing
                    ");
                }
            }

            $pairings += add_new_pairings($story, $_POST['pairings']);
            rebuild_story($story);
        } elseif ($chapter && $_POST['chapter']) {
            $chapter = $_POST['chapter'];

            mysql_query("
                UPDATE  fa_story
                SET     chapter = '" . mysql_escape_string($chapter) . "'
                WHERE   story = $story
            ");
        } elseif ($_POST['makechaptered']) {
            if ($chapter) {
                if ($_POST['makechaptered'] == 'single') {
                    $result = mysql_query("
                        SELECT  file
                        FROM    fa_file
                        WHERE   story = $story
                    ");

                    if (mysql_num_rows($result) > 1) {
                        $error = 'You cannot convert a story with multiple ' .
                            'files into a one-shot fic.';
                    } else {
                        if (mysql_num_rows($result) == 1) {
                            mysql_query("
                                UPDATE  fa_file
                                SET     number = NULL
                                WHERE   story = $story;
                            ");
                        }

                        mysql_free_result($result);

                        mysql_query("
                            UPDATE  fa_story
                            SET     chapter = ''
                            WHERE   story = $story
                        ");

                        $chapter = "";
                    }
                }
            } elseif (($_POST['makechaptered'] == 'prologue') ||
                    ($_POST['makechaptered'] == 'one')) {
                $chapter = "Chapter";

                mysql_query("
                    UPDATE  fa_story
                    SET     chapter = 'Chapter'
                    WHERE   story = $story
                ");

                mysql_query("
                    UPDATE  fa_file
                    SET     number = " . (($_POST['makechaptered'] == 'one')
                                            ? 1 : $fa_fileno_prologue). "
                    WHERE   story = $story
                      AND   number IS NULL
                ");
            };

            rebuild_story($story);
        } elseif ($_POST['hide']) {
            mysql_query("
                UPDATE  fa_story
                SET     hidden = (hidden | 1)
                WHERE   story = $story
            ");

            $hidden |= 1;
        } elseif ($_POST['unhide']) {
            mysql_query("
                UPDATE  fa_story
                SET     hidden = 0
                WHERE   story = $story
            ");

            $hidden = 0;
        }
    }

    $page_title = "Admin - Story - $title";
    include('header.php');
?>
<h2>Story info: <?=$title?></h2>
<p><a href="../story.php?story=<?=$story?>">View the public page for this story</a></p>
<?php
    echo display_backlinks();
    if ($error) echo '<p class="error">' . $error . "</p>\n";

    if ($hidden) {
?>
<h3>This Story is Hidden</h3>
<p>This story has been hidden and will not be publically visible. You can
    un-hide this story if hising is no longer neccessary.</p>
<form action="story.php?story=<?=$story?>" method="post">
<input type="submit" name="unhide" value="Un-hide this Story">
</form>
<?php
    }
?>
<h3>Update Story Details</h3>
<p>Here you can amend the details for this story. Existing pairings can be
    removed by unchecking them; new ones can be entered into the text box
    below, comma-separated.</p>
<form action="story.php?story=<?=$story?>" method="post">
<table class="info">
<tr><th>Title</th><td><input name="name" value="<?=$title?>" size="60"></td></tr>
<?=($rating ? "<tr><th>Rating</th><td>$rating</td></tr>\n" : '')?>
<?=($updated ? '<tr><th>Updated</th><td>' . long_date($updated, TRUE) . "</td></tr>\n" : '')?>
<tr class="multiline"><th>Summary</th><td><textarea name="summary" cols="60" rows="5"><?=$summary?></textarea></td></tr>
<tr class="multiline"><th>Categories</th><td><?=category_checkboxes($categories)?></td></tr>
<tr class="multiline"><th>Pairings</th><td><?=pairing_checkboxes($pairings)?></td></tr>
<tr><td></td><td><input name="pairings" size="60"></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update Story"></td></tr>
</table>
</form>
<?php
    if ($chapter) {
        $result = mysql_query("
            SELECT      file,
                        name,
                        number
            FROM        fa_file
            WHERE       story = $story
            ORDER BY    number
        ");

        $numfiles = 0;

        if (mysql_num_rows($result) > 0) {
?>
<h3>Manage an Existing File</h3>
<p>If you need to re-upload one of the files for this story, or simply need to
    amend the author's notes or rating, select it from the list and hit Go.</p>
<form action="file.php" method="get">
<select name="file">
<option value="">-- Select a File --</option>
<?php
            while (list($file, $name, $number) = mysql_fetch_row($result)) {
                $numfiles++;
                echo '<option value="', $file, '">', chapter_name($name,
                    $chapter, $number), "</option>\n";
                $lastfile = $number;
            }

?>
</select> <input type="submit" value="Go">
</form>
<?php
        }

        mysql_free_result($result);

        if ($lastfile != $fa_fileno_epilogue) {
?>
<h3>Add a New File</h3>
<p>Use this form to upload a new <?=$chapter?> to this story. The file you
    upload should contain only the story text - other details are added
    automatically from the information you enter below and the story details
    above. The chapter name is optional, normally you can leave this blank.</p>
<?php
            if ($numfiles) {
                add_story_form(array($lastfile + 1, $fa_fileno_epilogue));
            } else {
                add_story_form(array(1, $fa_fileno_prologue));
            }
        }
?>
<h3>Update Chapter Naming</h3>
<p>It's possible to call chapters something other than chapters. What they are
    called can be changed below.</p>
<form action="story.php?story=<?=$story?>" method="post">
<input name="chapter" size="20" value="<?=$chapter?>"> <input type="submit" value="Update">
</form>
<?php
        if ($numfiles < 2) {
?>
<h3>Story Type</h3>
<p>This story is currently a multi-chaptered fic - you have the option to
    convert it back to a one-shot fic.</p>
<?php
            change_type_form("single", "Make this a one-shot story");
        }
    } else {
        $result = mysql_query("
            SELECT  file
            FROM    fa_file
            WHERE   story = $story
              AND   number IS NULL
        ");

        if (list($file) = mysql_fetch_row($result)) {
?>
<h3>Manage the File for this Story</h3>
<p>If needed, you can re-upload the file for this story. You can also adjust
    the author's notes, rating etc.</p>
<p><a href="file.php?file=<?=$file?>">Manage the file for this story</a></p>
<h3>Story Type</h3>
<p>This story is currently a one-shot fic - you have the option to convert it
    to a multi-chaptered fic. The existing file can become either the prologue
    or the first chapter.</p>
<?php
            change_type_form("prologue",
                "Make this a chaptered story (as Prologue)");
            change_type_form("one",
                "Make this a chaptered story (as Chapter 1)");
        } else {
?>
<h3>Add the File for this Story</h3>
<p>You need to upload the file for a story before it is visible on the site.
    The file you upload should contain only the story text - other details are
    added automatically from the information you enter below and the story
    details above.</p>
<?php
    add_story_form(array())
?>
<h3>Story Type</h3>
<p>This story is currently a one-shot fic - you have the option to convert it
    to a multi-chaptered fic.</p>
<?php
            change_type_form("one", "Make this a chaptered story");
        }

        mysql_free_result($result);
    }

    if (!$hidden) {
?>
<h3>Hide Story</h3>
<p>This story is currently publically visible. It's possible to hide it so that
    it can no longer be read.</p>
<form action="story.php?story=<?=$story?>" method="post">
<input type="submit" name="hide" value="Hide this Story">
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
