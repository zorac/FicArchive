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
### FILE: boards/recent.php
###
### This page displays recent comments.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('comments.php');
    require_once('init.php');

    $search_page = 'boards';

    if ($_GET['author']) {
        $result = mysql_query('
            SELECT  author,
                    name
            FROM    fa_author
            WHERE   author = ' . (int)$_GET['author']
        );

        if (mysql_num_rows($result) != 1) fa_error('Author not found',
            'The author you requested could not be found.');
        list($author, $name) = mysql_fetch_row($result);
        mysql_free_result($result);
        $search_params = "author=$author";

        if ($_GET['type'] == 'feedback') {
            $page_title = "Recent Feedback for $name";
            $search_params .= '&amp;type=file';
            include('header.php');
            echo '<h2>Recent Feedback for <a href="../author.php?author=',
                $author, '">', $name, "</a></h2>\n<p>The most recently ",
                "posted feedback for this author.</p>\n";
            do_recent('review', FALSE, ('recent.php?author=' . $author
                . '&amp;type=feedback&amp;'), "
                  INNER JOIN    fa_file
                    ON          fa_file.file = fa_comment.id
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_file.story
                      AND       fa_story.author = $author
                WHERE           fa_comment.type = 'file'
            ");
        } elseif ($_GET['type'] == 'review') {
            $page_title = "Recent Reviews for $name";
            $search_params .= '&amp;type=story';
            include('header.php');
            echo '<h2>Recent Reviews for <a href="../author.php?author=',
                $author, '">', $name, "</a></h2>\n<p>The most recently ",
                "posted reader reviews for this author.</p>\n";
            do_recent('review', FALSE,
                ('recent.php?author=' . $author . '&amp;type=review'), "
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_comment.id
                      AND       fa_story.author = $author
                WHERE           fa_comment.type = 'story'
            ");
        } else {
            $page_title = "Recent Reviews for $name";
            include('header.php');
            echo '<h2>Recent Reviews for <a href="../author.php?author=',
                $author, '">', $name, "</a></h2>\n<p>The most recently ",
                "posted reviews and feedback for this author.</p>\n";
            do_recent_full('review', FALSE,
                ('recent.php?author=' . $author . '&amp;type=review'), array("
                  INNER JOIN    fa_file
                    ON          fa_file.file = fa_comment.id
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_file.story
                      AND       fa_story.author = $author
                WHERE           fa_comment.type = 'file'
            ", "
                  INNER JOIN    fa_story
                    ON          fa_story.story = fa_comment.id
                      AND       fa_story.author = $author
                WHERE           fa_comment.type = 'story'
            "), '');
        }
    } elseif ($_GET['user']) {
        $result = mysql_query('
            SELECT      user,
                        name
            FROM        fa_user
            WHERE       user = ' . (int)$_GET['user']
        );

        if (mysql_num_rows($result) != 1) fa_error('User not found',
            'The user you requested could not be found.');
        list($user, $name) = mysql_fetch_row($result);
        mysql_free_result($result);

        if ($_GET['type'] == 'reply') {
            $page_title = "Recent Replies to $name";
            include('header.php');
            echo '<h2>Recent Replies to <a href="../profile.php?user=',
                $user, '">', $name, "</a></h2>\n<p>The most recently ",
                "posted replies to comments and reviews by this user.</p>\n";
            do_recent_full('comment', TRUE, ('recent.php?user=' . $user
                . '&amp;type=reply&amp;'), array("
                  INNER JOIN    fa_comment AS parent
                    ON          parent.comment = fa_comment.parent
                     AND        parent.user = $user
                WHERE           fa_comment.user <> $user
            ", "
                  INNER JOIN    fa_thread
                    ON          fa_thread.thread = fa_comment.id
                      AND       fa_thread.user = $user
                WHERE           fa_comment.user <> $user
                  AND           fa_comment.type = 'thread'
                  AND           fa_comment.parent = 0
            "), "");
        } else {
            $page_title = "Recent Comments by $name";
            $search_params = "poster=$name";
            include('header.php');
            echo '<h2>Recent Comments by <a href="../profile.php?user=',
                $user, '">', $name, "</a></h2>\n<p>The most recently ",
                "posted comments and reviews by this user.</p>\n";
            do_recent('comment', TRUE, ('recent.php?user=' . $user
                . '&amp;'), "WHERE fa_comment.user = $user");
        }
    } else {
        $page_title = 'Recent comments';
        include('header.php');
        echo "<h2>Recent Comments</h2>\n<p>The most recently posted comments ",
            "and reviews from our boards.</p>\n";
        do_recent('comment', TRUE, 'recent.php?', '');
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
