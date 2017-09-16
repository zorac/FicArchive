<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2004,2005,2009 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE:       lib/comments.php
###
### This file contains the complete generic message board system.
###############################################################################
###############################################################################
    require_once('config.php');
    require_once('date.php');
    require_once('init.php');
    require_once('login.php');
    require_once('numbers.php');

    if ($_REQUEST['reply'] || $_POST['post'] || $_POST['preview'])
        $fa_no_login = 1;

###############################################################################
# FUNCTION:     auto_format
#
# ARGS: text    The text to be auto-formatted
#       auto    TRUE if auto-formatting should be performed
#
# RETURNS:      Formatted text
#
# This function replaces newlines with <br>s in the text if auto is set.
###############################################################################
    function auto_format($text, $auto) {
        return ($auto ? preg_replace('/\r?\n|\r/m', "<br>\n", $text)
            : $text);
    }

###############################################################################
# FUNCTION:     auto_unformat
#
# ARGS: text    The text to be auto-unformatted
#       auto    TRUE if auto-unformatting should be performed
#
# RETURNS:      Unformatted text
#
# This function removes <br>s at the end of lines in the text if auto is set.
###############################################################################
    function auto_unformat($text, $auto) {
        return ($auto ? preg_replace('/<br>(\r?\n|\r)/im', '$1', $text)
            : $text);
    }

###############################################################################
# FUNCTION:     cleanup_tag
#
# ARGS: tag     The tag to be cleaned up
#
# RETURNS:      A sparkly-clean version of the tag.
#
# This function cleans up an HTML tag, removing unwanted arguments and so on.
###############################################################################
    function cleanup_tag($tag) {
        global $fa_html_tags, $fa_tag_counts;

        if (!preg_match('|^\s*(/?)\s*(\w+)(\s+.*)?\s*(/?)\s*$|', $tag,
            $tmatch)) return '';
        $tag = strtolower($tmatch[2]);

        if (!array_key_exists($tag, $fa_html_tags)) return;

        if ($tmatch[1] == '/') {
            if (!array_key_exists('noclose', $fa_html_tags[$tag])
                && array_key_exists($tag, $fa_tag_counts)
                && ($fa_tag_counts[$tag] > 0)) $fa_tag_counts[$tag]--;
            return "</$tag>";
        } else {
            $makelink = array_key_exists('makelink', $fa_html_tags[$tag]);
            $output = ($makelink ? '<a' : "<$tag");
            preg_match_all('/(\w+)(?:=("[^"]*"|\'[^\'+]|\S+))?/', $tmatch[3],
                $pmatch, PREG_SET_ORDER);

            foreach ($pmatch as $param) {
                $p = strtolower($param[1]);

                if ($makelink && ($fa_html_tags[$tag]['makelink'] == $p)) {
                    $url = $param[2];
                    $output .= ' href=' . $param[2];
                } elseif (array_key_exists('allow', $fa_html_tags[$tag]) &&
                        array_key_exists($p, $fa_html_tags[$tag]['allow'])) {
                    if ($param[2]) {
                        $output .= " $p=" . $param[2];
                    } else {
                        $output .= " $p";
                    }
                }
            }

            if (!array_key_exists('noclose', $fa_html_tags[$tag]))
                $fa_tag_counts[$tag]++;
            if ($makelink) return ($url ? "[ $output>$url</a> ]" : '');
            return ($output . ($tmatch[4] ? '/>' : '>'));
        }
    }

###############################################################################
# FUNCTION:     cleanup_post
#
# ARGS: input   The text to be cleaned up
#       html    TRUE if HTML is allowed
#       auto    TRUE if the post is to be auto-formatted
#
# RETURNS:      A sparkly-clean version of the post.
#
# This function performs cleanup on a posting, removing invalid HTML tags and
# ensuring that they're all closed, and converting problem characters into
# entities.
###############################################################################
    function cleanup_post($input, $html, $auto) {
        global $fa_tag_counts;

        $pos = 0;
        $len = strlen($input);
        $output = '';
        $fa_tag_counts = array();

        while ($pos < $len) {
            $tag = strpos($input, '<', $pos);

            if ($tag === FALSE) {
                $output .= auto_format(substr($input, $pos), $auto);
                $pos = $len;
            } else {
                $output .= auto_format(substr($input, $pos, ($tag - $pos)),
                    $auto);
                $pos = strpos($input, '>', $tag) + 1;

                if ($pos === FALSE) {
                    $pos = $len;
                } else {
                    if ($html) $output .= cleanup_tag(
                        substr($input, $tag + 1, $pos - $tag - 2));
                }
            }
        }

        foreach ($fa_tag_counts as $tag => $count) {
            for ($i = 0; $i < $count; $i++) {
                $output .= "</$tag>";
            }
        }

        return $output;
    }

###############################################################################
# FUNCTION:     can_post
#
# ARGS: action  Self-URL for use in GETs and POSTs
#
# RETURNS:      TRUE if the user is logged in and can post, FALSE otherwise
#
# This function checks to see if the user is logged in and verified. If not, it
# outputs an appopriate message and possibly a login form.
###############################################################################
    function can_post($action) {
        global $fa_disable_posting, $fa_login_failed, $fa_server_path;

        if ($fa_disable_posting) {
            echo '<p class="error">Sorry, but all posting to the boards has ',
                "been disabled.</a></p>\n";
            return FALSE;
        } elseif (!$_SESSION['user'] || !$_SESSION['seen_password']) {
            if ($fa_login_failed) {
                echo '<p class="error">Your login failed - if you have ',
                    'forgotten your password, we have a <a href="',
                    $fa_server_path, '/login.php">password reminder form</a>.',
                    "</p>\n";
            } elseif (!$_SESSION['seen_password']) {
                echo '<p class="warning">You have not entered your password ',
                    'yet during this browser session - please enter it now ',
                    "to allow you to post to the boards</p>\n";
            } else {
                echo '<p class="warning">You need to log in to post to the ',
                    "boards. If you don't have a username and password, ",
                    'then please <a href="', $fa_server_path,
                    '/profile/register.php">visit our registration page</a>.',
                    "</p>\n";
            }

            print_login_form($action);
            return FALSE;
        } elseif (!fa_is_verified()) {
            echo '<p class="error">You must verify your account before you ',
                'may post to the boards. Visit the <a href="', $fa_server_path,
                '/profile/">profile page</a> and ensure that you have set an ',
                'email address on your account and enter the verification ',
                "code from a confirmation email.</p>\n";
            return FALSE;
        } else {
            return TRUE;
        }
    }

