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
### FILE:       lib/pairings.php
###
### This file provides handy functions for working with pairings.
###############################################################################
###############################################################################
    require_once('init.php');
    require_once('rebuild.php');

    $character_cache = array();
    $nickname_cache = array();
    $gender_chars = array();

    foreach ($fa_gender_list as $tmp_gender) {
        $gender_chars[substr($tmp_gender, 0, 1)] = $tmp_gender;
    }

###############################################################################
# FUNCTION:     find_pairings
#
# ARGS: vars    An associative array
#
# RETURNS:      An array of pairing IDs
#
# Extracts pairing IDs from $vars (which would normally be $_POST).
###############################################################################
    function find_pairings($vars) {
        $output = array();
        reset($vars);

        while (list($key, $value) = each($vars)) {
            if ($value) {
                $key = explode('-', $key);

                if (($key[0] == 'pairing') && is_numeric($key[1]))
                    $output[$key[1]] = $key[1];
            }
        }

        return($output);
    }

###############################################################################
# FUNCTION:     make_pairings_regex_all
#
# ARGS: pairings A pairing or array of pairings
#
# RETURNS:      An array of regexes which will match all of the pairings
#
# Generates one or more regular expressions which if all match, then all the
# supplied pairings are in the list matched against.
###############################################################################
    function make_pairings_regex_all($pairings) {
        if (!is_array($pairings)) $pairings = array($pairings);
        $regexes = array();
        $tmp = array();

        foreach ($pairings as $pairing) {
            if (preg_match('/^\w+(\/\w+)*(\/\*)?$/', $pairing, $matches)) {
                if ($matches[2]) {
                    $regexes[] = '(^|,|/)' . substr($pairing, 0, -2)
                        . '(/|,|$)';
                } else {
                    $tmp[] = $pairing;
                }
            }
        }

        if ($tmp) $regexes[] = ('(^|,)' . implode(',(.+,)?', $tmp) . '(,|$)');
        return($regexes);
    }

###############################################################################
# FUNCTION:     make_pairings_regex_any
#
# ARGS: pairings A pairing or array of pairings
#
# RETURNS:      A regex which will match any of the pairings
#
# Generates a regular expression which will match any of the given pairings.
###############################################################################
    function make_pairings_regex_any($pairings) {
        if (!is_array($pairings)) $pairings = array($pairings);
        $regexes = array();

        foreach ($pairings as $pairing) {
            if (preg_match('/^\w+(\/\w+)*(\/\*)?$/', $pairing, $matches)) {
                if ($matches[2]) {
                    $regexes[] = '(.*/)?' . substr($pairing, 0, -2) . '(/.*)?';
                } else {
                    $regexes[] = $pairing;
                }
            }
        }

        return('(^|,)(' . implode('|', $regexes) . ')(,|$)');
    }

###############################################################################
# FUNCTION:     make_pairings_regex_exact
#
# ARGS: pairings A pairing or array of pairings
#
# RETURNS:      A regex which will match the pairings exactly
#
# Generates a regular expression which will match exactly the given pairings.
###############################################################################
    function make_pairings_regex_exact($pairings) {
        if (!is_array($pairings)) $pairings = array($pairings);
        $regexes = array();

        foreach ($pairings as $pairing) {
            if (preg_match('/^\w+(\/\w+)*(\/\*)?$/', $pairing, $matches)) {
                if ($matches[2]) {
                    $regexes[] = '([^,]+/)?' . substr($pairing, 0, -2)
                        . '(/[^,]+)?';
                } else {
                    $regexes[] = $pairing;
                }
            }
        }

        return('^' . implode(',', $regexes) . '$');
    }

###############################################################################
# FUNCTION:     make_pairings_regex_only
#
# ARGS: pairings A pairing or array of pairings
#
# RETURNS:      A regex which will match the pairings exactly
#
# Generates a regular expression which will match exactly the given pairings.
###############################################################################
    function make_pairings_regex_only($pairings) {
        if (!is_array($pairings)) $pairings = array($pairings);
        $regexes = array();

        foreach ($pairings as $pairing) {
            if (preg_match('/^\w+(\/\w+)*(\/\*)?$/', $pairing, $matches)) {
                if ($matches[2]) {
                    $regexes[] = '([^,]+/)?' . substr($pairing, 0, -2)
                        . '(/[^,]+)?';
                } else {
                    $regexes[] = $pairing;
                }
            }
        }

        return('^(' . implode(')?((^|,)', $regexes) . ')?$');
    }

