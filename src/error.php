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
### FILE: error.php
###
### This page displays error messages.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');

    $page_title = $_SERVER['REDIRECT_STATUS'];
    $fa_no_login = TRUE;
    include('header.php');

    if ($_SERVER['REDIRECT_STATUS'] == 401) {
        echo "<h2>Login Incorrect<h2>\nThe username or password you supplied ",
            'was incorrect. Please try again.';
    } elseif ($_SERVER['REDIRECT_STATUS'] == 403) {
        echo "<h2>Access Denied</h2>\nSorry, but you don't have permission ",
            'to access that directory.';
    } elseif ($_SERVER['REDIRECT_STATUS'] == 404) {
        echo "<h2>File Not Found</h2>\nThe file you are trying to access ",
            'does not exist. ';

        if ($_SERVER['HTTP_REFERER']) {
            echo 'You seem to have followed a broken link on <a href="',
                $_SERVER['HTTP_REFERER'], '">this page</a>. You might want ',
                'to let the maintainer of that page know about it.';
        } else {
            echo 'Please check that you have the correct URL.';
        }
    } elseif ($_SERVER['REDIRECT_STATUS'] >= 500) {
        echo "<h2>Internal Server Error</h2>\nSomething seems to have gone ",
            'wrong on the server. Please try again later.';
    } elseif ($_SERVER['REDIRECT_STATUS'] >= 400) {
        echo "<h2>Browser Error</h2>\nYour web browser seems to have sent a ",
            'peculiar request. Please try again.';
    } else {
        echo "<h2>Welcome to the Error Page</h2>\nCongratulations! You seem ",
            'to have arrived here without actually having suffered an error.';
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
