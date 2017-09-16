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
### FILE:       lib/search.php
###
### This file contains search parsing and display functions.
###############################################################################
###############################################################################
    require_once('categories.php');
    require_once('init.php');
    require_once('pairings.php');

    $fa_search_matching = array(
        'all'   => 'All of',
        'any'   => 'Any of',
        'only'  => 'Only',
        'exact' => 'Exactly'
    );

    $fa_search_leastmost = array(
        'atleast'   => 'At Least',
        'atmost'    => 'At Most'
    );

    $fa_search_sortby = array(
        'title'     => 'Story Title',
        'author'    => "Author's Name",
        'date'      => 'Date of Last Update',
        'size'      => 'Word Count',
        'relevance' => 'Relevance'
    );

    $fa_search_direction = array(
        ''  => 'Normal',
        '1' => 'Reverse'
    );

###############################################################################
# FUNCTION:     dropdown_box
#
# ARGS: name    Name of the selectbox
#       options Associative array of value => text
#       selected selected value, if any
#
# RETURNS:      HTML code for the dropdown box
#
# This function generates an HTML code for a dropdown select box.
###############################################################################
    function dropdown_box($name, $options, $selected) {
        $result = '<select name = "' . $name . '">' . "\n";

        foreach ($options as $value => $text) {
            $result .= '<option';
            if ($value != $text) $result .= ' value="' . $value . '"';
            if ($value == $selected) $result .= ' selected';
            $result .= '>' . $text . "</option>\n";
        }

        return($result . "</select>\n");
    }

###############################################################################
# FUNCTION:     search_pairings
#
# ARGS: gender  Gender pairings, comma-separated.
#       number  Number pairings, comma-separated.
#
# RETURNS:      Text pairings, comma-separated.
#
# This function performd split/convert/merge on pairings values.
###############################################################################
    function search_pairings($gender, $number) {
        if ($number) $name = implode(', ', pairings_num_to_name(explode(',',
            $number)));

        return($gender ? ($name ? "$gender, $name" : $gender) : $name);
    }

###############################################################################
# FUNCTION:     display_search
#
# ARGS: search  Associative array of search parameters
#
# RETURNS:      HTML code describing the search
#
# This function generates an HTML representation of a search query.
###############################################################################
    function display_search($query) {
        global $fa_search_matching, $fa_search_dates, $fa_search_leastmost,
            $fa_search_sortby;
        $display = array();

        $query['categories'] = category_ids_to_names($query['categories']);
        if (!$query['incpair']) $query['incpair'] = search_pairings(
            $query['incgender'], $query['num_incpair']);
        if (!$query['excpair']) $query['excpair'] = search_pairings(
            $query['excgender'], $query['num_excpair']);

        if ($query['keywords']) $display['Keywords'] = $query['keywords'];
        if ($query['title']) $display['Title'] = $query['title'];
        if ($query['author']) $display['Author'] = $query['author'];
        if ($query['categories']) $display['Categories'] =
            $fa_search_matching[$query['category_mode']] . ' '
            . $query['categories'];

        if ($query['incpair']) {
            $display['Pairings'] = $fa_search_matching[$query['incpair_mode']]
                . ' ' . $query['incpair'] . ($query['excpair']
                ? ("<br>Exclude " . $query['excpair']) : '');
        } elseif ($query['excpair']) {
            $display['Pairings'] = 'Exclude ' . $query['excpair'];
        }

        if ($query['date']) $display['Date'] =
            $fa_search_dates[$query['date']];
        if ($query['size']) $display['Length'] =
            $fa_search_leastmost[$query['sizetype']] . ' '
            . $query['size'] . ' words';

        if ($display) {
            $display['Results'] = 'Sort by '
                . $fa_search_sortby[$query['sort']] . ($query['reverse']
                ? ', backwards' : '') . (is_numeric($query['perpage'])
                ? ("<br>\n" . $query['perpage'] . ' per page') : '');
            $result = '<table class="info">' . "\n";

            foreach ($display as $key => $value) {
                $result .= "<tr><th>$key</th><td>$value</td></tr>\n";
            }

            $result .= "</table>\n";
            return($result);
        }
    }

###############################################################################
# FUNCTION:     hidden_inputs
#
# ARGS: hidden  Associative array of hidden form values
#
# RETURNS:      HTML code for hidden inputs
#
# This function creats a set of hidden form inputs for the supplied array.
###############################################################################
    function hidden_inputs($hidden) {
        $result = '';

        foreach ($hidden as $key => $value) {
            $result .= '<input type="hidden" name="' . $key . '" value="' .
                htmlspecialchars($value) . '">' . "\n";
        }

        return($result);
    }

