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
### FILE:       lib/characters.php
###
### This file provides handy functions for working with characters.
###############################################################################
###############################################################################
    require_once('config.php');
    require_once('init.php');

###############################################################################
# FUNCTION:     add_names
#
# ARGS: person  The ID of a person
#       names   An array of names to add
#
# RETURNS:      A success/failure message
#
# This function adds additional nicknames to a character.
###############################################################################
    function add_names($person, $names) {
        $added = array();
        $failed = array();
        $output = '';

        foreach (explode(',', $names) as $name) {
            $name = trim($name);

            $result = mysql_query('
                INSERT INTO fa_nickname
                            (person, name)
                VALUES      (' . $person . ", '" .
                                 mysql_escape_string($name) . "')
            ");

            if ($result) {
                $added[] = $name;
            } else {
                $failed[] = $name;
            }
        }

        if ($added) {
            $output .= 'The following additional names were added: ' .
                implode(', ', $added) . ".\n";
        }

        if ($failed) {
            $output .= 'The following additional names were already in ' .
                'use: ' . implode(', ', $failed) . ".\n";
        }

        return $output;
    }

###############################################################################
# FUNCTION:     gender_select
#
# ARGS: selected The currently selected gender
#
# RETURNS:      An HTML select box
#
# This function generates an HTML select box for genders.
###############################################################################
    function gender_select($selected) {
        global $fa_gender_list;

        $output = '<select name="gender">' . ($selected ? "\n"
            : "\n<option></option>\n");

        reset($fa_gender_list);

        while (list($i, $gender) = each($fa_gender_list)) {
            $output .= '<option value="' . $gender{0}
                . (($gender{0} == $selected{0}) ? '" selected>' : '">')
                . $gender . "</option>\n";
        }

        return($output . "</select>");
    }
###############################################################################
###############################################################################
?>
