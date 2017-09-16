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
### FILE: admin/file.php
###
### This page displays the details of a file and allows those details to be
### updated, as well as handling the upload of files.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('config.php');
    require_once('date.php');
    require_once('numbers.php');
    require_once('rebuild.php');

    $backlinks[] = array('All Authors', 'authors.php');

    function have_uploaded_file() {
        return(is_uploaded_file($_FILES['filename']['tmp_name']) &&
            ($_FILES['filename']['size'] > 0));
    }

    function process_uploaded_file($file) {
        $in = fopen($_FILES['filename']['tmp_name'], 'r');
        $out = fopen('../files/' . ($file % 10) . '/' . (($file / 10) % 10) .
            '/' . $file, 'w');
        $state = 0;
        $count = 0;

        while (!feof($in)) {
            $line = fgets($in);

            if (preg_match('/^\s*$/', $line)) {
                if ($state == 1) $state = 2;
            } else {
                if (($state == 2) && !preg_match('/^\s*<(\/?p|br)[ \/>]/i',
                    $line)) fputs($out, '<p>');

                if (preg_match('/<(\/?p|br)[^>]*>\s*$/i', $line)) {
                    $state = 0;
                } else {
                    $state = 1;
                }
            }

            $count += count(preg_split('/\s+/', strip_tags($line), -1,
                PREG_SPLIT_NO_EMPTY));

            fputs($out, $line);
        }

        fclose($in);
        fclose($out);

        return $count;
    }


    if ($_REQUEST['original']) {
        $file = (int)$_REQUEST['file'];
        include('../files/' . ($file % 10) . '/' . (($file / 10) % 10) . '/'
            . $file);
        exit(0);
    } elseif ($_POST['add']) {
        $result = mysql_query('
            SELECT          fa_story.story,
                            fa_story.name,
                            fa_story.chapter,
                            fa_series.series,
                            fa_series.name,
                            fa_author.author,
                            fa_author.name
            FROM            fa_story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              LEFT JOIN     fa_series
                ON          fa_series.series = fa_story.series
            WHERE           fa_story.story = ' . (int)$_REQUEST['story']
        );

        if (list($story, $story_name, $chapter, $series, $series_name, $author,
                $author_name) = mysql_fetch_row($result)) {
            mysql_free_result($result);
        } else {
            admin_error('Story Not Found',
                'No story could be found with the ID supplied.');
        }

        if (have_uploaded_file()) {
            $name = $_POST['name'];
            $notes = $_POST['notes'];
            $rating = (int)$_POST['rating']; #TODO verify
            $number = (int)$_POST['addas'];

            $result = mysql_query("
                SELECT      number
                FROM        fa_file
                WHERE       story = $story
                ORDER BY    number DESC
                LIMIT       1
            ");

            if ($chapter) {
                if ($number == $fa_fileno_prologue) {
                    if (mysql_num_rows($result) > 0) admin_error(
                        'Bad Chapter Number', 'A prologue cannot be added to '
                        . 'a story with existing chapters.');
                } else {
                    if (mysql_num_rows($result) > 0)  {
                        list($last) = mysql_fetch_row($result);
                    } else {
                        $last = 0;
                    }

                    if ($last == $fa_fileno_epilogue) {
                        admin_error('Bad Chapter Number', 'You cannot add ' .
                            'chapters to a story which already has an ' .
                            'epilogue.');
                    } elseif (($number != $fa_fileno_epilogue) &&
                            ($number != ($last + 1))) {
                        admin_error('Bad Chapter Number', 'The chapter number '
                            . 'given does not make sense.');
                    }
                }
            } else {
                if (mysql_num_rows($result) > 0) {
                    admin_error('File Already Exists',
                        'The file for this story has already been uploaded.');
                } else {
                    $number = 'NULL';
                }
            }

            mysql_free_result($result);
            mysql_query("
                INSERT INTO fa_file
                            (story, number, name, notes, rating)
                VALUES      ($story, $number, '" .
                    mysql_escape_string($name) . "', '" .
                    mysql_escape_string($notes) . "', $rating)
            ");

            $file = mysql_insert_id();
            $size = process_uploaded_file($file);

            mysql_query("
                UPDATE  fa_file
                SET     updated = NOW(),
                        size    = $size
                WHERE   file    = $file
            ");

            mysql_query("
                REPLACE INTO    fa_alert
                                (file, user, time)
                VALUES          ($file, " . $_SESSION['user'] . ', NOW())
            ');

            rebuild_story($story);
        } else {
            admin_error('No File Uploaded', 'You have to actually upload a ' .
                'file to add it...');
        }
    } else {
        $result = mysql_query('
            SELECT          fa_file.file,
                            fa_file.number,
                            fa_file.name,
                            fa_file.notes,
                            fa_file.rating,
                            fa_file.size,
                            fa_file.updated,
                            fa_story.story,
                            fa_story.name,
                            fa_story.chapter,
                            fa_series.series,
                            fa_series.name,
                            fa_author.author,
                            fa_author.name
            FROM            fa_file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              LEFT JOIN     fa_series
                ON          fa_series.series = fa_story.series
            WHERE           fa_file.file = ' . (int)$_REQUEST['file']
        );

        if (list($file, $number, $name, $notes, $rating, $size, $updated,
                $story, $story_name, $chapter, $series, $series_name, $author,
                $author_name) = mysql_fetch_row($result)) {
            mysql_free_result($result);
        } else {
            admin_error('File Not Found', 'No file could be found with the ID '
                . 'supplied.');
        }

        if ($_POST['update']) {
            $name = $_POST['name'];
            $notes = $_POST['notes'];
            $rating = (int)$_POST['rating']; #TODO verify

            if (have_uploaded_file()) {
                $updated = 'NOW()';
                $size = process_uploaded_file($file);
            } else {
                $updated = "'$updated'";
            }

            mysql_query("
                UPDATE  fa_file
                SET     name    = '" . mysql_escape_string($name) . "',
                        notes   = '" . mysql_escape_string($notes) . "',
                        rating  = $rating,
                        updated = $updated,
                        size    = $size
                WHERE   file    = $file
            ");

            rebuild_story($story);
        }
    }

    $backlinks[] = array('Author: ' . $author_name, 'author.php?author='
        . $author);
    if ($series) $backlinks[] = array('Series: ' . $series_name,
        'series.php?series=' . $series);
    $backlinks[] = array('Story: ' . $story_name, 'story.php?story=' . $story);

    $file_title = $story_name;
    if ($chapter) $file_title .= ": " . chapter_name($name, $chapter, $number);
    $page_title = "Admin - File - $file_title";
    include('header.php');
?>
<h2>File info: <?=$file_title?></h2>
<p><a href="../file.php?file=<?=$file?>">View the public page for this file</a><br>
<a href="file.php?file=<?=$file?>&amp;original=1">Download the original uploaded file</a></p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . "</p>\n") : '')?>
<h3>Update File Details</h3>
<p>This is where you can amend the details for this file, or upload an updated
    version of it. Only select the file to upload if you are actually uploading
    a new file; leave it blank if you're just amending the other details.</p>
<form action="file.php?file=<?=$file?>" method="post" enctype="multipart/form-data">
<table class="info">
<tr><th>File to Upload</th><td><input type="file" name="filename" size="40"></td></tr>
<tr><th>Chapter Name</th><td><input name="name" value="<?=$name?>" size="50"></td></tr>
<tr class="multiline"><th>Author Notes</th><td><textarea name="notes" cols="50" rows="5"><?=$notes?></textarea></td></tr>
<tr><th>Last Updated</th><td><?=long_date($updated, TRUE)?></td></tr>
<tr><th>Size</th><td><?=$size?></td></tr>
<tr><th>Rating</th><td><select name="rating">
<?php
        $result = mysql_query('
            SELECT      rating,
                        name
            FROM        fa_rating
            ORDER BY    rating DESC
        ');

        while (list($rating_id, $rating_name) = mysql_fetch_row($result)) {
            echo '<option value="', $rating_id, '"';
            if ($rating_id == $rating) echo ' selected';
            echo '>', $rating_name, "</option>\n";
        }

        mysql_free_result($result);
?>
</select></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update File"></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
