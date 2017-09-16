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
### FILE:       lib/letters.php
###
### This file handles the letter-links for 'browse by...' pages.
###############################################################################
###############################################################################
    $letter_links = '<p class="letters">';

    $letter_selected = strtoupper($_REQUEST['letter']);
    if (!$letter_selected)
        $letter_selected = $_SESSION['last'][$fa_last_letter];

    if (($letter_selected != '0-9') && ((strlen($letter_selected) != 1)
            || (ord($letter_selected{0}) < ord('A'))
            || (ord($letter_selected{0}) > ord('Z')))) {
        $letter_selected = rand(0, 26);

        if ($letter_selected == 26) {
            $letter_selected = '0-9';
            $fa_random_letter = 'numbers etc';
        } else {
            $letter_selected = chr($letter_selected + ord('A'));
            $fa_random_letter = 'the letter ' . $letter_selected;
        }
    }

    if ($letter_selected == '0-9') {
        $letter_links .= '<strong>&nbsp;0-9&nbsp;</strong>';
        $letter_match = "RLIKE '^[^A-Z]'";
    } else {
        $letter_links .= '<a href="' . $_SERVER['PHP_SELF'] .
            '?letter=0-9">&nbsp;0-9&nbsp;</a>';
        $letter_match = "LIKE '$letter_selected%'";
    }

    for ($i = ord('A'); $i <= ord('Z'); $i++) {
        if ($letter_selected == chr($i)) {
            $letter_links .= ' <strong>&nbsp;' . chr($i) . '&nbsp;</strong>';
        } else {
            $letter_links .= '<a href="' . $_SERVER['PHP_SELF'] . '?letter='
                . chr($i) . '">&nbsp;' . chr($i) .  '&nbsp;</a>';
        }
    }

    $letter_links .= '</p>';

    if ($_SESSION['last'][$fa_last_letter] != $letter_selected)
        $_SESSION['last'][$fa_last_letter] = $letter_selected;
###############################################################################
###############################################################################
?>
