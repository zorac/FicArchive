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
### FILE:       lib/categories.php
###
### This file provides handy functions for working with categories.
###############################################################################
###############################################################################
    require_once('init.php');

###############################################################################
# FUNCTION:     get_category_cache
#
# ARGS:         None
#
# RETURNS:      Nothing
#
# This function loads the list of categories from the database into a cache
# variable.
###############################################################################
    function get_category_cache() {
        global $category_cache;

        if (!isset($category_cache)) {
            $category_cache = array();
            $result = mysql_query('
                SELECT      category,
                            name
                FROM        fa_category
                ORDER BY    name
            ');

            while (list($id, $name) = mysql_fetch_row($result)) {
                $category_cache[$id] = $name;
            }

            mysql_free_result($result);
        }
    }

###############################################################################
# FUNCTION:     category_id_to_name
#
# ARGS: id      A category ID
#
# RETURNS:      The category name
#
# Does exactly what it says on the tin.
###############################################################################
    function category_id_to_name($id) {
        global $category_cache;

        return $category_cache[$id];
    }

###############################################################################
# FUNCTION:     category_ids_to_names
#
# ARGS: ids     Comma-seperated category IDs
#
# RETURNS:      The category names
#
# Does exactly what it says on the tin.
###############################################################################
    function category_ids_to_names($ids) {
        global $category_cache;

        get_category_cache();
        $categories = array_map('category_id_to_name', explode(',', $ids));
        sort($categories, SORT_STRING);
        return(implode(', ', $categories));
    }

###############################################################################
# FUNCTION:     category_checkboxes
#
# ARGS: selected Associative array with selected category IDs as keys
#
# RETURNS:      HTML code for the checkboxes
#
# Generates HTML code for category checkboxes with specified ones pre-selected.
###############################################################################
    function category_checkboxes($selected) {
        global $category_cache;

        get_category_cache();
        $boxes = array();
        reset($category_cache);

        while (list($category, $name) = each($category_cache)) {
            $boxes[] = '<td><input type="checkbox" name="category-' . $category
                . '" id="category-' . $category . ($selected[$category] ?
                '" checked>' : '">') . '</td><td><label for="category-'
                . $category . '">' . $name . '</label></td>';
        }

        $output = '<table class="checkboxes">';
        $half = (int)((count($boxes) + 1) / 2);

        for ($i = 0; $i < $half; $i++) {
            $output .= "\n<tr>" . $boxes[$i];
            if ($boxes[$i + $half]) $output .= $boxes[$i + $half];
            $output .= '</tr>';
        }

        return($output . "\n</table>");
    }

###############################################################################
# FUNCTION:     find_categories
#
# ARGS: vars    An associative array
#
# RETURNS:      An array of category IDs
#
# Extracts category IDs from $vars (which would normally be $_POST).
###############################################################################
    function find_categories($vars) {
        $output = array();

        foreach ($vars as $key => $value) {
            if ($value) {
                $key = explode('-', $key);

                if (($key[0] == 'category') && is_numeric($key[1]))
                    $output[$key[1]] = $key[1];
            }
        }

        return($output);
    }

###############################################################################
# FUNCTION:     get_story_categories
#
# ARGS: story   The story ID
#       names   TRUE to return category names, FALSE to return IDs
#
# RETURNS:      An array of category names or IDs
#
# Returns an array of the categories for the given story.
###############################################################################
    function get_story_categories($story, $names) {
        $categories = array();

        if ($names) {
            $result = mysql_query('
                SELECT          fa_category.category,
                                fa_category.name
                FROM            fa_category
                  INNER JOIN    fa_story_category
                    ON          fa_story_category.category
                                    = fa_category.category
                      AND       fa_story_category.story = ' . (int)$story . '
                ORDER BY        fa_category.name
            ');

            while (list($category, $name) = mysql_fetch_row($result)) {
                $categories[$category] = $name;
            }
        } else {
            $result = mysql_query('
                SELECT      category
                FROM        fa_story_category
                WHERE       story = ' . (int)$story . '
                ORDER BY    category
            ');

            while (list($category) = mysql_fetch_row($result)) {
                $categories[$category] = $category;
            }
        }

        mysql_free_result($result);

        return($categories);
    }
###############################################################################
###############################################################################
?>