###############################################################################
# FUNCTION:     display_comment
#
# ARGS: comment Associative array containing the details of the comment
#       action  Self-URL for use in GETs and POSTs
#       name    Name for a comment, eg 'comment' or 'review'
#       noreply If set, don't include reply/update links
#
# RETURNS:      Nothing
#
# This function renders a single comment.
###############################################################################
    function display_comment($comment, $action, $name, $noreply) {
        global $fa_disable_posting, $fa_server_path;
        $noreply = $noreply | $fa_disable_posting;

        echo '<a name="', $comment['id'], '"></a>', "\n";
        echo '<table class="comment"', ($comment['depth']
            ? (' style="margin-left: ' . $comment['depth'] . 'em;">')
            : '>'), "\n";

        if ($comment['admin'] && !fa_has_mod_access()) {
            echo '<tr class="alert"><td>This comment is from the ',
                "moderators' private message board.</td></tr>\n";
        } elseif ($comment['deleted']) {
            echo '<tr class="alert"><td>This ', $name, ' has been deleted';
            if (!$noreply && (($comment['user'] == $_SESSION['user']) ||
                fa_has_mod_access())) echo ' - <a href="', $action,
                    '&amp;edit=', $comment['id'], '">View it</a>';
            echo "</td></tr>\n";
        } elseif ($comment['usertype'] == 'disabled') {
            echo '<tr class="alert"><td>This ', $name, ' was posted by a user',
                ' who has since been disabled';
            if (!$noreply && (($comment['user'] == $_SESSION['user']) ||
                fa_has_mod_access())) echo ' - <a href="', $action,
                    '&amp;edit=', $comment['id'], '">View it</a>';
            echo "</td></tr>\n";
        } else {
            if ($comment['re_text'] && $comment['re_url']) {
                echo '<tr class="re"><td colspan="' . ($comment['avatar'] ? 3
                    : 2) . '">Re: <a href="' . $comment['re_url'] . '#'
                    . $comment['id'] . '">' . $comment['re_text']
                    . "</a></td></tr>\n";
            }

            echo '<tr class="header">';
            $rows = 2;
            if (!$noreply) $rows++;
            if (array_key_exists('new', $comment)) $rows++;
            if ($comment['edited']) $rows++;

            if ($comment['avatar']) echo '<td class="avatar" rowspan="', $rows,
                '"><img src="../avatars/', $comment['avatar'], '"></td>';
            echo '<th><a href="', $fa_server_path, '/profile.php?user=',
                $comment['user'], '">', $comment['username'], '</a>',
                ($comment['subject'] ? (': ' . $comment['subject']) : ''),
                '</th><td>', short_datetime($comment['time']), "</td></tr>\n";

            if (array_key_exists('new', $comment)) {
                echo '<tr class="new">', ($comment['new'] ? '<td>' :
                    '<td colspan="2">'), "This $name is new.</td>";
                if ($comment['new']) echo '<td class="next"><a href="#',
                    $comment['new'], '">View next</a></td>';
                echo "</tr>\n";
            }

            if ($comment['edited']) {
                echo '<tr class="alert"><td colspan="2">This ', $name,
                    ' has been edited by ';

                if ($comment['edited'] == 1) {
                    echo 'the poster';
                } elseif ($comment['edited'] == 2) {
                    echo 'a moderator';
                } else {
                    echo 'the poster and by a moderator';
                }

                echo ".</td></tr>\n";
            }

            echo '<tr><td colspan="2">', $comment['body'], "</td></tr>\n";

            if (!$noreply) {
                echo '<tr class="footer">';

                if ($comment['user'] == $_SESSION['user']) {
                    echo '<td class="edit"><a href="', $action, '&amp;edit=',
                        $comment['id'], '">Edit/Delete</a></td><td>';
                } elseif (fa_has_mod_access()) {
                    echo '<td class="edit"><a href="', $action, '&amp;edit=',
                        $comment['id'], '">Moderate</a></td><td>';
                } else {
                    echo '<td colspan="2">';
                }

                echo '<a href="', $action, '&amp;reply=', $comment['id'],
                    '">Reply to this</a></td></tr>', "\n";
            }
        }

        echo "</table>\n";
    }

###############################################################################
# FUNCTION:     display_linked_comment
#
# ARGS: comment Associative array containing the details of the comment
#       action  Self-URL for use in GETs and POSTs
#       name    Name for a comment, eg 'comment' or 'review'
#       noreply If TRUE, don't include reply/update links
#       author  If TRUE, the names of story authors will be displayed
#
# RETURNS:      Nothing
#
# This function renders a single comment, calculating and including a link to
# the comment in its original context.
###############################################################################
function display_linked_comment($comment, $action, $name, $noreply, $author) {
    $comment['depth'] = 0;

    if ($comment['type'] == 'thread') {
        $result = mysql_query('
            SELECT      board,
                        subject
            FROM        fa_thread
            WHERE       thread = ' . $comment['type_id']
        );

        if (list($board, $subject) = mysql_fetch_row($result)) {
            $comment['re_text'] = $subject;
            $comment['re_url'] = "thread.php?thread=" . $comment['type_id'];
            if ($board == 0) $comment['admin'] = 1;
        }

        mysql_free_result($result);
    } elseif ($comment['type'] == 'file') {
        $result = mysql_query('
            SELECT          fa_file.number,
                            fa_file.name,
                            fa_story.name,
                            fa_story.chapter' . ($author ? ',
                            fa_author.name' : '') . '
            FROM            fa_file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story'
                                . ($author ? '
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author'
                                : '') . '
            WHERE           fa_file.file = ' . $comment['type_id']
        );

        if (list($number, $file, $story, $chapter, $author)
                = mysql_fetch_row($result)) {
            $comment['re_text'] = $story;
            if ($chapter) $comment['re_text'] .= ' - ' .
                chapter_name($file, $chapter, $number);
            if ($author) $comment['re_text'] .= " <i>by</i> $author";
            $comment['re_url'] = "review.php?file="
                . $comment['type_id'];
        }

        mysql_free_result($result);
    } elseif ($comment['type'] == 'story') {
        if ($author) {
            $result = mysql_query('
                SELECT          fa_story.name,
                                fa_author.name
                FROM            fa_story
                  INNER JOIN    fa_author
                    ON          fa_author.author = fa_story.author
                WHERE           fa_story.story = '
                                    . $comment['type_id']
            );
        } else {
            $result = mysql_query('
                SELECT  name
                FROM    fa_story
                WHERE   story = ' . $comment['type_id']
            );
        }

        if (list($story, $author) = mysql_fetch_row($result)) {
            $comment['re_text'] = ($author ? "$story <i>by</i> $author"
                : $story);
            $comment['re_url'] = "review.php?story="
                . $comment['type_id'];
        }

        mysql_free_result($result);
    }

    display_comment($comment, NULL, $name, TRUE);
}

