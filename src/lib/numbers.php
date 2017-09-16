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
### FILE:       lib/numbers.php
###
### This file provides handy functions and constants for working with numbers.
###############################################################################
###############################################################################
    $fa_big_numbers = array( 1000000000 => 'Billion', 1000000 => 'Million',
        1000 => 'Thousand', 100 => 'Hundred');
    $fa_text_numbers = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
        'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
        'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $fa_text_tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety');
    $fa_order_numbers = array('', 'First', 'Second', 'Third', 'Fourth',
        'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth', 'Eleventh',
        'Twelfth', 'Thirteenth', 'Fourteenth', 'Fifteenth', 'Sixteenth',
        'Seventeenth', 'Eighteenth', 'Nineteenth');
    $fa_roman_numerals = array(
        array('', 'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix'),
        array('', 'x', 'xx', 'xxx', 'xl', 'l', 'lx', 'lxx', 'lxxx', 'xc'),
        array('', 'c', 'cc', 'ccc', 'cd', 'd', 'dc', 'dcc', 'dccc', 'cm'),
        array('', 'm', 'mm', 'mmm')
    );

###############################################################################
# FUNCTION:     num_to_words
#
# ARGS: number  A number
#
# RETURNS:      The number in words
#
# Does exactly what it says on the tin.
###############################################################################
    function num_to_words($number) {
        global $fa_big_numbers, $fa_text_numbers, $fa_text_tens;

        $number = (int)$number;
        reset($fa_big_numbers);

        while (list($num, $word) = each($fa_big_numbers)) {
            if ($number >= $num) {
                if ($output) {
                    $output .= ', ';
                }

                $ouput .= num_to_words($number / $num) . ' ' . $word;
                $number %= $num;
            }
        }

        if ($number && $output) {
            $output .= ' and ';
        }

        if ($number < 20) {
            $output .= $fa_text_numbers[$number];
        } else {
            $output .= $fa_text_tens[(int)($number / 10)];

            if ($number % 10) {
                $output .= '-' . $fa_text_numbers[$number % 10];
            }
        }

        return($output ? $output : 'Zero');
    }

###############################################################################
# FUNCTION:     position_name
#
# ARGS: number  A number
#
# RETURNS:      The number as a position (eg First, Second...)
#
# Does exactly what it says on the tin.
###############################################################################
    function position_name($number) {
        global $fa_order_numbers;

        $i = ($number % 100);

        if ($i == 0) {
            $output = num_to_words($number - $i) . 'th';
        } else {
            if ($i < 20) {
                if ($number > 100) $output = num_to_words($number - $i)
                    . ' and ';
                $output .= $fa_order_numbers[$i];
            } else {
                $j = ($i % 10);
                if ($number > 9) $output = num_to_words($number - $j);

                if ($j == 0) {
                    $output = substr($output, 0, -1) . 'ieth';
                } else {
                    if ($number > 9) $output .= '-';
                    $output .= $fa_order_numbers[$j];
                }
            }
        }

        return $output;
    }

###############################################################################
# FUNCTION:     pluralize
#
# ARGS: singular A word
#
# RETURNS:      Its plural
#
# This function returns the plural of the supplied word. Usually.
###############################################################################
    function pluralize($singular) {
        $len = strlen($singular) - 1;
        $last = $singular{$len};

        if ($last == 's') {
            return($singular . 'es');
        } elseif ($last == 'y') {
            return(substr($singular, 0, $len) . 'ies');
        } else {
            return($singular . 's');
        }
    }

###############################################################################
# FUNCTION:     roman_numeralize
#
# ARGS: number  A number
#
# RETURNS:      The number in roman numerals
#
# Does exactly what it says on the tin.
###############################################################################
    function roman_numeralize($number) {
        global $fa_roman_numerals;

        $number = (int)$number;

        return($fa_roman_numerals[3][(int)($number / 1000)]
            . $fa_roman_numerals[2][(int)($number / 100) % 10]
            . $fa_roman_numerals[1][(int)($number / 10) % 10]
            . $fa_roman_numerals[0][$number % 10]);
    }

###############################################################################
# FUNCTION:     nice_count
#
# ARGS: number  A number
#
# RETURNS:      The number with comma-seperated thousands etc
#
# Does exactly what it says on the tin.
###############################################################################
    function nice_count($number) {
        $number = (string)(int)$number;

        if ($number < 1000) {
            return $number;
        } elseif ($number < 1000000) {
            return(substr($number, 0, -3) . ',' . substr($number, -3));
        } else {
            return(substr($number, 0, -6) . ',' . substr($number, -6, -3)
                . ',' . substr($number, -3));
        }
    }

