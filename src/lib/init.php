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
### FILE:       lib/init.php
###
### This file provides initialization code for every connection, session
### handling and many utility functions.
###############################################################################
###############################################################################
    require_once('config.php');

###############################################################################
# FUNCTION:     fa_error_handler
#
# ARGS: errno   Level of error
#       errstr  Error message
#       errfile File containing error
#       errline Line where error occured
#       errcontext Symbol table
#
# RETURNS:      Nothing
#
# This function implements a custom PHP error handler.
###############################################################################
    function fa_error_handler($errno, $errstr, $errfile, $errline,
            $errcontext) {
        if ($errno == E_NOTICE) return;

        foreach (array_keys($GLOBALS) as $var) {
            global ${$var};
        }

        include('header.php');
        echo "<h2>An Error Has Ocurred</h2>\n";
        echo '<p class="error">Unfortunately, a system error has ocurred.',
            " Please try again later.</p>\n";
        include('footer.php');
        die();
    }

###############################################################################
# FUNCTION:     fa_session_open
#
# ARGS: path    File path
#       name    Session name
#
# RETURNS:      TRUE
#
# Open a session - in our case, this is a NOP.
###############################################################################
    function fa_session_open($path, $name) {
        return(TRUE);
    }

###############################################################################
# FUNCTION:     fa_session_close
#
# ARGS:         None
#
# RETURNS:      TRUE
#
# Close a session - in our case, this is a NOP.
###############################################################################
    function fa_session_close() {
        return(TRUE);
    }