###############################################################################
# FUNCTION:     find_character_pairings
#
# ARGS: pairings A list of pairings
#
# RETURNS:      An array of person ID pairings
#
# Takes a list of pairings as input by the user and returns an array of
# PersonID/PersonID format pairings.
###############################################################################
    function find_character_pairings($pairings) {
        global $nickname_cache;
        $output = array();

        foreach (explode(',', $pairings) as $pairing) {
            $characters = array();
            $bad = FALSE;
            $wild = FALSE;

            foreach (explode('/', $pairing) as $nickname) {
                $nickname = strtolower(trim($nickname));

                if ($nickname == '*') {
                    $wild = TRUE;
                } elseif ($nickname_cache[$nickname]) {
                    $characters[] = $nickname_cache[$nickname][1];
                } else {
                    $result = mysql_query("
                        SELECT  nickname,
                                person
                        FROM    fa_nickname
                        WHERE   name = '" . mysql_escape_string($nickname)
                    . "'");

                    if ($detail = mysql_fetch_row($result)) {
                        $characters[] = $detail[1];
                        $nickname_cache[$nickname] = $detail;
                    } else {
                        $bad = TRUE;
                    }

                    mysql_free_result($result);
                }
            }

            if ($characters && !$bad) {
                sort($characters, SORT_NUMERIC);
                if ($wild) $characters[] = '*';
                $output[implode('/', $characters)] = 1;
            }
        }

        $output = array_keys($output);
        sort($output);
        return($output);
    }

###############################################################################
# FUNCTION:     find_gender_pairings
#
# ARGS: pairings A list of pairings
#
# RETURNS:      An array of gender pairings
#
# Takes a list of pairings as input by the user and returns an array of
# Gender/Gender format pairings.
###############################################################################
    function find_gender_pairings($pairings) {
        global $gender_chars;
        $output = array();

        foreach (explode(',', $pairings) as $pairing) {
            $genders = array();
            $bad = FALSE;
            $wild = FALSE;

            foreach (explode('/', $pairing) as $gender) {
                $gender = strtoupper(trim($gender));

                if ($gender == '*') {
                    $wild = TRUE;
                } elseif ($gender_chars[$gender]) {
                    $genders[] = $gender;
                } else {
                    $bad = TRUE;
                }
            }

            if ($genders && !$bad) {
                sort($genders, SORT_STRING);
                if ($wild) $genders[] = '*';
                $output[implode('/', $genders)] = 1;
            }
        }

        $output = array_keys($output);
        sort($output);
        return($output);
    }

