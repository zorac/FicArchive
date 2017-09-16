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
### FILE: search.php
###
### This file implements the story search and search results pages.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('categories.php');
    require_once('display.php');
    require_once('init.php');
    require_once('numbers.php');
    require_once('pairings.php');
    require_once('search.php');

###############################################################################
# Save a search.
###############################################################################
    if ($_POST['save']) {
        if (!$_SESSION['user']) fa_error('Cannot save', 'You have to be ' .
            'logged in to create saved searches.');
        $search = parse_search($_POST);
        $page_title = 'Save a Search';
        include('header.php');
?>
<h2>Save a Search</h2>
<p>This allows you to save a set of search parameters to your bookmarks, so
    that you can easily run the search again in future or share it with other
    users. Your search looks like this:</p>
<?=display_search($search['query'])?>
<form action="profile/bookmarks.php" method="post">
<input type="hidden" name="save_search" value="1">
<?=hidden_inputs($search['query'])?>
<p>To create a new saved search, enter a name and press Save.<br>
<input size="40" name="name"> <input type="submit" name="create" value="Save"><br>
<input type="checkbox" name="public" id="public">
    <label for="public">Make this saved search public</label></p>
<?php
        $result = mysql_query('
            SELECT      search,
                        name
            FROM        fa_bookmark_search
            WHERE       user = ' . $_SESSION['user'] . '
            ORDER BY    name
        ');

        if (mysql_num_rows($result) > 0) {
            echo '<p>To update an existing saved search, select it from the ',
                "list and press Update<br>\n", '<select name="search">', "\n";

            while (list($id, $name) = mysql_fetch_row($result)) {
                echo '<option value="', $id, '">', $name, "</option>\n";
            }

            echo '</select> <input type="submit" name="update" value="Update">',
                "</p>\n";
        }

        echo "</form>\n";
        include('footer.php');
        exit(0);
    }

###############################################################################
# Load a saved search, if any
###############################################################################
    if ($_REQUEST['saved']) {
        $result = mysql_query('
            SELECT  search,
                    user,
                    name,
                    query,
                    public
            FROM    fa_bookmark_search
            WHERE   search = ' . (int)$_REQUEST['saved']
        );

        list($saved, $user, $name, $query, $public) = mysql_fetch_row($result);
        mysql_free_result($result);

        if (!$saved) {
            fa_error('Search not found', 'The saved search you are trying to' .
                ' access could not be found.');
        } elseif (!$public && ($user != $_SESSION['user'])) {
            fa_error('Access Denied', 'The saved search you are trying to' .
                " access is another user's private search.");
        }

        $search = parse_search(unserialize($query));
    } else {
        $search = parse_search($_POST);
    }

###############################################################################
# Display the search results.
###############################################################################
    if (($_POST['search'] || $saved) && !$_REQUEST['edit']) {
        $page = $search['page'];
        $perpage = $search['perpage'];

        if ($search['where']) {
            $result = mysql_query('
                SELECT          SQL_CALC_FOUND_ROWS
                                fa_story.story          AS story_id,
                                fa_story.name           AS story_name,
                                fa_author.author        AS author_id,
                                fa_author.name          AS author_name,
                                fa_rating.name          AS rating_name,
                                fa_story.updated        AS updated,
                                fa_story.size           AS size,
                                fa_story.category_ids   AS category_ids,
                                fa_story.pairing_names  AS pairing_names,
                                fa_story.summary        AS summary,
                                fa_story.chapter        AS chapter,
                                fa_story.file_ids       AS file_ids,
                                fa_story.reviews        AS reviews
                FROM            fa_story
                  INNER JOIN    fa_author
                    ON          fa_author.author = fa_story.author
                  INNER JOIN    fa_rating
                    ON          fa_rating.rating = fa_story.rating
                WHERE           ' . implode('
                  AND           ', $search['where']) . '
                  AND           fa_story.hidden = 0
                ORDER BY        ' . $search['order'] . ($perpage ? ('
                LIMIT           ' . $search['skip'] . ', ' . $perpage) : '')
            ) or die(mysql_error());

            $results = mysql_found_rows();
            $pages = (($results > 0) && ($perpage > 0)) ?
                ceil($results / $perpage) : 1;

            $page_title = "Search Results";
            include('header.php');
            $links = '<p>';
            if ($pages > 1) $links .= page_links($page, $pages, NULL) . ' ';
            $links .= '<input type="submit" name="edit" value="Refine">';
            if ($_SESSION['user'])
                $links .= ' <input type="submit" name="save" value="Save">';
            $links .= "</p>\n";
            echo '<form action="search.php" method="post">', "\n",
                '<input type="hidden" name="search" value="again">', "\n";
            echo hidden_inputs($search['query']);

            if ($results == 0) {
                echo "<h2>No Results Found</h2>\n", '<p class="warning">',
                    'Unfortunately, no stories were found which matched all ',
                    "of your search criteria.</p>\n";
            # TODO past end of results
            } elseif ($pages > 1) {
                $minresult = 1 + (($page - 1) * $perpage);
                $maxresult = $minresult + $perpage - 1;
                if ($maxresult > $results) $maxresult = $results;

                if (mysql_num_rows($result) > 0) {
                    echo "<h2>Matches $minresult-$maxresult of $results</h2>\n",
                        $links;

                    while ($story = mysql_fetch_assoc($result)) {
                        echo story_display($story);
                    }
                } else {
                    echo '<p class="warning">No further matches were found.',
                        "</p>\n</form>\n";
                }
            } else {
                echo "<h2>$results Matches Found</h2>\n", $links;

                while ($story = mysql_fetch_assoc($result)) {
                    echo story_display($story);
                }
            }

            echo $links, "</form>\n";
            mysql_free_result($result);
            include('footer.php');
            exit(0);
        }
    }

    $mode = $_GET['mode'];
    if ($_POST['advanced'] || $_POST['edit']) $mode = 'advanced';
    if (($mode != 'simple') && ($mode != 'advanced')) $mode =
        ($_SESSION['options']['advanced_search'] ?  'advanced' : 'simple');

###############################################################################
# Display the advanced search form.
###############################################################################
    if ($mode == 'advanced') {
        $page_title = "Advanced Search";
        include('header.php');
        $query = $search['query'];

        if (!$query['incpair']) $query['incpair'] = search_pairings(
            $query['incgender'], $query['num_incpair']);
        if (!$query['excpair']) $query['excpair'] = search_pairings(
            $query['excgender'], $query['num_excpair']);
?>
<h2>Advanced Story Search</h2>
<p>This form allows you to search the archive for stories - supply as many or
    as few search condtions as you like to find the stories you're looking for.
    You can also see <a href="search_tips.php?mode=advanced">search tips</a> or
    the <a href="search.php?mode=simple">simple search</a> page.
    <?=($_SESSION['options']['advanced_search'] ? '' : 'To make this your default search page, <a href="profile/options.php">edit your profile</a>.')?></p>
<form action="search.php" method="post">
<table class="info">
<tr><th>Keyword(s)</th><td><input name="keywords" size="40" value="<?=$query['keywords']?>"/></td></tr>
<tr><th>Title</th><td><input name="title" size="40" value="<?=$query['title']?>"/></td></tr>
<tr><th>Author</th><td><input name="author" size="40" value="<?=$query['author']?>"/></td></tr>
<tr class="multitop"><th>Categories</th><td rowspan="2"><?=category_checkboxes(array_flip(explode(',', ',' . $query['categories'])))?></td></tr>
<tr class="multibot"><td><?=dropdown_box('category_mode', $fa_search_matching, $query['category_mode'])?></td></tr>
<tr><th>Include Pairing(s)</th><td><input name="incpair" size="40" value="<?=$query['incpair']?>"/> <?=dropdown_box('incpair_mode', $fa_search_matching, $query['incpair_mode'])?></td></tr>
<tr><th>Exclude Pairing(s)</th><td><input name="excpair" size="40" value="<?=$query['excpair']?>"/></td></tr>
<tr><th>Date</th><td><?=dropdown_box('date', $fa_search_dates, $query['date'])?></td></tr>
<tr><th>Word Count</th><td><?=dropdown_box('sizetype', $fa_search_leastmost, $query['sizetype'])?> <input name="size" size="8" value="<?=$query['size']?>"/></td></tr>
<tr><th>Sort by</th><td><?=dropdown_box('sort', $fa_search_sortby, $query['sort'])?> <?=dropdown_box('reverse', $fa_search_direction, $query['reverse'])?></td></tr>
<tr><th>Results per page</th><td><?=dropdown_box('perpage', $fa_search_perpage, $query['perpage'])?></td></tr>
<tr><td></td><td><input type="submit" name="search" value="Search"/> <input type="reset" value="Reset"/></td></tr>
<tr><td></td><td><a href="search_tips.php?mode=advanced">Advanced Search Tips</a></td></tr>
</table>
</form>
<?php
###############################################################################
# Display the simple search form.
###############################################################################
    } else {
        $page_title = "Search";
        include('header.php');
?>
<h2>Search the Archive</h2>
<p>This page allows you to search the archive for stories - specify the
    keywords and/or pairings you want to search for. You can also see
    <a href="search_tips.php?mode=simple">search tips</a> or the
    <a href="search.php?mode=advanced">advanced search</a> page.
    <?=($_SESSION['options']['advanced_search'] ? 'To make this your default search page, <a href="profile/options.php">edit your profile</a>.' : '')?></p>
<form action="search.php" method="post">
<table class="info">
<tr><th>Keyword(s)</th><td><input name="keywords" size="40"/></td></tr>
<tr><th>Pairing(s)</th><td><input name="incpair" size="40"/>
<tr><td></td><td><input type="checkbox" name="incpair_mode" value="only"/> Find stories with <i>only</i> these pairings.</td></tr>
<tr><th>Sort by</th><td><select name="sort"><option value="title">Story title</option><option value="author">Author's name</option><option value="date">Date of last update</option></select></td></tr>
<tr><td></td><td><input type="submit" name="search" value="Search for Stories"> <input type="submit" name="advanced" value="More options"></td></tr>
</table>
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