###############################################################################
# FUNCTION:     fa_session_read
#
# ARGS: id      The session ID
#
# RETURNS:      The session data
#
# Reads the specified session from the database, or inserts a new, empty one if
# needed.
###############################################################################
    function fa_session_read($id) {
        global $fa_new_session;

        $id = mysql_escape_string($id);
        $result = mysql_query("
            SELECT  data
            FROM    fa_session
            WHERE   session = '$id'
        ");

        if (mysql_num_rows($result) == 1) {
            list($data) = mysql_fetch_row($result);
            mysql_free_result($result);
            $fa_new_session = FALSE;
            return($data);
        } else {
            mysql_query("
                INSERT INTO fa_session
                            (session)
                VALUES      ('$id')
            ");

            $fa_new_session = TRUE;
            return('');
        }
    }

###############################################################################
# FUNCTION:     fa_session_write
#
# ARGS: id      The session ID
#       data    The session data
#
# RETURNS:      TRUE on success, FALSE on failure
#
# Writes the session data into the database.
###############################################################################
    function fa_session_write($id, $data) {
        return mysql_query("
            REPLACE INTO    fa_session
                            (session, data)
            VALUES          ('" . mysql_escape_string($id) . "', '"
                                . mysql_escape_string($data) . "')
        ");
    }

###############################################################################
# FUNCTION:     fa_session_destroy
#
# ARGS: id      The session ID
#
# RETURNS:      TRUE on success, FALSE on failure
#
# Deletes the specified session from the database.
###############################################################################
    function fa_session_destroy($id) {
        return mysql_query("
            DELETE FROM fa_session
            WHERE       session = '" . mysql_escape_string($id) . "'
        ");
    }

###############################################################################
# FUNCTION:     fa_session_gc
#
# ARGS: duration Maximum age (in seconds) of sessions to keep.
#
# RETURNS:      TRUE
#
# Deletes old sessions from the database.
###############################################################################
    function fa_session_gc($duration) {
        mysql_query("
            DELETE FROM fa_session
            WHERE       UNIX_TIMESTAMP(updated) < (UNIX_TIMESTAMP(NOW()) - "
                            . (int)$duration . ")
        ");

        return TRUE;
    }

###############################################################################
# FUNCTION:     fa_error
#
# ARGS: header  Title for the error page
#       text    Text of the error message
#
# RETURNS:      Nothing
#
# This function displays an error page and then exits.
###############################################################################
    function fa_error($header, $text) {
        foreach (array_keys($GLOBALS) as $var) {
            global ${$var};
        }

        $page_title = $header;
        include('header.php');
        echo "<h2>$header</h2>\n", '<p class="error">', $text, '</p>';
        include('footer.php');
        exit(0);
    }

###############################################################################
# FUNCTION:     fa_get_user
#
# ARGS: mode    Type of lookup to perform
#
# RETURNS:      TRUE if user details were retrieved, false otherwise.
#
# This session performs logins loads user details from the database. Modes:
# user - load the details for the currently logged in user
# login - perform a login (check username & password)
# autologin - perform an auto-login (grab username from cookie)
###############################################################################
    function fa_get_user($mode) {
        global $fa_done_get_user, $fa_login_succeeded, $fa_login_failed,
            $fa_cookie_username, $fa_cookie_autologin, $fa_cookie_time,
            $fa_set_autologin, $fa_unset_autologin, $fa_server_path;

        if ($mode == 'user') {
            if (!$_SESSION['user']) return FALSE;
            $where = 'user = ' . (int)$_SESSION['user'];
        } elseif ($mode == 'login') {
            if (!$_POST['username'] || !$_POST['password']) return FALSE;
            $where = "name = '" . mysql_escape_string($_POST['username'])
                . "' AND password = '"
                . mysql_escape_string($_POST['password']) . "'";
            $login = TRUE;
        } elseif ($mode == 'autologin') {
            if (!$_COOKIE[$fa_cookie_username] ||
                !$_COOKIE[$fa_cookie_autologin]) return FALSE;
            $where = "name = '"
                . mysql_escape_string($_COOKIE[$fa_cookie_username])
                . "' AND MD5(password) = '"
                . mysql_escape_string($_COOKIE[$fa_cookie_autologin]) . "'";
        }

        if ($fa_done_get_user) return($_SESSION['user'] ? TRUE : FALSE);

        $result = mysql_query("
            SELECT  user,
                    MD5(password),
                    name,
                    email,
                    dob,
                    type,
                    options
            FROM    fa_user
            WHERE   $where
              AND   type <> 'disabled'
        ");

        if (mysql_num_rows($result) == 1) {
            list($_SESSION['user'], $md5_password, $_SESSION['username'],
                $_SESSION['email'], $_SESSION['dob'], $_SESSION['type'],
                $options) = mysql_fetch_array($result);
            settype($_SESSION['user'], 'integer');

            if ($options) {
                $_SESSION['options'] = unserialize($options);
            } else {
                $_SESSION['options'] = array();
            }

            if (!$_SESSION['last']) $_SESSION['last'] = array();

            if (!headers_sent()) {
                $expiry = time() + $fa_cookie_time;
                setcookie($fa_cookie_username, $_SESSION['username'], $expiry,
                    $fa_server_path . '/');

                if ($fa_unset_autologin) {
                    setcookie($fa_cookie_autologin, '', time() - 86400,
                        $fa_server_path . '/');
                } elseif ($_COOKIE[$fa_cookie_autologin] || $fa_set_autologin) {
                    setcookie($fa_cookie_autologin, $md5_password, $expiry,
                        $fa_server_path . '/');
                }
            }

            if ($login) {
                $fa_login_succeeded = TRUE;
                $_SESSION['seen_password'] = TRUE;
            }
        } else {
            session_unset();
            $fa_login_failed = $login;
        }

        mysql_free_result($result);
        $fa_done_get_user = TRUE;

        return($_SESSION['user'] ? TRUE : FALSE);
    }

###############################################################################
# FUNCTION:     fa_has_admin_access
#
# ARGS:         None
#
# RETURNS:      TRUE iff the user has administrator access
#
# Does exactly whay it says on the tin.
###############################################################################
    function fa_has_admin_access() {
        return($_SESSION['type'] == 'admin');
    }

###############################################################################
# FUNCTION:     fa_has_mod_access
#
# ARGS:         None
#
# RETURNS:      TRUE iff the user has moderator/administrator access
#
# Does exactly whay it says on the tin.
###############################################################################
    function fa_has_mod_access() {
        return(($_SESSION['type'] == 'admin') || ($_SESSION['type'] == 'mod'));
    }

###############################################################################
# FUNCTION:     fa_is_verified
#
# ARGS:         None
#
# RETURNS:      TRUE iff the user's email address has been verified
#
# Does exactly whay it says on the tin.
###############################################################################
    function fa_is_verified() {
        return(($_SESSION['type'] == 'verified') || fa_has_mod_access());
    }

###############################################################################
# FUNCTION:     fa_protect_email_address
#
# ARGS: address The address to be protected
#
# RETURNS:      An obfuscated version of the address
#
# This function performs simple obfuscation of an email address.
###############################################################################
    function fa_protect_email_address($address) {
        return(str_replace(array('@', '.'), array(' (at) ', ' (dot) '),
            $address));
    }

    #
    # Some basic setup
    #
    $null = NULL;
    if (!$fa_debug_mode) set_error_handler('fa_error_handler');
    $fa_root = substr(ini_get('include_path'), 0, -3);
    $fa_server_root = ($fa_server_secure ? 'https://' : 'http://')
        . $fa_server_name . $fa_server_path;
    #
    # Connect to the database
    #
    $db = mysql_connect($fa_mysql_server, $fa_mysql_username,
        $fa_mysql_password) or die("Unable to connect to the database");
    mysql_select_db($fa_mysql_database, $db);
    #
    # Initialise a session
    #
    session_name($fa_session_name);
    session_set_cookie_params(0, $fa_server_path . '/');
    session_set_save_handler('fa_session_open', 'fa_session_close',
        'fa_session_read', 'fa_session_write', 'fa_session_destroy',
        'fa_session_gc');
    session_start();
    #
    # Perform a login, logout or autologin
    #
    if ($_POST['login']) {
        fa_get_user('login');
    } elseif ($_REQUEST['logout']) {
        session_unset();
    } elseif ($fa_new_session) {
        fa_get_user('autologin');
    }
###############################################################################
###############################################################################
?>
