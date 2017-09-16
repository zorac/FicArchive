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
### FILE: boards/search.php
###
### This file implements the message board search and search results pages.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('comments.php');
    require_once('init.php');

    function short_name($name) {
        if (strlen($name) <= 40) {
            return($name);
        } else {
            return(substr($name, 0, 39) . '&hellip;');
        }
    }

    $search_page = 'boards';
    $page_title = "Boards - Search";

###############################################################################
# Display the search results.
###############################################################################
    if ($_POST['search']) {
        $join = '';
        $where = array();
        $hidden = array();
        $name = 'comment';
        $author = TRUE;
        list($what, $id) = explode('=', $_POST['what']);

        if (($what == 'type') && (($id == 'thread') || ($id == 'review')
                || ($id == 'story') || ($id == 'file'))) {
            if ($id == 'review') {
                $where[] = "(fa_comment.type = 'story' "
                    . "OR fa_comment.type = 'file')";
            } else {
                $where[] = "fa_comment.type = '$id'";
            }
        } elseif (($what == 'file') || ($what == 'story')
                || ($what == 'thread')) {
            $where[] = "fa_comment.type = '$what'";
            $where[] = 'fa_comment.id = ' . (int)$id;
        } elseif ($what == 'files') {
            $join .= '
                INNER JOIN  fa_file
                  ON        fa_file.file = fa_comment.id
                    AND     fa_file.story = ' . (int)$id;
            $where[] = "fa_comment.type = 'file'";
        } elseif ($what == 'reviews') {
            $id = (int)$id;
            $join .= "
                LEFT JOIN   fa_file
                  ON        fa_file.file = fa_comment.id
                    AND     fa_file.story = $id";
            $where[] = "    ((fa_comment.type = 'file'
                  AND         fa_file.file IS NOT NULL)
                OR           (fa_comment.type = 'story'
                  AND         fa_comment.id = $id))";
        } elseif ($what == 'board') {
            $join .= '
                INNER JOIN  fa_thread
                  ON        fa_thread.thread = fa_comment.id
                    AND     fa_thread.board = ' . (int)$id;
            $where[] = "fa_comment.type = 'thread'";
        } elseif ($what == 'file_author') {
            $join .= '
                INNER JOIN  fa_file
                  ON        fa_file.file = fa_comment.id
                INNER JOIN  fa_story
                  ON        fa_story.story = fa_file.story
                    AND     fa_story.author = ' . (int)$id;
            $where[] = "fa_comment.type = 'file'";
        } elseif ($what == 'story_author') {
            $join .= '
                INNER JOIN  fa_story
                  ON        fa_story.story = fa_comment.id
                    AND     fa_story.author = ' . (int)$id;
            $where[] = "fa_comment.type = 'story'";
        } elseif ($what == 'review_author') {
            $id = (int)$id;
            $join .= "
                LEFT JOIN   fa_file
                  ON        fa_file.file = fa_comment.id
                LEFT JOIN   fa_story AS file_story
                  ON        file_story.story = fa_file.story
                    AND     file_story.author = $id
                LEFT JOIN   fa_story
                  ON        fa_story.story = fa_comment.id
                    AND     fa_story.author = $id";
            $where[] = '(file_story.story IS NOT NULL OR '
                . 'fa_story.story IS NOT NULL)';
        }

        if (count($where) > 0) $hidden['what'] = $_POST['what'];

        if ($_POST['keywords']) {
            $match = "MATCH (fa_comment.subject, fa_comment.body) AGAINST('" .
                mysql_escape_string($_POST['keywords']) . "' IN BOOLEAN MODE)";
            $where[] = $match;
            $hidden['keywords'] = $_POST['keywords'];
        }

        if ($_POST['poster']) {
            $where[] = "fa_user.name LIKE '%"
                . mysql_escape_string($_POST['poster']) . "%'";
            $hidden['poster'] = $_POST['poster'];
        }

        if (is_numeric($_POST['date'])) {
            $where[] = '(TO_DAYS(NOW()) - TO_DAYS(fa_comment.time)) <= '
                . (int)$_POST['date'];
            $hidden['date'] = (int)$_POST['date'];
        }

        if ($where) {
            if ($_POST['sort'] == 'poster') {
                $order = 'fa_user.name';
                if ($_POST['reverse']) $order .= ' DESC';
                $order .= ', fa_comment.time DESC';
                $hidden['sort'] = 'author';
            } elseif ($match && ($_POST['sort'] != 'date')) {
                $order = $match;
                if (!$_POST['reverse']) $order .= ' DESC';
                $order .= ', fa_comment.time DESC';
            } else {
                $order = 'fa_comment.time';
                if (!$_POST['reverse']) $order .= ' DESC';
                $hidden['sort'] = 'date';
            }

            if ($_POST['reverse']) $hidden['reverse'] = 1;

            foreach ($_POST as $key => $value) {
                if (strncmp($key, 'page', 4) == 0)
                    $page = (int)substr($key, 4);
            }

            if ($page < 1) $page = 1;
            $perpage = (int)$_POST['perpage'];
            if ($perpage < 10) $perpage = 10;
            if ($perpage > 50) $perpage = 50;
            $skip = ($page - 1) * $perpage;

            if (!fa_has_mod_access()) $where[] =
                "NOT (fa_comment.type = 'thread' AND fa_comment.id = 0)";
            $where[] = 'fa_comment.deleted = 0';

            $result = select_comments($join . '
                WHERE       ' . implode('
                  AND       ', $where) . "
                ORDER BY    $order
            ", $page, $perpage) or die(mysql_error());

            $results = mysql_found_rows();
            $pages = ceil($results / $perpage);

            $page_title .= ' Results';
            include('header.php');

            if ($results == 0) {
                echo "<h2>No Results Found</h2>\n", '<p class="warning">',
                    'Unfortunately, no ', $name, 's were found which matched ',
                    "all of your search criteria.</p>\n";
            } elseif ($perpage && ($perpage < $results)) {
                $hidden['search'] = 'again';
                $hidden['perpage'] = $perpage;
                $minresult = 1 + (($page - 1) * $perpage);
                $maxresult = $minresult + $perpage - 1;
                if ($maxresult > $results) $maxresult = $results;
                $links = '<p>' . page_links($page, $pages, NULL) . "</p>\n";
                echo '<form action="search.php" method="post">', "\n";

                foreach ($hidden as $key => $value) {
                    echo '<input type="hidden" name="', $key, '" value="',
                        str_replace('"', '&quot;', $value), '">', "\n";
                }

                if (mysql_num_rows($result) > 0) {
                    echo "<h2>Matches $minresult-$maxresult of $results</h2>\n",
                        $links;

                    while ($comment = mysql_fetch_assoc($result)) {
                        echo display_linked_comment($comment, NULL, $name,
                            TRUE, $author);
                    }

                    echo $links, "</form>\n";
                } else {
                    echo '<p class="warning">No further matches were found.',
                        "</p>\n</form>\n";
                }
            } else {
                echo "<h2>$results Matches Found</h2>\n";

                while ($comment = mysql_fetch_assoc($result)) {
                    echo display_linked_comment($comment, NULL, $name, TRUE,
                        $author);
                }
            }

            mysql_free_result($result);
            include('footer.php');
            exit(0);
        }
    }

