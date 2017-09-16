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
### FILE: admin/ratings.php
###
### This page displays the list of ratings.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');

    $page_title = 'Admin - Ratings';
    include('header.php');
?>
<h2>Manage Ratings</h2>
<p>This page allows you to manage the ratings. Click on a rating to edit it.</p>
<?=display_backlinks()?>
<table id="ratings">
<?php
    $result = mysql_query('
        SELECT      rating,
                    name,
                    description
        FROM        fa_rating
        ORDER BY    rating
    ');

    while (list($rating, $name, $description) = mysql_fetch_row($result)) {
        echo '<tr><th><a href="rating.php?rating=', $rating, '">', $name,
            '</a></th><td>', $description, "</td></tr>\n";
    }

    echo "</table>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
