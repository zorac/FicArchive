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
### FILE:       lib/submit.php
###
### This file ensures that the page is being viewed by a logged in and verified
### user and provides other submission functions.
###############################################################################
###############################################################################
    require_once('init.php');
    require_once('login.php');

###############################################################################
# FUNCTION:     submit_links
#
# ARGS: prev    Name of previous page (if any)
#       next    Name of next page (if any)
#
# RETURNS:      Nothing
#
# Outputs previous and/or next buttons for a submit page
###############################################################################
    function submit_links($prev, $next) {
        echo '<table><tr>';
        if ($prev) echo '<td><form method="post" action="', $prev,
            '"><input type="submit" value="&lt; Previous step"></form></td>';
        if ($next) echo '<td><form method="post" action="', $next,
            '"><input type="submit" value="Next step &gt;"></form></td>';
        echo "</tr></table>\n";
    }

    if ($_REQUEST['set_autologin']) $fa_set_autologin = TRUE;
    if ($_REQUEST['unset_autologin']) $fa_unset_autologin = TRUE;
    fa_get_user('user');

    if (!$_SESSION['user'] || !$_SESSION['seen_password']) {
        $fa_no_login = TRUE;
        $page_title = 'User Profile';
        include('header.php');
        echo "<h2>User Profile</h2>\n";

        if ($fa_login_failed) {
            echo '<p class="error">Your login failed - if you have forgotten ',
                'your password, we have a <a href="../login.php">password ',
                "reminder form</a>.</p>\n";
        } elseif (!$_SESSION['seen_password']) {
            echo '<p class="warning">You have not entered your password yet ',
                'during this browser session - please enter it now to ',
                "access your profile.</p>\n";
        } else {
            echo '<p class="warning">You need to log in to submit a story. ',
                "If you don't have a username and password, then please ",
                '<a href="register.php">visit our registration page</a>.</p>' .
                "\n";
        }

        print_login_form('');
        include('footer.php');
        exit(0);
    } elseif (!fa_is_verified()) {
        fa_error('You must verify your account before you may submit a ' .
            'story. Visit the <a href="../profile/">profile page</a> and ' .
            'ensure that you have set an email address on your account and ' .
            'enter the verification code from a confirmation email.</p>' .
            "\n");
    }

    if ($_SESSION['submit']) {
        $submit = $_SESSION['submit'];
    } else {
        $submit = array();
    }
###############################################################################
###############################################################################
?>
