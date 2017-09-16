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
### FILE:       lib/admin.php
###
### This file provides some utility functions for the admin area and also
### throws an immediate error page unless viewed by a logged-in mod or admin.
###############################################################################
###############################################################################
    require_once('init.php');
    require_once('login.php');

    $backlinks = array(array('Administration Home', './'));

###############################################################################
# FUNCTION:     display_backlinks
#
# ARGS:         None
#
# RETURNS:      Nothing
#
# This function display the admin area's breadcrumb trail, which is initialised
# above, and added to in the admin pages where appropriate.
###############################################################################
    function display_backlinks() {
        global $backlinks;

        $indent = '';
        $backlinks = array_reverse($backlinks);

        for ($i = 0; $i < count($backlinks); $i++) {
            $indent .= '&lt;';
            $backlinks[$i] = '<a href="' . $backlinks[$i][1] . '">'
                . $indent . ' ' . $backlinks[$i][0] . '</a>';
        }

        return('<p>' . implode("<br>\n", $backlinks) . "\n</p>");
    }

###############################################################################
# FUNCTION:     admin_error
#
# ARGS: header  Title for the error page
#       text    Text of the error message
#
# RETURNS:      Nothing
#
# This function displays an error page and then exits.
###############################################################################
    function admin_error($header, $text) {
        foreach (array_keys($GLOBALS) as $var) {
            global ${$var};
        }

        $page_title = $header;
        include('header.php');
        echo "<h2>$header</h2>\n", '<p class="error">', $text, '</p>',
            display_backlinks();
        include('footer.php');
        exit(0);
    }

    fa_get_user('user');

    #
    # Check to see if we're being viewed by a user who has access to the admin
    # pages. If not, we display a warning and login box, then exit.
    #
    if (!$_SESSION['seen_password'] || !fa_has_mod_access()) {
        $fa_no_login = TRUE;
        include('header.php');
        echo "<h2>Moderator Login Required</h2>\n", '<p class="error">',
            'Access to this page requires you to be logged on as a ',
            'moderator or an administrator.';
        #
        # Provide further details where appropriate.
        #
        if ($fa_login_failed) {
            echo ' <strong>The username and/or password you supplied is ',
                'incorrect.</strong> Please try again.';
        } elseif (!fa_has_mod_access()) {
            echo ' The account you are logged on with does not have this ',
                'privilege. Please log on with an appropriate account if ',
                'you wish to access this page.';
        } elseif (!$_SESSION['seen_password']) {
            echo ' You have not yet entered your password in this browser ',
                'session.';
        }

        echo "</p>\n";
        print_login_form('');
        include('footer.php');
        exit(0);
    }
###############################################################################
###############################################################################
?>
