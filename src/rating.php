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
### FILE: rating.php
###
### This page displays the list of story ratings and highlights a selected one.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('init.php');

    $page_title = 'Story Ratings';
    include('header.php');
    echo "<h2>Story Ratings</h2>\n<table id=\"ratings\">\n";

    $result = mysql_query('
        SELECT      rating,
                    name,
                    age,
                    description
        FROM        fa_rating
        ORDER BY    rating
    ');

    while (list($rating, $name, $age, $description)
            = mysql_fetch_row($result)) {
        $trclass = ($rating == $_REQUEST['rating']) ? ' class="hilite"' : '';
        echo '<tr', $trclass, '><th>', $name, '</th><td>', $description,
            ' You must be at least ', $age,
            ' years old to view stories rated ', $name, ' on this site.',
            "</td></tr>\n";
    }

    echo "</table>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
