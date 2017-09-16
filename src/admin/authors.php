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
### FILE: admin/authors.php
###
### This page displays the list of authors.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('init.php');

    $page_title = "Admin - Authors";
    include('header.php');
?>
<h2>Author Administration</h2>
<p>This area allows you to manage the authors in the archive. Within an
    author's area, you can manage their stories and series, and the file or
    files for each story.</p>
<?=display_backlinks()?>
<?php
    $result = mysql_query("
        SELECT      author,
                    name
        FROM        fa_author
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Manage an Existing Author</h3>
<p>To update an existing author, or to add or modify stories, chapters or
    series, select their name from the list below and hit Go.</p>
<form action="author.php" method="get">
<select name="author">
<option value="0">-- Select an Author --</option>
<?php
        while (list($author, $name) = mysql_fetch_row($result)) {
            echo '<option value="', $author, '">', $name, "</option>\n";
        }

        echo '</select> <input type="submit" value="Go">', "\n</form>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Author</h3>
<p>If you need to add a new author to the site, enter their name and email
    address below and hit Add.</p>
<form action="author.php" method="post">
<table class="info">
<tr><th>Name</th><td><input name="name" size="40"></td></tr>
<tr><th>Email</th><td><input name="email" size="40"></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add new Author"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