###############################################################################
# Display the search form.
###############################################################################
    include('header.php');

    if ($_REQUEST['file']) {
        $result = mysql_query('
            SELECT          fa_author.author,
                            fa_author.name,
                            fa_story.story,
                            fa_story.name,
                            fa_story.chapter,
                            fa_file.file,
                            fa_file.number,
                            fa_file.name
            FROM            fa_author
              INNER JOIN    fa_story
                ON          fa_story.author = fa_author.author
              INNER JOIN    fa_file
                ON          fa_file.story = fa_story.story
                  AND       fa_file.file = ' . (int)$_REQUEST['file']
        ) or die(mysql_error());

        if (mysql_num_rows($result) == 1) {
            list($author, $author_name, $story, $story_name, $chapter, $file,
                $number, $file_name) = mysql_fetch_row($result);
            $author_name = short_name($author_name);
            $story_name = short_name($story_name);

            if ($chapter) {
                $file_name = short_name(
                    chapter_name($file_name, $chapter, $number));
            } else {
                $file = 0;
            }
        }

        mysql_free_result($result);
    } elseif ($_REQUEST['story']) {
        $result = mysql_query('
            SELECT          fa_author.author,
                            fa_author.name,
                            fa_story.story,
                            fa_story.name
            FROM            fa_author
              INNER JOIN    fa_story
                ON          fa_story.author = fa_author.author
                  AND       fa_story.story = ' . (int)$_REQUEST['story']
        );

        if (mysql_num_rows($result) == 1) {
            list($author, $author_name, $story, $story_name)
                = mysql_fetch_row($result);
            $author_name = short_name($author_name);
            $story_name = short_name($story_name);
        }

        mysql_free_result($result);
    } elseif ($_REQUEST['author']) {
        $result = mysql_query('
            SELECT  author,
                    name
            FROM    fa_author
            WHERE   author = ' . (int)$_REQUEST['author']
        );

        if (mysql_num_rows($result) == 1) {
            list($author, $author_name) = mysql_fetch_row($result);
            $author_name = short_name($author_name);
        }

        mysql_free_result($result);
    }

    $searches = array(
        'All Message Boards'    => '',
        '-All Review Boards'    => 'type=review',
    );

    if ($author) {
        $selected = '--All Reviews for ' . $author_name;
        $searches[$selected] = "review_author=$author";

        if ($story) {
            $selected = '---The Story "' . $story_name . '"';
            $searches[$selected] = "reviews=$story";
        }
    }

    $searches['--All Reader Review Boards'] = 'type=story';

    if ($author) {
        $tmp = '---Reader Reviews for ' . $author_name;
        $searches[$tmp] = "story_author=$author";
        if ($_REQUEST['type'] == 'story') $selected = $tmp;

        if ($story) {
            $tmp = '----The Story "' . $story_name . '"';
            $searches[$tmp] = "story_story=$story";
            if ($_REQUEST['type'] == 'story') $selected = $tmp;
        }
    }

    $searches['--All Author Feedback Boards'] = 'type=file';

    if ($author) {
        $tmp = '---Feedback for ' . $author_name;
        $searches[$tmp] = "file_author=$author";
        if ($_REQUEST['type'] == 'file') $selected = $tmp;

        if ($story) {
            $tmp = '----The Story "' . $story_name . '" ';
            $searches[$tmp] = "files=$story";
            if ($_REQUEST['type'] == 'file') $selected = $tmp;

            if ($file) {
                $selected = '-----' . $file_name;
                $searches[$selected] = "file=$file";
            }
        }
    }

    $searches['-All Discussion Boards'] = 'type=thread';

    if ($_REQUEST['thread']) {
        $id = (int)$_REQUEST['thread'];

        $result = mysql_query("
            SELECT          fa_thread.subject,
                            fa_board.board,
                            fa_board.name
            FROM            fa_thread
              INNER JOIN    fa_board
                ON          fa_board.board = fa_thread.board
            WHERE           fa_thread.thread = $id
        ");

        if (mysql_num_rows($result) == 1) {
            list($subject, $board, $name) = mysql_fetch_row($result);

            if ($board > 0) {
                $searches['--The Board "' . short_name($name) . '"']
                    = "board=$board";
                $selected = '---The Thread "' . short_name($subject) . '"';
                $searches[$selected] = "thread=$id";
            } elseif (fa_has_mod_access()) {
                $selected = '--The Thread "' . short_name($subject) . '"';
                $adminthread = "thread=$id";
            }
        }

        mysql_free_result($result);
    } elseif ($_REQUEST['board']) {
        $id = (int)$_REQUEST['board'];

        if ($id > 0) {
            $result = mysql_query("
                SELECT  name
                FROM    fa_board
                WHERE   board = $id
            ");

            if (mysql_num_rows($result) == 1) {
                list($name) = mysql_fetch_row($result);
                $selected = '--The Board "' . short_name($name) . '"';
                $searches[$selected] = "board=$id";
            }

            mysql_free_result($result);
        }
    }

    if (fa_has_mod_access()) {
        $searches["-The Moderators' Private Board"] = 'board=admin';
        if ($adminthread) $searches[$selected] = $adminthread;
    }

    $select = '';

    foreach ($searches as $name => $value) {
        $select .= '<option value="' . $value . (($name == $selected) ?
            '" selected>' : '">') . $name . "</option>\n";
    }
?>
<h2>Search the Message Boards</h2>
<p>This form allows you to search the review and/or discussion boards. Enter as
    many or as few conditions as you like - see the
    <a href="../search_tips.php?mode=boards">search tips</a> page for more
    information. You can also <a href="../search.php">search for stories</a>.</p>
<form action="search.php" method="post">
<table class="info">
<tr><th>What to Search</th><td><select name="what">
<?=$select?></select></td></th>
<tr><th>Keyword(s)</th><td><input name="keywords" size="40"></td></tr>
<tr><th>Poster</th><td><input name="poster" value="<?=$_REQUEST['poster']?>" size="40"></td></tr>
<tr><th>Date</th><td><select name="date"><option value="">All</option><option value="7">Last 7 Days</option><option value="30">Last 30 Days</option><option value="92">Last 3 Months</option><option value="184">Last 6 months</option><option value="366">Last Year</option></select></td></tr>
<tr><th>Sort by</th><td><select name="sort"><option value="">Relevance</option><option value="date">Date</option><option value="poster">Poster</option></select> <select name="reverse"><option value="">Normal</option><option value="1">Reverse</option></select></td></tr>
<tr><th>Results per page</th><td><select name="perpage"><option>10</option><option>25</option><option>50</option></select></td></tr>
<tr><td></td><td><input type="submit" name="search" value="Search"> <input type="reset" value="Reset"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
