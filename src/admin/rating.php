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
### FILE: admin/rating.php
###
### This page displays the details of a rating and allows it to be updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');
    require_once('numbers.php');

    $backlinks[] = array('Rating Admin', 'ratings.php');

    if ($_POST['add']) {
        admin_error('Not Yet Implemented', "You can't add ratings yet!");
    } else {
        $result = mysql_query('
            SELECT  rating,
                    name,
                    age,
                    description
            FROM    fa_rating
            WHERE   fa_rating.rating = ' . (int)$_REQUEST['rating']
        );

        if (list($rating, $name, $age, $description)
                = mysql_fetch_row($result)) {
            mysql_free_result($result);
            $result = mysql_query("
                SELECT  count(story)
                FROM    fa_story
                WHERE   rating = $rating
            ");

            list($stories) = mysql_fetch_row($result);
            mysql_free_result($result);
            $result = mysql_query("
                SELECT  count(file)
                FROM    fa_file
                WHERE   rating = $rating
            ");

            list($files) = mysql_fetch_row($result);
            mysql_free_result($result);
        } else {
            admin_error('Rating Not Found',
                'The rating you requested does not exist.');
        }

        if ($_POST['update']) {
            $new_name = trim($_POST['name']);
            $new_age = (int)$_POST['age'];
            $new_description = trim($_POST['description']);

            if ($new_name) {
                $result = mysql_query("
                    UPDATE  fa_rating
                    SET     name = '" . mysql_escape_string($new_name)
                            . "', age = $new_age, description = '"
                            . mysql_escape_string($new_description) . "'
                    WHERE   rating = $rating
                ");

                if ($result) {
                    $name = $new_name;
                    $age = $new_age;
                    $description = $new_description;
                } else {
                    $result = mysql_query("
                        SELECT  rating
                        FROM    fa_rating
                        WHERE   name = '" . mysql_escape_string($new_name) . "'
                    ");

                    list($new_rating) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    $error = '<a href="' . $_SERVER['PHP_SELF'] . '?rating='
                        .  $new_rating . '">A rating with the name '
                        .  $new_name . ' already exists.</a>';
                }
            } else {
                $error = "No new name was supplied.";
            }
        }
    }

    $page_title = "Admin - Rating - $name";
    include('header.php');
?>
<h2>Rating Info: <?=$name?></h2>
<p><a href="../ratings/rating.php?rating=<?=$rating?>">View this rating</a></p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . '</p>') : '')?>
<h3>Update Rating Details</h3>
<p>Please avoid changing the names of ratings which are in use (other than to
    correct typographical or spelling errors).</p>
<form action="rating.php?rating=<?=$rating?>" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" value="<?=$name?>" size="60"></td></tr>
<tr><th>Minimum Age</th><td><input name="age" value="<?=$age?>" size="4"></td></tr>
<tr class="multiline"><th>Description</th><td><textarea name="description" rows="5" cols="60"><?=$description?></textarea></td></tr>
<tr><th>Stories</th><td><?=nice_count($stories)?></td></tr>
<tr><th>Files</th><td><?=nice_count($files)?></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update this Rating"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
