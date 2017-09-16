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
### FILE:       lib/date.php
###
### This file provides handy date and time functions and constants.
###############################################################################
###############################################################################
    require_once('config.php');

    date_default_timezone_set($fa_timezone);

    $fa_month_names = array('', 'January', 'February', 'March', 'April', 'May',
        'June', 'July', 'August', 'September', 'October', 'November',
        'December');
    $fa_month_names_short = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $fa_num_days = array(0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

###############################################################################
# FUNCTION:     get_date
#
# ARGS: datetime Date/time (MySQL format)
#
# RETURNS:      An array (year, month, day)
#
# This function extracts the date parts from a date/time value.
###############################################################################
    function get_date($datetime) {
        preg_match('/(\d+)-(\d+)-(\d+)/', $datetime, $result);
        array_shift($result);

        return($result);
    }

###############################################################################
# FUNCTION:     get_time
#
# ARGS: datetime Date/time (MySQL format)
#
# RETURNS:      An array (hour, minute, second)
#
# This function extracts the time parts from a date/time value.
###############################################################################
    function get_time($datetime) {
        preg_match('/(\d+):(\d+):(\d+)/', $datetime, $result);
        array_shift($result);

        return($result);
    }

###############################################################################
# FUNCTION:     get_age
#
# ARGS: date    Date of birth (MySQL format)
#
# RETURNS:      Age in years
#
# This function returns the age in years of someone born on the given date.
###############################################################################
    function get_age($date) {
        list($year, $month, $day) = get_date($date);
        $now = localtime();

        $age = ($now[5] + 1900) - $year;

        if (($now[4] < ($month - 1))
                || (($now[4] == ($month - 1)) && ($now[3] < $day))) {
            $age -= 1;
        }

        return $age;
    }

###############################################################################
# FUNCTION:     short_date
#
# ARGS: date    Date/time (MySQL format)
#
# RETURNS:      Formatted date
#
# This function returns a short format of the given date.
###############################################################################
    function short_date($date) {
        global $fa_month_names_short;

        list($year, $month, $day) = get_date($date);
        return((int)$day . ' ' . $fa_month_names_short[(int)$month] . ' '
            . $year);
    }

###############################################################################
# FUNCTION:     short_time
#
# ARGS: time    Date/time (MySQL format)
#
# RETURNS:      Formatted time
#
# This function returns a short format of the given time.
###############################################################################
    function short_time($time) {
        list($hour, $minute, $second) = get_time($time);
        $ampm = (($hour < 12) ? 'am' : 'pm');
        if ($hour > 12) $hour = sprintf("%02d", $hour - 12);
        if ($hour == 0) $hour = 12;

        return "$hour:$minute $ampm";
    }

###############################################################################
# FUNCTION:     short_datetime
#
# ARGS: datetime Date/time (MySQL format)
#
# RETURNS:      Formatted date and time
#
# This function returns a short format of the given date and time.
###############################################################################
    function short_datetime($datetime) {
        return(short_date($datetime) . ', ' . short_time($datetime));
    }

###############################################################################
# FUNCTION:     long_date
#
# ARGS: date    Date/time (MySQL format)
#
# RETURNS:      Formatted date
#
# This function returns a long format of the given date.
###############################################################################
    function long_date($date, $show_year) {
        global $fa_month_names;

        list($year, $month, $day) = get_date($date);
        $day = (int)$day;
        $month = (int)$month;
        $th = 'th';

        if (($day == 1) || ($day == 21) || ($day == 31)) $th = 'st';
        if (($day == 2) || ($day == 22)) $th = 'nd';
        if (($day == 3) || ($day == 23)) $th = 'rd';

        $result = "$day<sup>$th</sup> " . $fa_month_names[$month];

        if ($show_year) {
            return("$result $year");
        } else {
            return($result);
        }
    }

###############################################################################
# FUNCTION:     make_date
#
# ARGS: day     The day of the month
#       month   The month
#       year    The year
#
# RETURNS:      MySQL formatted date
#
# This function creates a date in MySQL format.
###############################################################################
    function make_date($day, $month, $year) {
        global $fa_num_days;

        $day = (int)$day;
        $month = (int)$month;
        $year = (int)$year;

        if (($year < 0) || (($year > 99) && ($year < 1000))
                || ($month < 1) || ($month > 12)
                || ($day < 1) || ($day > $fa_num_days[$month])
                || (($month == 2) && ($day == 29)
                    && (($year % 4) || (!($year % 100) && ($year % 400))))) {
            return '';
        } else {
            if ($year < 100) {
                $now = localtime();

                if ($year <= ($now[5] - 100)) {
                    $year += 2000;
                } else {
                    $year += 1900;
                }
            }

            return(sprintf('%04d-%02d-%02d', $year, $month, $day));
        }
    }

###############################################################################
# FUNCTION:     todays_date
#
# ARGS:         None
#
# RETURNS:      MySQL formatted date
#
# This function returns today's date in MySQL format.
###############################################################################
    function todays_date() {
        $now = localtime();

        return make_date($now[3], $now[4] + 1, $now[5] + 1900);
    }

###############################################################################
# FUNCTION:     mysql_date
#
# ARGS: unix    UNIX time (seconds since the epoch)
#
# RETURNS:      MySQL formatted date
#
# This function returns the given date in MySQL format.
###############################################################################
    function mysql_date($unix) {
        return gmstrftime('%Y-%m-%d', $unix);
    }

###############################################################################
# FUNCTION:     nice_date
#
# ARGS: unix    UNIX time (seconds since the epoch)
#
# RETURNS:      Nicely formatted date
#
# This function returns the given date in a nice format.
###############################################################################
    function nice_date($unix) {
        return gmstrftime('%e %b %Y', $unix);
    }

###############################################################################
# FUNCTION:     rfc822_date
#
# ARGS: unix    UNIX time (seconds since the epoch)
#
# RETURNS:      RFC 822 formatted date
#
# This function returns the given date in RFC 822 format (for email headers).
###############################################################################
    function rfc822_date($unix) {
        return gmstrftime('%a, %e %b %Y %H:%M:%S GMT', $unix);
    }

###############################################################################
# FUNCTION:     tag_date
#
# ARGS: unix    UNIX time (seconds since the epoch)
#
# RETURNS:      XML tag formatted date
#
# This function returns the given date in XML tag format.
###############################################################################
    function tag_date($unix) {
        return gmstrftime('%Y-%m-%d', $unix);
    }

###############################################################################
# FUNCTION:     w3c_date
#
# ARGS: unix    UNIX time (seconds since the epoch)
#
# RETURNS:      W3C formatted date
#
# This function returns the given date in W3C format.
###############################################################################
    function w3c_date($unix) {
        return gmstrftime('%Y-%m-%dT%H:%M:%SZ', $unix);
    }

###############################################################################
###############################################################################
?>
