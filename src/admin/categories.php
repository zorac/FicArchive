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
### FILE: admin/categories.php
###
### This page displays the list categories.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');

    $page_title = 'Admin - Categories';
    include('header.php');
?>
<h2>Category Administration</h2>
<p>This area allows you to manage the story categories in the archive.</p>
<?=display_backlinks()?>
<?php
    $result = mysql_query("
        SELECT      category,
                    name
        FROM        fa_category
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Manage an Existing Category</h3>
<p>To update an existing category, select its name from the list below and hit Go.</p>
<form action="category.php" method="get">
<select name="category">
<option value="0">-- Select a Category --</option>
<?php
        while (list($category, $name) = mysql_fetch_row($result)) {
            echo '<option value="', $category, '">', $name, "</option>\n";
        }

        echo '</select> <input type="submit" value="Go">', "\n</form>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Category</h3>
<p>If you need to add a new category to the site, enter a name for it below and hit Add.</p>
<form action="category.php" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" size="40"></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add new Category"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
