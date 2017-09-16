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
### FILE:       lib/rebuild.php
###
### This file provides functions for rebuilding cached database data.
###############################################################################
###############################################################################
    require_once('categories.php');
    require_once('config.php');
    require_once('init.php');
    require_once('pairings.php');

###############################################################################
# FUNCTION:     rebuild_pairing
#
# ARGS: pairing The pairing's ID
#
# RETURNS:      Nothing
#
# This function rebuilds the cached information for a pairing.
###############################################################################
    function rebuild_pairing($pairing) {
        $result = mysql_query("
            SELECT          fa_nickname.nickname,
                            fa_nickname.name,
                            fa_person.person,
                            fa_person.gender
            FROM            fa_nickname
              INNER JOIN    fa_person
                ON          fa_nickname.person = fa_person.person
              INNER JOIN    fa_pairing_nickname
                ON          fa_pairing_nickname.nickname
                                = fa_nickname.nickname
                  AND       fa_pairing_nickname.pairing = $pairing
            ORDER BY        fa_pairing_nickname.position
        ");

        $nickname_ids = array();
        $nicknames = array();
        $person_ids = array();
        $genders = array();

        while (list($nickname, $name, $person, $gender)
                = mysql_fetch_array($result)) {
            $nickname_ids[] = $nickname;
            $nicknames[] = $name;
            $person_ids[] = $person;
            $genders[] = $gender;
        }

        mysql_free_result($result);
        sort($person_ids, SORT_NUMERIC);
        sort($genders, SORT_STRING);

        return mysql_query("
            UPDATE  fa_pairing
            SET     name = '" . mysql_escape_string(
                                    implode('/', $nicknames)) . "',
                    person_ids = '" . implode('/', $person_ids) . "',
                    nickname_ids = '" . implode('/', $nickname_ids) . "',
                    genders = '" . implode('/', $genders) . "'
            WHERE   pairing = " . $pairing
        );
    }

###############################################################################
# FUNCTION:     rebuild_story
#
# ARGS: story   The story's ID
#
# RETURNS:      Nothing
#
# This function rebuilds the cached information for a story.
###############################################################################
    function rebuild_story($story) {
        global $fa_fileno_prologue, $fa_fileno_epilogue;

        $pairing_genders = array();
        $category_ids = get_story_categories($story, FALSE);
        $pairing_names = get_story_pairings($story, TRUE, $pairing_genders);
        $pairing_person_ids = array_keys($pairing_names);
        sort($pairing_person_ids);
        sort($pairing_genders);

        $result = mysql_query("
            SELECT      number,
                        file,
                        rating,
                        UNIX_TIMESTAMP(updated),
                        size
            FROM        fa_file
            WHERE       story = $story
            ORDER BY    number
        ");

        $rating = 0;
        $updated = 0;
        $size = 0;
        $file_ids = array();

        while (list($number, $file, $file_rating, $file_updated, $file_size)
                = mysql_fetch_row($result)) {
            if ($number == $fa_fileno_prologue) {
                array_push($file_ids, 'P:' . $file);
            } elseif ($number == $fa_fileno_epilogue) {
                array_push($file_ids, 'E:' . $file);
            } else {
                array_push($file_ids, $number . ':' . $file);
            }

            if ($file_rating > $rating) $rating = $file_rating;
            if ($file_updated > $updated) $updated = $file_updated;
            $size += $file_size;
        }

        mysql_free_result($result);
        if ($updated == 0) $updated = 'NULL';

        return mysql_query("
            UPDATE  fa_story
            SET     rating = $rating,
                    updated = FROM_UNIXTIME($updated),
                    size = $size,
                    category_ids = '" . implode(',', $category_ids) . "',
                    pairing_person_ids = '" . implode(',',
                        $pairing_person_ids) . "',
                    pairing_genders = '" . implode(',',
                        $pairing_genders) . "',
                    pairing_names = '" . mysql_escape_string(implode(', ',
                        $pairing_names)) . "',
                    file_ids = '" . implode(',', $file_ids) . "'
            WHERE   story = $story
        ");
    }
###############################################################################
###############################################################################
?>
