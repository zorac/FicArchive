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

###############################################################################
# FUNCTION:     display_subscriptions
#
# ARGS: user    The user ID
#
# RETURNS:      An HTML list of subscriptions
#
# This function generates HTML listing all of the email subscriptions for a
# user.
###############################################################################
    function display_subscriptions($user) {
        global $fa_server_path;

        $subscriptions = array();

        #
        # Check for subscription to all story updates.
        #
        $result = mysql_query("
            SELECT  1
            FROM    fa_subscription
            WHERE   user = $user
              AND   type = 1
              AND   data = 0
        ");

        if (mysql_num_rows($result)) $subscriptions[] = array(1, 0,
            '<strong>All</strong> story and chapter updates.');
        mysql_free_result($result);

        #
        # Search for per-author subscriptions.
        #
        $result = mysql_query("
            SELECT          fa_author.author,
                            fa_author.name
            FROM            fa_author
              INNER JOIN    fa_subscription
                ON          fa_subscription.data = fa_author.author
                  AND       fa_subscription.user = $user
                  AND       fa_subscription.type = 2
            ORDER BY        fa_author.name
        ");

        while (list($author, $name) = mysql_fetch_row($result)) {
            $subscriptions[] = array(2, $author, 'All updates by author <a '
                . 'href="' . $fa_server_path . '/author.php?author=' . $author
                . '"><strong>' . $name . '</strong></a>.');
        }

        #
        # Search for per-series subscriptions.
        #
        $result = mysql_query("
            SELECT          fa_series.series,
                            fa_series.name,
                            fa_author.author,
                            fa_author.name
            FROM            fa_series
              INNER JOIN    fa_author
                ON          fa_author.author = fa_series.author
              INNER JOIN    fa_subscription
                ON          fa_subscription.data = fa_series.series
                  AND       fa_subscription.user = $user
                  AND       fa_subscription.type = 3
            ORDER BY        fa_series.name
        ");

        while (list($series, $title, $author, $name) =
                mysql_fetch_row($result)) {
            $subscriptions[] = array(3, $series, 'Additions to the <a href="'
                . $fa_server_path . '/series.php?series=' . $series
                . '"><strong>' . $title . '</strong></a> series by <a href="'
                . $fa_server_path . '/author.php?author=' . $author . '">'
                . $name . '</a>.');
        }

        #
        # Search for per-story subscriptions.
        #
        $result = mysql_query("
            SELECT          fa_story.story,
                            fa_story.name,
                            fa_author.author,
                            fa_author.name
            FROM            fa_story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              INNER JOIN    fa_subscription
                ON          fa_subscription.data = fa_story.story
                  AND       fa_subscription.user = $user
                  AND       fa_subscription.type = 4
            ORDER BY        fa_story.name
        ");

        while (list($story, $title, $author, $name) =
                mysql_fetch_row($result)) {
            $subscriptions[] = array(4, $story, 'New chapters of of the story '
                . '<a href="' . $fa_server_path . '/story.php?story=' . $story
                . '"><strong>' . $title . '</strong></a> by <a href="'
                . $fa_server_path . '/author.php?author=' . $author . '">'
                . $name . '</a>.');
        }

        return $subscriptions;
    }
###############################################################################
###############################################################################
?>
