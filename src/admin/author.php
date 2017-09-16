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
### FILE: admin/author.php
###
### This page displays the details for an author and allows those details to be
### updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('categories.php');
    require_once('init.php');

    $backlinks[] = array('Author Admin', 'authors.php');

    if ($_POST['add']) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);

        if (!$name || !$email) admin_error('No Name/Email Supplied',
            'You must supply a name and email address to add a new author.');

        $result = mysql_query("
            INSERT INTO fa_author
                        (name, email)
            VALUES      ('" . mysql_escape_string($name) . "', '"
                            . mysql_escape_string($email) . "')
        ");

        if ($result && (mysql_affected_rows() == 1)) {
            $author = mysql_insert_id();
        } else {
            $result = mysql_query("
                SELECT  author
                FROM    fa_author
                WHERE   name = '" . mysql_escape_string($name) . "'
            ");

            list($author) = mysql_fetch_row($result);
            mysql_free_result($result);

            admin_error('Author Already Exists', '<a href="' .
                $_SERVER['PHP_SELF'] . '?author=' . $author . '">An author ' .
                "with that name already exists.</a>");
        }
    } else {
        $result = mysql_query('
            SELECT  author,
                    name,
                    email
            FROM    fa_author
            WHERE   author = ' . (int)$_REQUEST['author']
        );

        if (list($author, $name, $email) = mysql_fetch_row($result)) {
            mysql_free_result($result);
        } else {
            admin_error('Author Not Found',
                'No author was found matching the supplied ID.');
        }

        if ($_POST['update']) {
            $new_name = trim($_POST['name']);
            $new_email = trim($_POST['email']);

            if ($new_name && $new_email) {
                $result = mysql_query("
                    UPDATE  fa_author
                    SET     name = '" . mysql_escape_string($new_name) . "',
                            email = '" . mysql_escape_string($new_email) . "'
                    WHERE   author = $author
                ");

                if ($result) {
                    $name = $new_name;
                    $email = $new_email;
                } else {
                    $result = mysql_query("
                        SELECT  author
                        FROM    fa_author
                        WHERE   name = '" . mysql_escape_string($new_name) . "'
                    ");

                    list($new_author) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    $error = '<a href="' . $_SERVER['PHP_SELF'] . '?author='
                        .  $new_author . '">An author with the name '
                        .  $new_name . ' already exists.</a>';
                }
            } else {
                $error = 'No new name/email was supplied.';
            }
        }
    }

    $page_title = "Admin - Author - $name";
    include('header.php');
?>
<h2>Author profile: <?=$name?></h2>
<p><a href="../author.php?author=<?=$author?>">View the public page for this author</a></p>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . "</p>\n") : '')?>
<h3>Update Author Details</h3>
<p>You can update this author's name or email address if needed.</p>
<form action="author.php?author=<?=$author?>" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" value="<?=$name?>" size="40"></th></tr>
<tr><th>Email</th><td><input name="email" value="<?=$email?>" size="40"></th></tr>
<tr><td></td><td><input type="submit" name="update" value="Update this Author"></td></tr>
</table>
</form>
<?php
    $result = mysql_query("
        SELECT      story,
                    name,
                    hidden
        FROM        fa_story
        WHERE       author = $author
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Manage an Existing Story</h3>
<p>To upload a new chapter or otherwise modify an existing story, select it
    from the list and hit Go.</p>
<form action="story.php" method="get">
<select name="story">
<option value="0">-- Select a Story --</option>
<?php
        while (list($story, $title, $hidden) = mysql_fetch_row($result)) {
            echo '<option value="', $story, ($hidden ? '" class="hidden' : ''),
                '">', $title, "</option>\n";
        }

        echo '</select> <input type="submit" value="Go">', "\n</form>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Story</h3>
<p>To add a new story for this author, enter the title and summary, select the
    apropriate categories and list all pairings included.</p>
<form action="story.php" method="post">
<input type="hidden" name="author" value="<?=$author?>">
<table class="info">
<tr><th>Title</th><td><input name="name" size="60"></td></tr>
<tr class="multiline"><th>Summary</th><td><textarea name="summary" cols="60" rows="5"></textarea></td></tr>
<tr class="multiline"><th>Categories</th><td><?=category_checkboxes(array())?></td></tr>
<tr><th>Pairings</th><td><input name="pairings" size="60"></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add new Story"></td></tr>
</table>
</form>
<?php
    $result = mysql_query("
        SELECT      series,
                    name
        FROM        fa_series
        WHERE       author = $author
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Manage an Existing Series</h3>
<p>To change the story listings etc for an existing, select it from the list
    and hit Go.</p>
<form action="series.php" method="get">
<select name="series">
<option value="0">-- Select a Series --</option>
<?php
        while (list($series, $title) = mysql_fetch_row($result)) {
            echo '<option value="', $series, '">', $title, "</option>\n";
        }

        echo '</select> <input type="submit" value="Go">', "\n</form>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Series</h3>
<p>To add a new series, you need only specify a title.</p>
<form action="series.php" method="post">
<input type="hidden" name="author" value="<?=$author?>">
<table class="info">
<tr><th>Title</th><td><input name="name" size="40"></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add new Series"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