###############################################################################
# FUNCTION:     do_threads
#
# ARGS: board   The ID of the message board
#       action  Self-URL for use in GETs and POSTs
#       threadaction  URL of the page for displaying a thread
#
# RETURNS:      Nothing
#
# This function does the entire body of the main message board page in all its
# forms, inculding the list of threads and posting of new threads.
###############################################################################
    function do_threads($board, $action, $threadaction) {
        global $fa_disable_posting, $fa_server_path;

        if (strpos($action, '?') === FALSE) {
            $paramaction = "$action?";
        } else {
            $paramaction = "$action&amp;";
        }

###############################################################################
# Actually post a new thread.
###############################################################################
        if ($_POST['post'] && $_POST['subject']) {
            if (!can_post($action)) return;

            $subject = mysql_escape_string(cleanup_post($_POST['subject'],
                FALSE, FALSE));
            $body = mysql_escape_string(cleanup_post($_POST['body'],
                TRUE, !$_POST['noformat']));
            mysql_query("
                INSERT INTO fa_thread
                            (board, user, ipaddr, time, subject, body)
                VALUES      ($board, " . $_SESSION['user'] .
                            ", INET_ATON('" . $_SERVER['REMOTE_ADDR'] .
                            "'), NOW(), '$subject', '$body')
            ");
?>
<p>The thread has been successfully posted. You can
    <a href="<?=$action?>">return to the board</a> or
    <a href="<?=$threadaction?>thread=<?=mysql_insert_id()?>">view your new thread</a>.</p>
<?php
###############################################################################
# Display the new thread form.
###############################################################################
        } elseif ($_REQUEST['new'] || $_POST['post'] || $_POST['preview']) {
            if (!can_post($paramaction . 'new=thread')) return;

            if ($_POST['preview']) {
                echo "<h3>Preview Your New Thread:</h3>\n<h3>",
                    cleanup_post($_POST['subject'], FALSE, FALSE),
                    "</h3>\n<p>", cleanup_post($_POST['body'], TRUE,
                    !$_POST['noformat']), "</p>\n";
            } else {
                echo "<h3>Start a New Thread</h3>\n";
            }
?>
<form method="post" action="<?=$action?>">
<p><strong>Subject:</strong><br>
<input name="subject" size="60" value="<?=$_REQUEST['subject']?>"></p>
<p><strong>Initial Comment:</strong> <em>(Optional)</em><br>
<textarea name="body" cols="60" rows="12"><?=$_POST['body']?></textarea><br>
<input type="checkbox" name="noformat" id="noformat" <?=($_POST['noformat'] ? 'checked' : '')?>> <label for="noformat">Don't auto-format</label></p>
<p><input type="submit" name="preview" value="Preview"> <input type="submit" name="post" value="Start a new thread"></p>
</form>
<?php
###############################################################################
# Display the list of threads in a board.
###############################################################################
        } else {
            $perpage = 25;
            $user = (int)$_SESSION['user'];

            if (!$fa_disable_posting) {
                echo '<p class="commentlinks"><a href="', $paramaction,
                    'new=thread">Start a new thread</a>';
                if ($user) echo ' | Mark the <a href="', $paramaction,
                    'seen=threads">thread list</a> or <a href="', $paramaction,
                    'seen=comments">all comments</a> as seen';
                echo "</p>\n";
            }

            $page = (int)$_GET['page'];
            if ($page < 1) $page = 1;
            $skip = ($page - 1) * $perpage;

            if ($user) {
                if (($_REQUEST['seen'] == 'threads')
                        || ($_REQUEST['seen'] == 'comments')) {
                    $result = mysql_query("
                        SELECT  MAX(thread)
                        FROM    fa_thread
                    ");
                    list($seen_threads) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    if ($seen_threads) {
                        mysql_query("
                            REPLACE INTO    fa_last_thread
                                            (user, board, thread)
                            VALUES          ($user, $board, $seen_threads)
                        ");

                        if ($_REQUEST['seen'] == 'comments') mysql_query("
                UPDATE          fa_last_comment
                  INNER JOIN    fa_thread
                    ON          fa_thread.thread = fa_last_comment.id
                      AND       fa_thread.board = $board
                SET             fa_last_comment.comment = fa_thread.comment,
                                fa_last_comment.replies = fa_thread.replies
                WHERE           fa_last_comment.user = $user
                      AND       fa_last_comment.type = 'thread'
                            ");
                    }
                } else {
                    $result = mysql_query("
                        SELECT  thread
                        FROM    fa_last_thread
                        WHERE   user = $user
                          AND   board = $board
                    ");

                    if (mysql_num_rows($result) == 1)
                        list($seen_threads) = mysql_fetch_row($result);
                    mysql_free_result($result);
                }
            }

            $result = mysql_query("
                SELECT          SQL_CALC_FOUND_ROWS
                                fa_thread.thread,
                                fa_user.user,
                                fa_user.name,
                                fa_user.type,
                                fa_thread.time,
                                fa_thread.sticky,
                                fa_thread.deleted,
                                fa_thread.replies,
                                fa_last_comment.replies,
                                fa_thread.subject
                FROM            fa_thread
                  INNER JOIN    fa_user
                    ON          fa_user.user = fa_thread.user
                  LEFT JOIN     fa_last_comment
                    ON          fa_last_comment.user = $user
                      AND       fa_last_comment.type = 'thread'
                      AND       fa_last_comment.id = fa_thread.thread
                WHERE           fa_thread.board = $board"
                                    . (fa_has_mod_access() ? '' : "
                  AND           fa_thread.deleted = 0
                  AND           fa_user.type <> 'disabled'") . "
                ORDER BY        fa_thread.sticky DESC,
                                fa_thread.time DESC
                LIMIT           $skip, $perpage
            ");

            $results = mysql_found_rows();
            $pages = ceil($results / $perpage);

            if (mysql_num_rows($result) > 0) {
                $i = 1;
?>
<table id="threads">
<tr class="inverse"><th>User and Subject</th><th class="number">Replies</th><th class="time">Last Post</th></tr>
<?php
                while (list($thread, $user, $username, $usertype, $time,
                        $sticky, $deleted, $replies, $seen_replies, $subject)
                            = mysql_fetch_row($result)) {
                    if ($usertype == 'disabled') $deleted = 1;
                    echo '<tr class="', ($deleted ? 'deleted-' :
                        ($sticky ?  'sticky-' : '')), ($i ? 'odd' : 'even'),
                        '"><td><strong><a href="', $fa_server_path,
                        '/profile.php?user=', $user, '">', $username,
                        '</a></strong>: <a href="', $threadaction, 'thread=',
                        $thread, '">', $subject, '</a></th><td class="number">';

                    if ($seen_replies) {
                        if ($seen_replies < $replies) echo '(<strong>'
                            . ($replies - $seen_replies) . '</strong>) ';
                        echo $replies;
                    } elseif ($seen_threads && ($seen_threads < $thread)) {
                        echo "<strong>$replies</strong>";
                    } else {
                        echo $replies;
                    }

                    echo '</td><td class="time">', short_datetime($time),
                        "</td></tr>\n";
                    $i = 1 - $i;
                }

                if ($pages > 1) echo '<tr class="inverse">',
                    '<td class="pages" colspan="3">', page_links($page,
                    $pages, $paramaction), "</td></tr>\n";
                echo "</table>\n";
            } elseif($results) {
                echo '<p class="warning">There are no more threads on this ',
                    'board. <a href="', $paramaction, 'page=', $pages,
                    '">Go back</a> to the last page of threads.</p>', "\n";
            } else {
                echo '<p class="warning">There are no threads on this board ',
                    "at present.</p>\n";
            }

            mysql_free_result($result);
        }
    }

###############################################################################
# FUNCTION:     select_comments_full
#
# ARGS: queries Array of addition JOIN, WHERE and ORDER BY clauses
#       more    More SQL to apply to the whole union if multiple queries
#       page    Page number
#       perpage Number of results per page
#
# RETURNS:      A SQL result set containing the requested comments
#
# Selects comments. If multiple queries are given, does a UNION ALL of these
# queries and applies more to the combined query.
###############################################################################
    function select_comments_full($queries, $more, $page, $perpage) {
        if ($page) $more .=  ' LIMIT ' . (($page - 1) * $perpage)
            . ", $perpage";
        $selects = array();
        $first = 1;

        foreach ($queries as $query) {
            $selects[] = '
                SELECT ' . ($page && $first ? 'SQL_CALC_FOUND_ROWS' : '') . '
                                fa_comment.comment  AS id,
                                fa_comment.type     AS type,
                                fa_comment.id       AS type_id,
                                fa_comment.depth    AS depth,
                                fa_user.user        AS user,
                                fa_user.name        AS username,
                                fa_user.type        AS usertype,
                                fa_user.avatar      AS avatar,
                                fa_comment.time     AS time,
                                fa_comment.edited   AS edited,
                                fa_comment.deleted  AS deleted,
                                fa_comment.subject  AS subject,
                                fa_comment.body     AS body
                FROM            fa_comment
                  INNER JOIN    fa_user
                    ON          fa_user.user = fa_comment.user ' . $query;
            $first = 0;
        }

        if (count($selects) == 1) {
            return(mysql_query($selects[0] . ' ' . $more));
        } elseif (count($selects) > 1) {
            return(mysql_query('(' . implode(') UNION ALL (', $selects)
                . ') ' . $more));
        }
    }

###############################################################################
# FUNCTION:     select_comments
#
# ARGS: query   Addition JOIN, WHERE and ORDER BY clauses
#       page    Page number
#       perpage Number of results per page
#
# RETURNS:      A SQL result set containing the requested comments
#
# Does exactly what it says on the tin.
###############################################################################
    function select_comments($query, $page, $perpage) {
        return(select_comments_full(array($query), '', $page, $perpage));
    }

###############################################################################
# FUNCTION:     select_new
#
# ARGS: query   A SQL query which returns the IDs of the new comments in order
#
# RETURNS:      A linked list of new comment IDs
#
# Does exactly what it says on the tin.
###############################################################################
    function select_new($query) {
        $new = array();
        $result = mysql_query($query);

        while (list($id) = mysql_fetch_row($result)) {
            $new[$id] = 0;

            if ($last) {
                $new[$last] = $id;
            } else {
                $new['first'] = $id;
            }

            $last = $id;
        }

        mysql_free_result($result);
        return $new;
    }

###############################################################################
# FUNCTION:     lock_comments
#
# ARGS: type    Type of thread, eg 'thread', 'file', 'story'
#       id      ID of the thread

# RETURNS:      TRUE if the comments could be locked, otherwise FALSE.
#
# This function obtains an advisory lock on a thread of comments.
###############################################################################
    function lock_comments($type, $id) {
        $result = mysql_query("SELECT GET_LOCK('fa_comment_" . $type . '_'
            . $id . "', 10)");
        list($return) = mysql_fetch_row($result);
        mysql_free_result($result);
        return($return);
    }

###############################################################################
# FUNCTION:     unlock_comments
#
# ARGS: type    Type of thread, eg 'thread', 'file', 'story'
#       id      ID of the thread

# RETURNS:      Nothing
#
# This function releases an advisory lock on a thread of comments.
###############################################################################
    function unlock_comments($type, $id) {
        $result = mysql_query("DO RELEASE_LOCK('fa_comment_" . $type . '_'
            . $id . "')");
    }

###############################################################################
# FUNCTION:     cleanup_thread
#
# ARGS: type    Type of thread, eg 'thread', 'file', 'story'
#       id      ID of the thread

# RETURNS:      TRUE on success, FALSE on failure.
#
# This function performs cleanup on a thread, recalculating all of the position
# values.
###############################################################################
    function cleanup_thread($type, $id) {
        if (!lock_comments($type, $id)) return FALSE;
        $order = array();
        $parents = array();
        $max_comment = 0;
        $replies = 0;

        $result = mysql_query("
            SELECT      comment,
                        parent
            FROM        fa_comment
            WHERE       type = '$type'
              AND       id = $id
            ORDER BY    time
        ");

        while (list($comment, $parent) = mysql_fetch_row($result)) {
            $parents[$comment] = $parent;

            if ($parent == 0) {
                $order[] = $comment;
            } else {
                $ancestors = array();

                for ($i = $parent; $i != 0; $i = $parents[$i]) {
                    $ancestors[$parents[$i]] = 1;
                }

                for ($i = 0; $order[$i] != $parent; $i++) { }
                for ($i++; (($i < count($order)) &&
                    !$ancestors[$parents[$order[$i]]]); $i++) { }
                array_splice($order, $i, 0, $comment);
            }

            if ($comment > $max_comment) $max_comment = $comment;
            $replies++;
        }

        mysql_free_result($result);

        foreach ($order as $position => $comment) {
            mysql_query("
                UPDATE  fa_comment
                SET     position = $position
                WHERE   comment = $comment
            ");
        }

        if ($type == 'thread') mysql_query("
                UPDATE  fa_thread
                SET     comment = $max_comment,
                        replies = $replies
                WHERE   thread = $id
            ");

        unlock_comments($type, $id);
        return TRUE;
    }

###############################################################################
# FUNCTION:     do_comments
#
# ARGS: name    Name for a comment, eg 'comment' or 'review'
#       action  Self-URL for use in GETs and POSTs
#       type    Type of thread, eg 'thread', 'file', 'story'
#       id      ID of the thread
#
# RETURNS:      Nothing
#
# This massive function does the entire body of the comments page in all its
# forms, inculding the threaded comment display and all of the posting and
# updating functionality.
###############################################################################
    function do_comments($name, $action, $type, $id) {
        global $fa_disable_posting;

        if ($_REQUEST['cleanup'] && fa_has_mod_access())
            cleanup_thread($type, $id);

###############################################################################
# Post a new comment.
###############################################################################
        if ($_POST['post'] && $_POST['body']) {
            if (!can_post($action)) return;

            $auto = $_POST['noformat'] ? 0 : 1;
            $subject = mysql_escape_string(cleanup_post($_POST['subject'],
                FALSE, FALSE));
            $body = mysql_escape_string(cleanup_post($_POST['body'],
                TRUE, $auto));
            $parent = 0;
            $depth = 0;
            $pos = 0;

            lock_comments($type, $id);

            $result = mysql_query("
                SELECT  MAX(position) + 1 AS max_pos
                FROM    fa_comment
                WHERE   type = '$type'
                  AND   id = $id
                HAVING  max_pos IS NOT NULL
            ");

            if (mysql_num_rows($result) == 1)
                list($pos) = mysql_fetch_row($result);
            mysql_free_result($result);

            if ($_REQUEST['reply'] != 'new') {
                $result = mysql_query('
                    SELECT  type,
                            id,
                            comment,
                            depth + 1,
                            position
                    FROM    fa_comment
                    WHERE   comment = ' . (int)$_REQUEST['reply']
                );

                if (mysql_num_rows($result) == 1) {
                    list($board_type, $board_id, $parent, $depth, $ppos) =
                        mysql_fetch_row($result);
                    mysql_free_result($result);

                    if (($board_type != $type) || ($board_id != $id)) {
                        echo '<p class="error">You are attempting to reply ',
                            'to a ', $name, " from another board.</p>\n";
                        unlock_comments($type, $id);
                        return;
                    }
                } else {
                    echo '<p class="error">The ', $name, ' you are ',
                        "attempting to reply to could not be found.</p>\n";
                    unlock_comments($type, $id);
                    return;
                }

                $result = mysql_query("
                    SELECT  MIN(position) AS min_pos
                    FROM    fa_comment
                    WHERE   type = '$type'
                      AND   id = $id
                      AND   position > $ppos
                      AND   depth < $depth
                    HAVING  min_pos IS NOT NULL
                ");

                if (mysql_num_rows($result) == 1) {
                    list($pos) = mysql_fetch_row($result);
                    mysql_free_result($result);

                    mysql_query("
                        UPDATE  fa_comment
                        SET     position = position + 1
                        WHERE   type = '$type'
                          AND   id = $id
                          AND   position >= $pos
                    ");
                }
            }

            mysql_query("
                INSERT INTO fa_comment
                            (type, id, user, ipaddr, time, parent, depth,
                            position, subject, body, format)
                VALUES      ('$type', $id, " . $_SESSION['user'] .
                            ", INET_ATON('" . $_SERVER['REMOTE_ADDR'] .
                            "'), NOW(), $parent, $depth, $pos, ".
                            "'$subject', '$body', $auto)
            ");
            $comment = mysql_insert_id();

            unlock_comments($type, $id);

            if ($type == 'thread') {
                mysql_query("
                    UPDATE  fa_thread
                    SET     time = NOW(),
                            comment = $comment,
                            replies = replies + 1
                    WHERE   thread = $id
                ");
            } elseif ($type == 'story') {
                mysql_query("
                    UPDATE  fa_story
                    SET     reviews = reviews + 1
                    WHERE   story = $id
                ");
            }
?>
<p>Your <?=$name?> has been successfully posted. <a href="<?=$action?>#<?=mysql_insert_id()?>">Click here</a> to go back to the board.</p>
<?php
###############################################################################
# Display the reply to comment/new comment form.
###############################################################################
        } elseif ($_REQUEST['reply']) {
            if (!can_post($action . '&amp;reply=' . $_REQUEST['reply']))
                return;

            if ($_POST['preview']) {
                echo "<h3>Preview your $name:</h3>\n";
                display_comment(array('subject' => cleanup_post(
                    $_POST['subject'], FALSE, FALSE), 'body' => cleanup_post(
                    $_POST['body'], TRUE, !$_POST['noformat']), 'user' =>
                    $_SESSION['user'], 'username' => $_SESSION['username']),
                    $action, $name, TRUE);
            } elseif ($_REQUEST['reply'] == 'new') {
                echo '<h3>Post a new ', $name, "</h3>\n";
            } else {
                $result = mysql_query('
                    SELECT          fa_comment.type     AS board_type,
                                    fa_comment.id       AS board_id,
                                    fa_comment.comment  AS id,
                                    fa_user.user        AS user,
                                    fa_user.name        AS username,
                                    fa_user.type        AS usertype,
                                    fa_user.avatar      AS avatar,
                                    fa_comment.time     AS time,
                                    fa_comment.time     AS time,
                                    fa_comment.subject  AS subject,
                                    fa_comment.body     AS body
                    FROM            fa_comment
                      INNER JOIN    fa_user
                        ON          fa_user.user = fa_comment.user
                    WHERE           fa_comment.comment = '
                                        . (int)$_REQUEST['reply']
                );

                if (mysql_num_rows($result) == 1) {
                    $comment = mysql_fetch_assoc($result);
                    mysql_free_result($result);

                    if (($comment['board_type'] != $type) ||
                            ($comment['board_id'] != $id)) {
                        echo '<p class="error">You are attempting to reply ',
                            'to a ', $name, " from another board.</p>\n";
                        return;
                    }

                    echo '<h3>Reply to the following ', $name, "</h3>\n";
                    display_comment($comment, $action, $name, TRUE);
                } else {
                    echo '<p class="error">The ', $name, ' you are ',
                        "attempting to reply to could not be found.</p>\n";
                    return;
                }
            }
?>
<form method="post" action="<?=$action?>&amp;reply=<?=$_REQUEST['reply']?>">
<p><strong>Subject:</strong> <em>(Optional)</em><br>
<input name="subject" size="60" value="<?=$_REQUEST['subject']?>"></p>
<p><strong>Your <?=$name?>:</strong><br>
<textarea name="body" cols="60" rows="12"><?=$_POST['body']?></textarea><br>
<input type="checkbox" name="noformat" id="noformat" <?=($_POST['noformat'] ? 'checked' : '')?>> <label for="noformat">Don't auto-format</label></p>
<p><input type="submit" name="preview" value="Preview"> <input type="submit" name="post" value="Post your <?=$name?>"></p>
</form>
<?php
###############################################################################
# Update an existing comment.
###############################################################################
        } elseif ($_POST['update']) {
            $id = (int)$_REQUEST['edit'];
            echo "<h2>Update an existing $name</h2>\n";

            $result = mysql_query("
                SELECT  user,
                        edited,
                        deleted,
                        subject,
                        body
                FROM    fa_comment
                WHERE   comment = $id
            ");

            if (list($user, $edited, $deleted, $subject, $body)
                    = mysql_fetch_row($result)) {
                mysql_free_result($result);

                if (fa_has_mod_access()) {
                    if ($deleted & 4) {
                        echo '<p class="error">The thread containing this ',
                            'comment has been deleted, so you can no longer ',
                            "edit it.</p>\n";
                        return;
                    } else {
                        $who = (($user == $_SESSION['user']) ? 1 : 2);
                    }
                } elseif ($user == $_SESSION['user']) {
                    if ($deleted > 1) {
                        echo '<p class="error">This comment has been ',
                            'deleted by a moderator, so you can no longer ',
                            "edit it.</p>\n";
                        return;
                    } elseif ($edited > 1) {
                        echo '<p class="error">This comment has been ',
                            'edited by a moderator, so you can no longer ',
                            "edit it.</p>\n";
                        return;
                    } else {
                        $who = 1;
                    }
                } else {
                    echo '<p class="error">You do not have access to the ',
                        "comment you are attempting to edit.</p>\n";
                    return;
                }
            } else {
                echo '<p class="error">The comment you are attempting to ',
                    "edit could not be found.</p>\n";
                return;
            }

            $auto = $_POST['noformat'] ? 0 : 1;
            $subject = mysql_escape_string(cleanup_post($_POST['subject'],
                FALSE, FALSE));
            $body = mysql_escape_string(cleanup_post($_POST['body'],
                TRUE, $auto));

            mysql_query("
                UPDATE  fa_comment
                SET     edited = (edited | $who),
                        subject = '$subject',
                        body = '$body',
                        format = $auto
                WHERE   comment = $id
            ");

            echo '<p class="notice">The ', $name, ' was successfully ',
                "updated.</p>\n", '<p><a href="', "$action#$id",
                '">Back to the board.</a>', "\n";
###############################################################################
# Peform a comment deletion.
###############################################################################
        } elseif ($_POST['delete']) {
            $id = (int)$_REQUEST['edit'];
            echo "<h2>Delete an existing $name</h2>\n";

            $result = mysql_query("
                SELECT  user,
                        deleted
                FROM    fa_comment
                WHERE   comment = $id
            ");

            if (list($user, $deleted) = mysql_fetch_row($result)) {
                mysql_free_result($result);

                if ($user == $_SESSION['user']) {
                    if (($deleted > 1) && !fa_has_mod_access()) {
                        echo '<p class="warning">This ', $name, ' has ',
                            "already been deleted by a moderator.</p>\n";
                        return;
                    } else {
                        $who = 1;
                    }
                } elseif (fa_has_mod_access()) {
                    if ($deleted & 4) {
                        echo '<p class="warning">The thread containing this ',
                            $name, " has been deleted.</p>\n";
                        return;
                    } elseif ($deleted & 1) {
                        echo '<p class="warning">This ', $name, ' has ',
                            "already been deleted by the poster.</p>\n";
                        return;
                    } else {
                        $who = 2;
                    }
                } else {
                    echo '<p class="error">You do not have access to the ',
                        "$name you are attempting to delete.</p>\n";
                    return;
                }
            } else {
                echo '<p class="error">The ', $name, ' you are attempting to ',
                    "delete could not be found.</p>\n";
                return;
            }

            mysql_query("
                UPDATE  fa_comment
                SET     deleted = $who
                WHERE   comment = $id
            ");

            echo '<p class="notice">The ', $name, ' was successfully ',
                "deleted.</p>\n", '<p><a href="', "$action#$id",
                '">Back to the board.</a>', "\n";
###############################################################################
# Peform a comment un-deletion.
###############################################################################
        } elseif ($_POST['undelete']) {
            $id = (int)$_REQUEST['edit'];
            echo "<h2>Undelete an existing $name</h2>\n";

            $result = mysql_query("
                SELECT  user,
                        deleted
                FROM    fa_comment
                WHERE   comment = $id
            ");

            if (list($user, $deleted) = mysql_fetch_row($result)) {
                mysql_free_result($result);

                if ($user == $_SESSION['user']) {
                    if (($deleted > 1) && !fa_has_mod_access()) {
                        echo '<p class="error">This ', $name, ' can only be ',
                            "undeleted by a moderator.</p>\n";
                        return;
                    }
                } elseif (fa_has_mod_access()) {
                    if ($deleted & 4) {
                        echo '<p class="warning">The thread containing this ',
                            $name, " has been deleted.</p>\n";
                        return;
                    } elseif ($deleted & 1) {
                        echo '<p class="error">This ', $name, ' can only be ',
                            "undeleted by the poster.</p>\n";
                        return;
                    }
                } else {
                    echo '<p class="error">You do not have access to the ',
                        "$name you are attempting to undelete.</p>\n";
                    return;
                }
            } else {
                echo '<p class="error">The ', $name, ' you are attempting to ',
                    "undelete could not be found.</p>\n";
                return;
            }

            mysql_query("
                UPDATE  fa_comment
                SET     deleted = 0
                WHERE   comment = $id
            ");

            echo '<p class="notice">The ', $name, ' was successfully ',
                "undeleted.</p>\n", '<p><a href="', "$action#$id",
                '">Back to the board.</a>', "\n";
###############################################################################
# Display the 'edit comment' page, with possible (un)delete buttons.
###############################################################################
        } elseif ($_REQUEST['edit']) {
            $id = (int)$_REQUEST['edit'];
            echo "<h2>Edit an existing $name</h2>\n";

            $result = mysql_query("
                SELECT          fa_comment.user,
                                fa_user.name,
                                INET_NTOA(fa_comment.ipaddr),
                                fa_comment.edited,
                                fa_comment.deleted,
                                fa_comment.subject,
                                fa_comment.body,
                                fa_comment.format
                FROM            fa_comment
                  INNER JOIN    fa_user
                    ON          fa_user.user = fa_comment.user
                WHERE           fa_comment.comment = $id
            ");

            if (list($user, $username, $ipaddr, $edited, $deleted, $subject,
                    $body, $auto) = mysql_fetch_row($result)) {
                mysql_free_result($result);
                $canedit = 0;
                $candelete = ($deleted ? 0 : 1);
                $canundelete = (1 - $candelete);
                $body = auto_unformat($body, $auto);

                if (fa_has_mod_access()) {
                    if ($deleted & 4) {
                        echo '<p class="warning">The thread containing this ',
                            $name, " has been deleted.</p>\n";
                        $canundelete = 0;
                    } elseif (($deleted & 1) && ($user != $_SESSION['user'])) {
                        echo '<p class="warning">This comment has been ',
                           'deleted by the poster, so you can no longer ',
                           "edit it.</p>\n";
                        $canundelete = 0;
                    } else {
                        $canedit = 1;
                    }
                } elseif ($user == $_SESSION['user']) {
                    if ($deleted > 1) {
                        echo '<p class="warning">This comment has been ',
                           'deleted by a moderator, so you can no longer ',
                           "edit it.</p>\n";
                        $canundelete = 0;
                    } elseif ($edited > 1) {
                        echo '<p class="warning">This comment has been ',
                           'edited by a moderator, so you can no longer ',
                           "edit it.</p>\n";
                    } else {
                        $canedit = 1;
                    }
                } else {
                    echo '<p class="error">You do not have access to the ',
                        "comment you are attempting to edit.</p>\n";
                    return;
                }
            } else {
                echo '<p class="error">The comment you are attempting to ',
                    "edit could not be found.</p>\n";
                return;
            }

            echo '<form method="post" action="', $action, '&amp;edit=', $id,
                '">', "\n", '<p><strong>Poster: <a href="../profile.php?user=',
                $user, '">', $username, '</a></strong>';
            if (($ipaddr != '0.0.0.0') && fa_has_mod_access())
                echo " (IP address: $ipaddr)";
            echo "</p>\n";

            if ($canedit || $subject) {
                echo "<p><strong>Subject:</strong>";

                if ($canedit) {
                    echo "<br>\n", '<input name="subject" size="60" value="',
                        $subject, '">';
                } else {
                    echo " $subject";
                }

                echo "</p>\n";
            }

            echo "<p><strong>Body:</strong><br>\n";

            if ($canedit) {
                echo '<textarea name="body" cols="60" rows="12">', $body,
                    '</textarea><br><input type="checkbox" name="noformat" ',
                    'id="noformat"', ($auto ? '> ' : ' checked> '),
                    '<label for="noformat">', "Don't auto-format</label>";
            } else {
                echo $body;
            }

            echo "<p>\n<p>\n";
            if ($canedit) echo '<input type="submit" name="update" ',
                'value="Update">', "\n";
            if ($candelete) echo '<input type="submit" name="delete" ',
                'value="Delete">', "\n";
            if ($canundelete) echo '<input type="submit" name="undelete" ',
                'value="Undelete">', "\n";
            echo "</p>\n</form>\n";
###############################################################################
# Display the consolidated author feedback page for a story.
###############################################################################
        } elseif ($type == 'files') {
            $new = array();

            if ($_SESSION['user']) {
                $user = $_SESSION['user'];

                $result = mysql_query("
                    SELECT          fa_last_comment.comment
                    FROM            fa_file
                      INNER JOIN    fa_last_comment
                        ON          fa_last_comment.id = fa_file.file
                          AND       fa_last_comment.user = $user
                          AND       fa_last_comment.type = 'file'
                    WHERE           fa_file.story = $id
                ");

                if (list($comment) = mysql_fetch_row($result))
                    $new = select_new("
                        SELECT          fa_comment.comment
                        FROM            fa_file
                          INNER JOIN    fa_comment
                            ON          fa_comment.id = fa_file.file
                              AND       fa_comment.type = 'file'
                              AND       fa_comment.deleted = 0
                          LEFT JOIN     fa_last_comment
                            ON          fa_last_comment.id = fa_file.file
                              AND       fa_last_comment.user = $user
                              AND       fa_last_comment.type = 'file'
                        WHERE           fa_file.story = $id
                          AND           (fa_last_comment.comment IS NULL
                            OR           fa_last_comment.comment
                                            < fa_comment.comment)
                        ORDER BY        fa_file.number,
                                        fa_comment.position
                    ");

                mysql_free_result($result);
            }

            $result = mysql_query("
                SELECT  chapter
                FROM    fa_story
                WHERE   story = $id
            ");

            list($chapter) = mysql_fetch_row($result);
            mysql_free_result($result);

            $result = mysql_query("
                SELECT      file,
                            number,
                            name
                FROM        fa_file
                WHERE       story = $id
                ORDER BY    number
            ");

            if ($chapter && $new['first']) echo '<p class="commentlinks">',
                '<a href="#', $new['first'], '">Jump to first unread ', $name,
                "</a></p>\n";

            while (list($file, $number, $title) = mysql_fetch_row($result)) {
                $comments = select_comments("
                    WHERE       fa_comment.type = 'file'
                      AND       fa_comment.id = $file
                    ORDER BY    fa_comment.position
                ", 0, 0);

                $replies = mysql_num_rows($comments);
                $last = 0;
                $action = "review.php?file=$file";

                if ($chapter) {
                    if ($fa_disable_posting) {
                    echo '<h3 class="chapter">', chapter_name($title, $chapter,
                            $number), "</h3>\n";
                    } else {
                    echo '<h3 class="chapter"><a href="', $action,
                        '&amp;reply=new">', chapter_name($title, $chapter,
                            $number), "</a></h3>\n";
                    }
                } else {
                    if (!$fa_disable_posting) {
                        echo '<p class="commentlinks"><a href="', $action,
                            '&amp;reply=new">Post a new ', $name, '</a>';
                        if ($new['first']) echo ' | <a href="#', $new['first'],
                            '">Jump to first unread ', $name, '</a>';
                        echo "</p>\n";
                    }

                    if ($replies == 0)
                        echo '<p class="warning">There are no ', $name,
                            "s on this board at present.</p>\n";
                }

                while ($comment = mysql_fetch_assoc($comments)) {
                    if ($comment['id'] > $last) $last = $comment['id'];
                    if (array_key_exists($comment['id'], $new))
                        $comment['new'] = $new[$comment['id']];
                    display_comment($comment, $action, $name, FALSE);
                }

                if (!$chapter && !$fa_disable_posting)
                    echo '<p class="commentlinks"><a href="', $action,
                    '&amp;reply=new">Post a new ', $name, "</a></p>\n";
                mysql_free_result($comments);

                if ($last && $_SESSION['user']) mysql_query('
                    REPLACE INTO    fa_last_comment
                                    (user, type, id, comment, replies)
                    VALUES          (' . $_SESSION['user']
                                       . ", 'file', $file, $last, $replies)
                ");
            }

            mysql_free_result($result);
###############################################################################
# Display the set of comments.
###############################################################################
        } else {
            $new = array();

            if ($_SESSION['user']) {
                $result = mysql_query('
                    SELECT  comment
                    FROM    fa_last_comment
                    WHERE   user = ' . $_SESSION['user'] . "
                      AND   type = '$type'
                      AND   id = $id
                ");

                if (list($comment) = mysql_fetch_row($result))
                    $new = select_new("
                        SELECT      comment
                        FROM        fa_comment
                        WHERE       type = '$type'
                          AND       id = $id
                          AND       deleted = 0
                          AND       comment > $comment
                        ORDER BY    position
                    ");

                mysql_free_result($result);
            }

            if (fa_has_mod_access() && ($type == 'thread'))
                echo '<form method="post" action="', $action, '">', "\n";

            if (!$fa_disable_posting) {
                echo '<p class="commentlinks"><a href="', $action,
                    '&amp;reply=new">Post a new ', $name, '</a>';
                if (fa_has_mod_access() && ($type == 'thread'))
                    echo ' | <a href="', $action, '&amp;deletethread=1">',
                        'Delete this thread</a>';
                if (fa_has_admin_access() && ($type == 'thread'))
                    echo ' | <a href="', $action, '&amp;stickythread=1">Make ',
                        'this thread sticky</a>';
                if ($new['first']) echo ' | <a href="#', $new['first'],
                    '">Jump to first unread ', $name, '</a>';
            }

            if (fa_has_mod_access() && ($type == 'thread')) {
                $result = mysql_query("
                    SELECT      fa_board.board,
                                fa_board.name,
                                fa_thread.thread
                    FROM        fa_board
                      LEFT JOIN fa_thread
                        ON      fa_thread.board = fa_board.board
                          AND   fa_thread.thread = $id
                    ORDER BY    fa_board.name
                ");

                if (mysql_num_rows($result) > 1) {
                    echo "<br>\n", '<input type="submit" value="Move thread ',
                        'to board"> <select name="moveto">', "\n";

                    while (list($board_id, $board_name, $board_selected) =
                            mysql_fetch_row($result)) {
                        echo '<option value="', $board_id, '"',
                            ($board_selected ? ' selected>' : '>'),
                            $board_name, "</option>\n";
                    }

                    echo '</select>';
                }

                mysql_free_result($result);
            }

            echo "</p>\n";
            if (fa_has_mod_access() && ($type == 'thread')) echo "</form>\n";

            $result = select_comments("
                WHERE           fa_comment.type = '$type'
                  AND           fa_comment.id = $id
                ORDER BY        fa_comment.position
            ", 0, 0);

            $replies = mysql_num_rows($result);
            $last = 0;

            if ($replies > 0) {
                while ($comment = mysql_fetch_assoc($result)) {
                    if ($comment['id'] > $last) $last = $comment['id'];
                    if (array_key_exists($comment['id'], $new))
                        $comment['new'] = $new[$comment['id']];
                    display_comment($comment, $action, $name, FALSE);
                }
            } else {
                echo '<p class="warning">There are no ', $name,
                    "s on this board at present.</p>\n";
            }

            mysql_free_result($result);

            if (!$fa_disable_posting) echo '<p class="commentlinks"><a href="',
                $action, '&amp;reply=new">Post a new ', $name, "</a></p>\n";

            if ($last && $_SESSION['user']) mysql_query('
                REPLACE INTO    fa_last_comment
                                (user, type, id, comment, replies)
                VALUES          (' . $_SESSION['user']
                                    . ", '$type', $id, $last, $replies)
            ");
        }
    }

###############################################################################
# FUNCTION:     do_recent_full
#
# ARGS: name    Name for a comment, eg 'comment' or 'review'
#       author  If TRUE, author names should be displayed for files/stories
#       action  URL for appending 'page=n' to
#       queries JOIN and WHERE parts of an SQL query to find some comments
#       more    Additional SQL to apply to a union if multiple queries
#
# RETURNS:      Nothing
#
# This function displays a simple list of the most recent comments matching
# the criteria specified.
###############################################################################
    function do_recent_full($name, $author, $action, $queries, $more) {
        $perpage = 25;
        $page = (int)$_REQUEST['page'];
        if ($page < 1) $page = 1;

        $result = select_comments_full($queries, $more
            . ' ORDER BY time DESC', $page, $perpage);
        $results = mysql_found_rows();
        $pages = ceil($results / $perpage);

        if (mysql_num_rows($result) == 0) {
            if ($results == 0) {
                echo '<p class="warning">No recent ', $name,
                    "s were found.</p>\n";
            } else {
                echo '<p class="warning">No further ', $name,
                    's were found. <a href="', $action, 'page=', $pages, '">',
                    "Skip back</a> to the earliest ones.</p>\n";
            }

            mysql_free_result($result);
            return;
        }

        $links = ($pages > 1) ? ('<p class="commentlinks">' . page_links($page,
            $pages, $action) . "</p>\n") : '';
        echo $links;

        while ($comment = mysql_fetch_assoc($result)) {
            display_linked_comment($comment, NULL, $name, TRUE, $author);
        }

        mysql_free_result($result);
        echo $links;
    }

###############################################################################
# FUNCTION:     do_recent
#
# ARGS: name    Name for a comment, eg 'comment' or 'review'
#       author  If TRUE, author names should be displayed for files/stories
#       action  URL for appending 'page=n' to
#       query   JOIN and WHERE parts of an SQL query to find some comments
#
# RETURNS:      Nothing
#
# This function displays a simple list of the most recent comments matching
# the criteria specified.
###############################################################################
    function do_recent($name, $author, $action, $query) {
        do_recent_full($name, $author, $action, array($query), '');
    }
###############################################################################
###############################################################################
?>