###############################################################################
# FUNCTION:     make_where
#
# ARGS: where   Reference to array in which the results should go
#       field   Name of the SQL field to match against
#       match   A string of words to match against
#       words   TRUE if the match string should be split into words
#
# RETURNS:      Nothing
#
# This function constructs a match against given words.
###############################################################################
    function make_where(&$where, $field, $match, $words) {
        if (preg_match('|^\s*/(.+?)/\s*$|', $match, $n)) {
            $where[] = "$field RLIKE '" . mysql_escape_string($n[1]) . "'";
        } elseif ($words) {
            foreach (explode(' ', $match) as $word) {
                $where[] = "$field LIKE '%" . mysql_escape_string($word) . "%'";
            }
        } else {
            $where[] = "$field LIKE '%" . mysql_escape_string($match) . "%'";
        }
    }

###############################################################################
# FUNCTION:     parse_search
#
# ARGS: params  An array which contains the search parameters
#
# RETURNS:      An associative array:
#       query   An associative array containing compact search parameters
#       where   An array of SQL conditions defining the search
#       order   An SQL function defining the ordering
#       page    Which page of the search results to display
#       perpage The number of search results per page
#       skip    The number of search results to skip
#
# This function parses an input array into a search definition structure, and
# generates appropriate SQL code to implement it.
###############################################################################
    function parse_search($params) {
        global $fa_min_perpage, $fa_max_perpage;

        $where = array();
        $query = array();

        #######################################################################
        # Extract the query parameters
        #######################################################################

        if ($params['keywords']) {
            $keyword_match = 'MATCH (fa_story.name, fa_story.summary, ' .
                "fa_story.pairing_names) AGAINST('" .
                mysql_escape_string($params['keywords']) . "' IN BOOLEAN MODE)";
            $where[] = $keyword_match;
            $query['keywords'] = $params['keywords'];
        }

        if ($params['title']) {
            make_where($where, 'fa_story.name', $params['title'], FALSE);
            $query['title'] = $params['title'];
        }

        if ($params['author']) {
            make_where($where, 'fa_author.name', $params['author'], FALSE);
            $query['author'] = $params['author'];
        }

        if ($params['categories']) {
            $categories = explode(',', $params['categories']);
            $query['categories'] = $params['categories'];
        } else {
            $categories = find_categories($params);
            sort($categories, SORT_NUMERIC);
            if ($categories) $query['categories'] = implode(',', $categories);
        }

        if ($categories) {
            if ($params['category_mode'] == 'all') {
                $where[] = "fa_story.category_ids RLIKE '(^|,)"
                    . mysql_escape_string(implode(',(.+,)?', $categories))
                    . "(,|$)'";
            } elseif ($params['category_mode'] == 'exact') {
                $where[] = "fa_story.category_ids = '"
                    . mysql_escape_string(implode(',', $categories)) . "'";
                $query['category_mode'] = 'exact';
            } elseif ($params['category_mode'] == 'only') {
                $where[] = "fa_story.category_ids RLIKE '^("
                    . mysql_escape_string(implode(')?((^|,)', $categories))
                    . ")?$'";
                $query['category_mode'] = 'only';
            } else { # category_mode = any
                $where[] = "fa_story.category_ids RLIKE '(^|,)("
                    . mysql_escape_string(implode('|', $categories))
                    . ")(,|$)'";
                $query['category_mode'] = 'any';
            }

            $query['category_mode'] = $params['category_mode'];
        }

        if ($params['incpair']) {
            $incpair = find_character_pairings($params['incpair']);
            $incgender = find_gender_pairings($params['incpair']);
            if ($incpair) $query['num_incpair'] = implode(',', $incpair);
            if ($incgender) $query['incgender'] = implode(',', $incgender);
        } else {
            if ($params['num_incpair']) {
                $incpair = explode(',', $params['num_incpair']);
                $query['num_incpair'] = $params['num_incpair'];
            }

            if ($params['incgender']) {
                $incgender = explode(',', $params['incgender']);
                $query['incgender'] = $params['incgender'];
            }
        }

        if ($incpair || $incgender) {
            if ($params['incpair_mode'] == 'all') {
                foreach (make_pairings_regex_all($incpair) as $regex) {
                    $where[] = "fa_story.pairing_person_ids RLIKE '$regex'";
                }

                foreach (make_pairings_regex_all($incgender) as $regex) {
                    $where[] = "fa_story.pairing_genders RLIKE '$regex'";
                }

                $query['incpair_mode'] = 'all';
            } elseif ($params['incpair_mode'] == 'exact') {
                if ($incpair) $where[] = "fa_story.pairing_person_ids RLIKE '"
                    . make_pairings_regex_exact($incpair) . "'";
                if ($incgender) $where[] = "fa_story.pairing_genders RLIKE '"
                    . make_pairings_regex_exact($incgender) . "'";
                $query['incpair_mode'] = 'exact';
            } elseif ($params['incpair_mode'] == 'only') {
                if ($incpair) $where[] = "fa_story.pairing_person_ids RLIKE '"
                    . make_pairings_regex_only($incpair) . "'";
                if ($incgender) $where[] = "fa_story.pairing_genders RLIKE '"
                    . make_pairings_regex_only($incgender) . "'";
                $where[] = "fa_story.pairing_person_ids <> ''";
                $query['incpair_mode'] = 'only';
            } else { # incpair_mode = any
                $tmp = array();
                if ($incpair) $tmp[] = "fa_story.pairing_person_ids RLIKE '"
                    . make_pairings_regex_any($incpair) . "'";
                if ($incgender) $tmp[] = "fa_story.pairing_genders RLIKE '"
                    . make_pairings_regex_any($incgender) . "'";
                $where[] = '(' . implode('
                  OR             ', $tmp) . ')';
                $query['incpair_mode'] = 'any';
            }
        }

        if ($params['excpair']) {
            $excpair = find_character_pairings($params['excpair']);
            $excgender = find_gender_pairings($params['excpair']);
            if ($excpair) $query['num_excpair'] = implode(',', $excpair);
            if ($excgender) $query['excgender'] = implode(',', $excgender);
        } else {
            if ($params['num_excpair']) {
                $excpair = explode(',', $params['num_excpair']);
                $query['num_excpair'] = $params['num_excpair'];
            }

            if ($params['excgender']) {
                $excgender = explode(',', $params['excgender']);
                $query['excgender'] = $params['excgender'];
            }
        }

        if ($excpair) $where[] = "fa_story.pairing_person_ids NOT RLIKE '"
            . make_pairings_regex_any($excpair) . "'";
        if ($excgender) $where[] = "fa_story.pairing_genders NOT RLIKE '"
            . make_pairings_regex_any($excgender) . "'";

        if (is_numeric($params['date'])) {
            $where[] = '(TO_DAYS(NOW()) - TO_DAYS(fa_story.updated)) <= '
                . $params['date'];
            $query['date'] = $params['date'];
        }

        if (is_numeric($params['size']) && ($params['size'] > 0)) {
            $where[] = 'fa_story.size ' . (($params['sizetype'] == 'atmost') ?
                '<= ' : '>= ') . $params['size'];
            $query['size'] = $params['size'];
            $query['sizetype'] = ($params['sizetype'] == 'atmost') ? 'atmost'
                : 'atleast';
        }

        #######################################################################
        # Determine the sort order and paging
        #######################################################################

        if ($params['sort'] == 'author') {
            $order = 'fa_author.name';
            if ($params['reverse']) $order .= ' DESC';
            $order .= ', fa_story.name';
            $query['sort'] = 'author';
        } elseif ($params['sort'] == 'date') {
            $order = 'fa_story.updated';
            if (!$params['reverse']) $order .= ' DESC';
            $order .= ', fa_story.name';
            $query['sort'] = 'date';
        } elseif ($params['sort'] == 'size') {
            $order = 'fa_story.size';
            if (!$params['reverse']) $order .= ' DESC';
            $order .= ', fa_story.name';
            $query['sort'] = 'size';
        } elseif (($params['sort'] == 'relevance') && $keyword_match) {
            $order = $keyword_match;
            if (!$params['reverse']) $order .= ' DESC';
            $order .= ', fa_story.name';
            $query['sort'] = 'relevance';
        } else {
            $order = 'fa_story.name';
            if ($params['reverse']) $order .= ' DESC';
            $query['sort'] = 'title';
        }

        if ($params['reverse']) $query['reverse'] = 1;

        foreach ($params as $key => $value) {
            if (strncmp($key, 'page', 4) == 0) $page = (int)substr($key, 4);
        }

        if ($page < 1) $page = 1;

        if ($params['perpage'] == 'all') {
            $query['perpage'] = 'all';
            $perpage = $fa_max_perpage;
        } else {
            $perpage = (int)$params['perpage'];
            if ($perpage < $fa_min_perpage) $perpage = $fa_min_perpage;
            if ($perpage > $fa_max_perpage) $perpage = $fa_max_perpage;
            $query['perpage'] = $perpage;
        }

        $skip = ($page - 1) * $perpage;

        return(array(
            'query'     => $query,
            'where'     => $where,
            'order'     => $order,
            'page'      => $page,
            'perpage'   => $perpage,
            'skip'      => $skip
        ));
    }
###############################################################################
###############################################################################
?>