###############################################################################
# FUNCTION:     find_exact_pairings
#
# ARGS: pairings A list of pairings
#
# RETURNS:      Arrays of exact pairings and bad pairings/nicknames
#
# Takes a list of pairings as input by a moderator and returns an array of
# unsorted NicknameID/NicknameID format pairings, generating new ones as
# required.
###############################################################################
    function find_exact_pairings($pairings) {
        global $nickname_cache;
        $output = array(
            'pairings'  => array(),
            'badpairs'  => array(),
            'badnicks'  => array()
        );

        foreach (explode(',', $pairings) as $pairing) {
            $nicknames = array();
            $prefix = '';

            foreach (explode('/', $pairing) as $nickname) {
                $nicklower = strtolower(trim($nickname));

                if ($nickname_cache[$nicklower]) {
                    $nicknames[] = $nickname_cache[$nicklower][0];
                } elseif ($nicklower != '') {
                    $result = mysql_query("
                        SELECT  nickname,
                                person
                        FROM    fa_nickname
                        WHERE   name = '" . mysql_escape_string($nicklower)
                    . "'");

                    if ($detail = mysql_fetch_row($result)) {
                        $nicknames[] = $detail[0];
                        $nickname_cache[$nicklower] = $detail;
                    } else {
                        $output['badpairs'][$pairing] = TRUE;
                        $output['badnicks'][$nickname] = TRUE;
                    }

                    mysql_free_result($result);
                }
            }

            if ($nicknames && !$output['badpairs'][$pairing]) {
                $nicks = implode('/', $nicknames);

                $result = mysql_query("
                    SELECT  pairing
                    FROM    fa_pairing
                    WHERE   prefix = '$prefix'
                      AND   nickname_ids = '$nicks'
                ");

                if (list($id) = mysql_fetch_row($result)) {
                    $output['pairings'][$id] = $pairing;
                } else {
                    mysql_query("
                        INSERT INTO fa_pairing
                                    (prefix, nickname_ids)
                        VALUES      ('$prefix', '$nicks')
                    ");

                    $id = mysql_insert_id();
                    $output['pairings'][$id] = $pairing;
                    $i = 1;

                    foreach ($nicknames as $nickid) {
                        mysql_query("
                            INSERT INTO fa_pairing_nickname
                            VALUES      ($id, $nickid, $i)
                        ");

                        $i++;
                    }

                    rebuild_pairing($id);
                }

                mysql_free_result($result);
            }
        }

        return($output);
    }

###############################################################################
# FUNCTION:     get_story_pairings
#
# ARGS: story   The story ID
#       pids    TRUE to return person IDs, FALSE to return names
#       genders Array in which genders are returned
#
# RETURNS:      An array of pairing names or IDs
#
# Returns an array of the pairings for the given story.
###############################################################################
    function get_story_pairings($story, $pids, &$genders) {
        $pairings = array();
        $result = mysql_query('
            SELECT              fa_pairing.' . ($pids ? 'person_ids'
                                                      : 'pairing') . ',
                                fa_pairing.prefix,
                                fa_pairing.name,
                                fa_pairing.genders
            FROM                fa_pairing
              INNER JOIN        fa_story_pairing
                ON              fa_story_pairing.pairing = fa_pairing.pairing
                  AND           fa_story_pairing.story = ' . (int)$story . '
            ORDER BY            fa_pairing.prefix,
                                fa_pairing.name
        ');

        while (list($pairing, $prefix, $name, $gender) =
                mysql_fetch_row($result)) {
            $pairings[$pairing] = ($prefix ? "$prefix $name" : $name);
            if (is_array($genders)) $genders[$gender] = $gender;
        }

        mysql_free_result($result);

        return($pairings);
    }

###############################################################################
# FUNCTION:     pairing_checkboxes
#
# ARGS: pairings Associative array with pairing IDs as keys and names as values
#
# RETURNS:      HTML code for the checkboxes
#
# Generates HTML code for pairing checkboxes.
###############################################################################
    function pairing_checkboxes($pairings) {
        $boxes = array();
        reset($pairings);

        while (list($pairing, $name) = each($pairings)) {
            $boxes[] = '<td><input type="checkbox" name="pairing-' . $pairing
                . '" id="pairing-' . $pairing
                . '" checked></td><td><label for="pairing-' . $pairing . '">'
                . $name . '</label></td>';
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
# FUNCTION:     pairings_num_to_name
#
# ARGS: pairings Array of pairings in character number format
#
# RETURNS:      Array of pairings in character name format
#
# Converts pairings from numeric to name format.
###############################################################################
    function pairings_num_to_name($pairings) {
        global $character_cache;
        $output = array();

        foreach ($pairings as $pairing) {
            $characters = array();

            foreach (explode('/', $pairing) as $character) {
                if ($character == '*') {
                    $characters[] = '*';
                } elseif ($character_cache[$character]) {
                    $characters[] = $character_cache[$character];
                } else {
                    $result = mysql_query('
                        SELECT       fa_nickname.name
                        FROM         fa_person
                          INNER JOIN fa_nickname
                            ON       fa_nickname.nickname = fa_person.nickname
                        WHERE        fa_person.person = ' . (int)$character
                    );

                    if (list($name) = mysql_fetch_row($result)) {
                        $characters[] = $name;
                        $character_cache[$character] = $name;
                    }

                    mysql_free_result($result);
                }
            }

            if (count($characters) > 0) $output[] = implode('/', $characters);
        }

        return($output);
    }
###############################################################################
###############################################################################
?>
