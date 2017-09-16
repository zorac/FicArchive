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
### FILE: admin/index.php
###
### This page displays the main index for the administration area.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');

    $page_title = 'Administration';
    include('header.php');
?>
<h2>Administration Pages</h2>
<p>Welcome to the <?=$fa_site_name?> administration pages!</p>
<?php
    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_alert
    ');

    list($alerts) = mysql_fetch_row($result);
    if ($alerts > 0) echo '<p><a href="alerts.php">There ', (($alerts == 1)
        ? 'is 1' : "are $alerts"), ' pending email alert', (($alerts == 1)
        ? '.' : 's.'), "</a></p>\n";
    mysql_free_result($result);
?>
<h3>Content Management</h3>
<dl>
<dt><a href="authors.php">Authors, Stories, etc</a>
<dd>Management of authors and their stories and series, and the uploading of
    new files - all the main content is accessed through here.
<dt><a href="characters.php">Characters</a>
<dd>A database of characters is maintained as opposed to a list of pairings -
    update their information here.
<dt><a href="categories.php">Categories</a>
<dd>Manage the list of categories into which stories can be organised.
<dt><a href="ratings.php">Ratings</a>
<dd>Ratings are used to advise readers on content and control age-restricted
    access.
<dt><a href="boards.php">Message Boards</a>
<dd>Add to or update the list of message boards.
</dl>
<h3>User Administration</h3>
<dl>
<dt><a href="users.php">Users</a>
<dd>Look up a user's details, reset their password and so on.
<?php
    if (fa_has_admin_access()) {
?>
<dt><a href="admins.php">Mods and Admins</a>
<dd>View and update the list of moderators and administrators.
<?php
    }
?>
<dt><a href="lists.php">Mailing Lists</a>
<dd>Manage users with mailing-list style alerts.
</dl>
<h3>Other Tools</h3>
<dl>
<dt><a href="alerts.php">Send out New Story Alerts</a>
<dd>Once you have finished uploading a batch of new stories, go to this page to
    send out the appropriate email alerts.
<dt><a href="backup.php">Backup Management</a>
<dd>This allows you to generate and manage backup dumps of the database, etc.
<dt><a href="rebuild.php">Rebuild Cached Information</a>
<dd>If searching and browsing seems to be returning out-of-date or incorrect
    information, you can rebuild all of the cached information in the database.
<dt><a href="stats.php">Pointlness Statistics</a>
<dd>A thrilling list of how many stories/users/whatever there are on the site.
</dl>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
