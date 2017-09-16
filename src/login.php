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
### FILE: login.php
###
### This page displays allows users to log in and out of the site.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('init.php');
    require_once('login.php');

    $page_title = 'User Login';
    $fa_no_login = TRUE;
    fa_get_user('user');
    include('header.php');

    echo "<h2>User Login</h2>\n";

    if ($fa_login_failed) {
        echo '<p class="error">Your login failed. ' . ($fa_login_fail_text ?
            $fa_login_fail_text : 'If you have forgotten your password, ' .
            'please use the password request form below.'), "</p>\n";
    } elseif ($_SESSION['user']) {
        echo '<p>You are currently logged in as user ', $_SESSION['username'],
            '. If you want to log in as a different user, please enter the ',
            'username and password below. You can also <a href="login.php?',
            'logout=1">', "log out from this site</a> completely.</p>\n";
    } else {
        echo '<p>Please enter your username and password to log on to this ',
            "site. If you don't already have a username, then please go to ",
            'our <a href="profile/register.php">registration page</a>.',
            "</p>\n";
    }

    print_login_form('');

    echo "<h2>Password Request</h2>\n";

    if ($_POST['request']) {
        if ($_POST['username']) {
            $result = mysql_query("
                SELECT  name,
                        email,
                        password
                FROM    fa_user
                WHERE   name  = '" . mysql_escape_string($_POST['username']) .
            "'");

            if (list($username, $email, $password) = mysql_fetch_row($result)) {
                send_password_reminder($email, $username, $password);
            } else {
                echo '<p class="error">The username you gave could not be ',
                    "found. If you're not sure what your username is, you ",
                    "can try just entering your email address.</p>\n";
                $_SESSION['username'] = '';
            }
        } elseif ($_POST['email']) {
            $result = mysql_query("
                SELECT  name,
                        email,
                        password
                FROM    fa_user
                WHERE   email = '" . mysql_escape_string($_POST['email']) . "'
            ");

            if (list($username, $email, $password) = mysql_fetch_row($result)) {
                send_password_reminder($email, $username, $password);
            } else {
                echo '<p class="error">Sorry, but the email address you gave ',
                    "could not be found. If you're not sure what email ",
                    'address you registered with, you can try just entering ',
                    "your username.</p>\n";
            }
        } else {
            echo '<p>You need to supply either a username or an email ',
                "address to retrieve your login details.</p>\n";
        }
    } else {
        echo "<p>If you've forgotton your login details, we can send you a ",
            'reminder. Please enter either your username or the email ',
            'address you registered with, and an email will be sent to you.',
            "</p>\n";
    }

    print_request_form();
    include('footer.php');
###############################################################################
###############################################################################
?>
