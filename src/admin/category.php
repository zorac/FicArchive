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
### FILE: admin/category.php
###
### This page displays the details of a category and allows those details to be
### updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');
    require_once('numbers.php');

    $backlinks[] = array('Category Admin', 'categories.php');

    if ($_POST['add']) {
        $name = trim($_POST['name']);
        $stories = 0;

        if (!$name) admin_error('No Name Supplied',
            'You must supply a name to create a new category.');

        $result = mysql_query("
            INSERT INTO fa_category
                        (name)
            VALUES      ('" . mysql_escape_string($name) . "')
        ");

        if ($result && (mysql_affected_rows() == 1)) {
            $category = mysql_insert_id();
        } else {
            $result = mysql_query("
                SELECT  category
                FROM    fa_category
                WHERE   name = '" . mysql_escape_string($name) . "'
            ");

            list($category) = mysql_fetch_row($result);
            mysql_free_result($result);

            admin_error('Category Already Exists', '<a href="' .
                $_SERVER['PHP_SELF'] . '?category=' . $category .
                '">A category with that name already exists.</a>');
        }
    } else {
        $result = mysql_query('
            SELECT  category,
                    name
            FROM    fa_category
            WHERE   fa_category.category = ' . (int)$_REQUEST['category']
        );

        if (list($category, $name) = mysql_fetch_row($result)) {
            mysql_free_result($result);
            $result = mysql_query("
                SELECT  count(story)
                FROM    fa_story_category
                WHERE   category = $category
            ");

            list($stories) = mysql_fetch_row($result);
            mysql_free_result($result);
        } else {
            admin_error('Category Not Found',
                'The category you requested does not exist.');
        }

        if ($_POST['update']) {
            $new_name = trim($_POST['name']);

            if ($new_name) {
                $result = mysql_query("
                    UPDATE  fa_category
                    SET     name = '" . mysql_escape_string($new_name) . "'
                    WHERE   category = $category
                ");

                if ($result) {
                    $name = $new_name;
                } else {
                    $result = mysql_query("
                        SELECT  category
                        FROM    fa_category
                        WHERE   name = '" . mysql_escape_string($new_name) . "'
                    ");

                    list($new_category) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    $error = '<a href="' . $_SERVER['PHP_SELF'] . '?category='
                        .  $new_category . '">A category with the name '
                        .  $new_name . ' already exists.</a>';
                }
            } else {
                $error = "No new name was supplied.";
            }
        } elseif ($_POST['remove']) {
            if ($stories == 0) {
                mysql_query("
                    DELETE FROM fa_category
                    WHERE       category = $category
                ");

                $page_title = 'Admin - Category Removed';
                include('header.php');
                echo "<h2>Category Removed</h2>\nThe category '$name' was ",
                    "successfully removed.\n", display_backlinks();
                include('footer.php');
                exit(0);
            } else {
                $error = 'This category cannot be removed as there are still '
                    . $stories . ' stories which reference it (try doing a '
                    . 'search).';
            }
        }
    }

    $page_title = "Admin - Category - $name";
    include('header.php');
?>
<h2>Category info: <?=$name?></h2>
<?=display_backlinks()?>
<?=($error ? ('<p class="error">' . $error . '</p>') : '')?>
<h3>Update Category Details</h3>
<p>Please avoid changing the names of categories which are in use (other than
    to correct typographical or spelling errors).</p>
<form action="category.php?category=<?=$category?>" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" value="<?=$name?>" size="60"></td></tr>
<tr><th>Stories</th><td><?=nice_count($stories)?></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update this Category"></td></tr>
</table>
</form>
<h3>Remove this Category</h3>
<p>You can only remove a category if there are no stories referencing it.</p>
<?php
    if ($stories == 0) {
?>
<form action="category.php?category=<?=$category?>" method="post">
<input type="submit" name="remove" value="Remove this Category">
</table>
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
