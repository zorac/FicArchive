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
### FILE: search_tips.php
###
### This file displays hints and tips for using the story search.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('init.php');

    $mode = $_GET['mode'];
    if (($mode != 'simple') && ($mode != 'boards')) $mode = 'advanced';

    if ($mode == 'boards') {
        $what = 'posts';
    } else {
        $what = 'stories';
    }

    if ($mode == 'simple') {
        $page_title = "Search Tips";
        include('header.php');
?>
<h2>Search Tips</h2>
<p>This page gives some tips on how to use the
    <a href="search.php?mode=simple">story search</a>. There are
    <a href="search_tips.php?mode=advanced">more options</a> available on the
    <a href="search.php?mode=advanced">advanced search</a> page.</a></p>
<?php
    } elseif ($mode == 'advanced') {
        $page_title = "Advanced Search Tips";
        include('header.php');
?>
<h2>Advanced Search Tips</h2>
<p>This page gives some tips on how to use the
    <a href="search.php?mode=advanced">advanced search</a> page.</p>
<?php
    } elseif ($mode == 'boards') {
        $page_title = "Boards - Search Tips";
        include('header.php');
?>
<h2>Search Tips</h2>
<p>This page gives some tips on how to use the
<a href="boards/search.php">message board search</a> page.</p>
<h3>What to Search</h3>
<p>You can choose which board(s) to search. More options may be available
    depending on what you were viewing when you followed the 'Search' link,
    allowing you to search specific boards or threads.</p>
<?php
    }
?>
<h3>Keywords</h3>
<p>This allows you to search for <?=$what?> that have any of the given words in
    the title, summary or list of pairings. You can use the following special
    characters to improve your search results:
<dl>
<dt>+required</dt>
<dd>Only find <?=$what?> matching the word 'required'.</dd>
<dt>-ignore</dt>
<dd>Do not return any <?=$what?> matching the word 'ignore'.</dd>
<dt>prefix*</dt>
<dd>Find <?=$what?> matching any word starting 'prefix' (for example prefixed
    or prefixes).</dd>
<dt>"this exact phrase"</dt>
<dd>Searches for the words 'this exact phrase' together.</dd>
</dl>
You can combine the <b>+</b> and <b>-</b> operators with the other ones.
    <?=($mode == 'simple') ? '' : "Use the 'Relevance' sort order to return most relevant matches first. "?>
    Words shorter than four letters will not be found, nor will very common words.</p>
<?php
    if ($mode == 'advanced') {
?>
<h3>Title and Author</h3>
<p>These two search options simply look for the given text in either the story
    title or the author's name.</p>
<h3>Categories</h3>
<p>You can select which story categories you want to search for. You can chose
    one of four ways to match the categories you select:
<dl>
<dt>All of</dt>
<dd>Returns only stories which are in all the given categories.</dd>
<dt>Any of</dt>
<dd>Searches for stories which are in any of the selected categories.</dd>
<dt>Only</dt>
<dd>Excludes all stories which are in categories other than the ones selected.</dd>
<dt>Exactly</dt>
<dd>Finds only stories which are in exactly the set of categories you chose.</dd>
</dl></p>
<?php
    }

    if ($mode == 'boards') {
?>
<h3>Poster</h3>
<p>This option simply looks for the given text in any part of the poster's name.</p>
<?php
    } else {
?>
<h3><?=($mode == 'advanced') ? 'Include ' : ''?>Pairings</h3>
<p>You can select the stories you want based on which pairings they contain.
    Enter as many search terms as you like, separated by commas. The formats
    you can use are as follows:</p>
<dl>
<dt>Character Pairings</dt>
<dd>The most simple form, for example 'Alice/Bob' or 'Tom/Dick/Harry'. The
    system will also recognize different names for the same character where
    possible, and the order of character names within a pairing is unimportant.</dd>
<dt>Gender Pairings</dt>
<dd>You can also search for pairings by the genders of the character, for
    example 'M/F', 'F/F' of 'M/M/F'. You can't mix character names and genders
    in the same pairing.</dd>
<dt>Wildcards</dt>
<dd>An asterisk can be used in place of specific names or genders, for example
    'Joe/*' will match any pairing featuring Joe, and 'M/M/*' will match any
    pairing featuring two male characters.</dd>
</dl>
<?php
        if ($mode == 'simple') {
?>
<p>By default stories will be found that contain at least one of the given
    pairings, but the tickbox allows you to specify that stories containing
    pairings other than the ones given will be rejected.</p>
<?php
        } else {
?>
<p>The All/Any/Only/Exactly options work in the same way as with Categories
    above. Note that Exact or Only searches are unlikely to work as expected if
    you use wildcards or mix names and genders.</p>
<h3>Exclude Pairings</h3>
<p>This will exclude stories which contain any of the given pairings from the
    search results. You can use any of the search term formats described in
    Include Pairings above.</p>
<?php
        }
    }

    if ($mode != 'simple') {
?>
<h3>Date</h3>
<p>This restricts the search to recent <?=$what?> within the given time period.</p>
<?php
    }

    if ($mode == 'advanced') {
?>
<h3>Word Count</h3>
<p>This option allows you specify a minimum or maximum number of words for the
    stories that are found.</p>
<?php
    }
?>
<h3>Sort by</h3>
<p>This allows you to choose what order you want the <?=$what?> to be returned in:
<ul>
<?php
    if ($mode != 'boards') {
?>
<li>Alphabetically by story title</li>
<li>Alphabetically by author name</li>
<li>By date of last update (newest first)</li>
<?php
    }

    if ($mode == 'advanced') echo "<li>By word count (longest first)</li>\n";
    if ($mode != 'simple') echo "<li>By relevance (see Keywords above)</li>\n";

    if ($mode == 'boards') {
?>
<li>By date of posting (newest first)</li>
<li>Alphabetically by poster's name</li>
<?php
    }

    echo ($mode == 'simple') ? "</ul></p>\n" : "</ul>\n";

    if ($mode != 'simple') {
?>
You can also reverse the sort order.</p>
<h3>Results per page</h3>
<p>You can specify how many results to return at a time. Fewer results per page
    is faster<?=($mode == 'advanced') ? '; "All results" pages can get very large' : ''?>.</p>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
