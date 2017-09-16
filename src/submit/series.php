<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2005 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE: submit/series.php
###
### Select a series for a story submission.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('submit.php');

    $page_title = 'Submit a Story - Series';
    include('header.php');

    echo "<h2>Submit a Story</h2>\n";

    if ($submit['author']) {
        submit_links('', 'series');
    } else {
        echo "<p>Start your story submission here!</p>\n";
    }
?>
<h3>Step 1: Select an Author</h3>
<table class="info">
<?php
    $result = mysql_query('
        SELECT          fa_author.author,
                        fa_author.name
        FROM            fa_author_user
          INNER JOIN    fa_author
            ON          fa_author.author = fa_author_user.author
        WHERE           fa_author_user.user = ' . $_SESSION['user'] . '
          AND           fa_author_user.verify IS NULL
        ORDER BY        fa_author.name
    ');

    if (mysql_num_rows($result) > 0) {
        $class = ($submit['author'] && ($submit['author']['type'] == 'old')) ?
            'selected_title' : 'title';
?>
<form method="post" action="series.php">
<input type="hidden" name="type" value="old">
<tr class="<?=$class?>"><td></td><td>Existing author</td></tr>
<tr><th>Author name</th><td><select name="author">
<option value="">-- Select an existing author --</option>
<?php
        while (list($id, $name) = mysql_fetch_row($result)) {
            echo '<option value="', $id, '"';
            if ($submit['author'] && ($submit['author']['id'] == $id))
                echo ' selected';
            echo ">$name</option>\n";
        }
    }
?>
</select></td></tr>
<tr><td></td><td><input type="submit" name="set_author" value="Use this author &gt;"></td></tr>
</form>
<tr><td colspan="2"><hr></td></tr>
<?php
    mysql_free_result($result);

    if ($submit['author'] && ($submit['author']['type'] == 'new')) {
        $class = "selected_title";
        $name = $submit['author']['name'];
        $email = $submit['author']['email'];
    } else {
        $class = "title";
        $name = $_SESSION['username'];
        $email = $_SESSION['email'];
    }
?>
<tr class="<?=$class?>"><td></td><td>New author</td></tr>
<form method="post" action="series.php">
<input type="hidden" name="type" value="new">
<tr><th>Author name</th><td><input name="name" size="40" value="<?=$name?>"></td></tr>
<tr><th>Email address</th><td><input name="email" size="40" value="<?=$email?>"></td></tr>
<tr><td></td><td><input type="submit" name="set_author" value="Create new author &gt;"></td></tr>
</form>
</table>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