###############################################################################
# FUNCTION:     chapter_name
#
# ARGS: name    The chapter name, if there is one
#       chapter The chapter word for this story
#       number  The chapter number
#
# RETURNS:      The chapter name
#
# This returns either the chapter name, or a text version of the chapter
# number.
###############################################################################
    function chapter_name($name, $chapter, $number) {
        global $fa_fileno_prologue, $fa_fileno_epilogue;

        if ($name) {
            return $name;
        } else {
            if ($number == $fa_fileno_prologue) {
                $result = 'Prologue';
            } elseif ($number == $fa_fileno_epilogue) {
                $result = 'Epilogue';
            } else {
                $result = $chapter . ' ' . num_to_words($number);
            }

            return ($chapter == ucfirst($chapter)) ? $result
                : strtolower($result);
        }
    }

###############################################################################
# FUNCTION:     mysql_found_rows
#
# ARGS:         None
#
# RETURNS:      The number of found rows
#
# This function returns the total number of rows found by a previous MySQL
# query which used SELECT SQL_CALC_FOUND_ROWS.
###############################################################################
    function mysql_found_rows() {
        $result = mysql_query('SELECT FOUND_ROWS()');
        list($found_rows) = mysql_fetch_row($result);
        mysql_free_result($result);

        return($found_rows);
    }

###############################################################################
# FUNCTION:     page_links
#
# ARGS: page    The current page
#       pages   The total number of pages
#       url     A self-URL for appending 'page=...' to (optional)
#
# RETURNS:      A string of links to pages.
#
# This function returns HTML for links to other pages of a query. It always
# includes links to the first and last pages, and to a number of pages around
# the currently selected one. If the URL parameter is supplied, text links are
# generated, otherwise form buttons are created. In both cases, the current
# page is highlighted/non-selectable.
###############################################################################
    function page_links($page, $pages, $url) {
        if ($page < 5) {
            $minpage = 2;
            $maxpage = 7;
            if ($maxpage >= $pages) $maxpage = $pages - 1;
        } elseif ($page > ($pages - 4)) {
            $minpage = $pages - 6;
            if ($minpage < 2) $minpage = 2;
            $maxpage = $pages - 1;
        } else {
            $minpage = $page - 3;
            $maxpage = $page + 3;
        }

        $result = 'Pages: ';

        if ($url) {
            if ($page == 1) {
                $result .= '<strong>&nbsp;1&nbsp;</strong>';
            } else {
                $result .= '<a href="' . $url . 'page=1">&nbsp;1&nbsp;</a>';
                if ($minpage > 2) $result .= '&hellip;';
            }

            if ($pages > 1) {
                for ($i = $minpage; $i <= $maxpage; $i++) {
                    if ($i == $page) {
                        $result .= "<strong>&nbsp;$i&nbsp;</strong>";
                    } else {
                        $result .= '<a href="' . $url . 'page=' . $i .
                            '">&nbsp;' . $i . '&nbsp;</a>';
                    }
                }

                if ($page == $pages) {
                    $result .= "<strong>&nbsp;$pages&nbsp;</strong>";
                } else {
                    if ($maxpage < ($pages - 1)) $result .= '&hellip;';
                    $result .= '<a href="' . $url . 'page=' . $pages .
                        '">&nbsp;' . $pages . '&nbsp;</a>';
                }
            }
        } else {
            if ($page == 1) {
                $result .= '<input type="button" value="1" disabled>';
            } else {
                $result .= '<input type="submit" name="page1" value="1">';
                if ($minpage > 2) $result .= ' &hellip;';
            }

            if ($pages > 1) {
                for ($i = $minpage; $i <= $maxpage; $i++) {
                    if ($i == $page) {
                        $result .= ' <input type="button" value="' . $i
                            . '" disabled>';
                    } else {
                        $result .= ' <input type="submit" name="page' . $i
                            . '" value="' . $i . '">';
                    }
                }

                if ($page == $pages) {
                    $result .= ' <input type="button" value="' . $pages
                        . '" disabled>';
                } else {
                    if ($maxpage < ($pages - 1)) $result .= ' &hellip;';
                    $result .= ' <input type="submit" name="page' . $pages
                        . '" value="' . $pages . '">';
                }
            }
        }

        return($result);
    }
###############################################################################
###############################################################################
?>
