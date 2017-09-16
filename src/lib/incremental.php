<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2005 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE:       lib/incremental.php
###
### This library provides utility functions for performing large tasks in an
### incremental fashion to avoid timeouts or resource limits.
###############################################################################
###############################################################################
    $script_start_time = time();
    $script_end_time = $script_start_time +
        2;#(ini_get('max_execution_time') * 0.5);

###############################################################################
# FUNCTION:     start_incremental
#
# ARGS:         None
#
# RETURNS:      None
#
# This function starts an incremental process.
###############################################################################
    function start_incremental() {
        global $incremental;

        $incremental = array('progress' => 1);
    }

###############################################################################
# FUNCTION:     is_incremental
#
# ARGS:         None
#
# RETURNS:      TRUE iff we're running an incremental process.
#
# This function checks to see whether we're part way through an incremental
# process, and sets $incremental to the session data if we are. It will exit
# with an error if the session data and URL do not match.
###############################################################################
    function is_incremental() {
        global $incremental;

        if ($_GET['incremental']) {
            if (!array_key_exists('incremental', $_SESSION)) {
                fa_error('Unexpected Process', 'Your appear to be ' .
                    'attempting to continue an incremental process, but ' .
                    'there is no record of it in your session.');
            } elseif ($_GET['incremental'] <>
                    $_SESSION['incremental']['progress']) {
                unset($_SESSION['incremental']);
                fa_error('Process Mismatch', 'Your appear to be attempting ' .
                    'to continue an incremental process, but there is a '.
                    'progress mismatch in your session. The process has ' .
                    'been abandoned.');
            } else {
                $_SESSION['incremental']['progress']++;
                $incremental = $_SESSION['incremental'];
                return(TRUE);
            }
        } elseif (array_key_exists('incremental', $_SESSION) &&
                $_SESSION['incremental']['progress']) {
            unset($_SESSION['incremental']);
            fa_error('Incomplete Process', 'Your session contained a ' .
                'reference to an incremental process which was not ' .
                'completed. That process must now be abandoned.');
        } else {
            return(FALSE);
        }
    }

###############################################################################
# FUNCTION:     incremental_can_continue
#
# ARGS:         None
#
# RETURNS:      TRUE iff the incremental process can continue.
#
# This function checks to see whether an incremental process can safely
# continue.
###############################################################################
    function incremental_can_continue() {
        global $script_end_time;

        return(time() < $script_end_time);
    }

###############################################################################
# FUNCTION:     next_incremental
#
# ARGS:         body    Body text to display
#
# RETURNS:      None
#
# This function causes an incremental process to go to the next page.
###############################################################################
    function next_incremental($body) {
        foreach (array_keys($GLOBALS) as $var) {
            global ${$var};
        }

        $_SESSION['incremental'] = $incremental;
        header('Refresh: 1; URL=' .  $_SERVER['PHP_SELF'] . '?incremental='
            . $incremental['progress']);
        include('header.php');
        echo $body;
        include('footer.php');
        exit(0);
    }

###############################################################################
# FUNCTION:     stop_incremental
#
# ARGS:         None
#
# RETURNS:      None
#
# This function stops an incremental process.
###############################################################################
    function stop_incremental() {
        global $incremental;

        unset($incremental);
        unset($_SESSION['incremental']);
    }
###############################################################################
###############################################################################
?>
