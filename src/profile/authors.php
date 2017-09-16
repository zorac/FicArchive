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
### FILE: profile/authors.php
###
### This page displays a list of authors associated with this user.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');

    require_once('display.php');
    require_once('init.php');
    require_once('profile.php');

    $page_title = 'User Profile - ' . $_SESSION['username'] . ' - Authors';
    include('header.php');
?>
<h2>User Profile: <?=$_SESSION['username']?></h2>
<p><a href="./">Back to the main Profile page</a></p>
<h3>Authors</h3>
<p>This page lists your author names which you have associated with this profile.</p>
<?php
    if (!fa_is_verified()) fa_error('Not verified', 'You cannot add authors ' .
        'to your profile unless you have a verified email address.');

    if ($_GET['remove']) {
        mysql_query('
            DELETE FROM fa_author_user
            WHERE       author = ' . (int)$_GET['remove'] . '
              AND       user = ' . $_SESSION['user']
        );
    } elseif ($_GET['private']) {
        mysql_query('
            UPDATE  fa_author_user
            SET     public = 0
            WHERE   author = ' . (int)$_GET['private'] . '
              AND   user = ' . $_SESSION['user']
        );
    } elseif ($_GET['public']) {
        mysql_query('
            UPDATE  fa_author_user
            SET     public = 1
            WHERE   author = ' . (int)$_GET['public'] . '
              AND   user = ' . $_SESSION['user']
        );
    } elseif ($_GET['id']) {
        $result = mysql_query("
            SELECT  author
            FROM    fa_author
            WHERE   author = " . (int)$_GET['id'] . "
              AND   email = '" . $_SESSION['email'] . "'
        ");

        if (list($id) = mysql_fetch_row($result)) {
            mysql_query('
                REPLACE INTO    fa_author_user
                                (author, user, verify)
                VALUES          (' . $id . ', ' . $_SESSION['user'] . ', NULL)
            ');
        } else {
            echo '<p class="warning">The specified author could not be added ',
                "to your profile.</p>\n";
        }

        mysql_free_result($result);
    } elseif ($_POST['name']) {
    }

    $authors = array();
    $pending = array();
    $verified = array();

    $result = mysql_query('
        SELECT          fa_author.author,
                        fa_author.name,
                        fa_author_user.public,
                        fa_author_user.verify
        FROM            fa_author_user
          INNER JOIN    fa_author
            ON          fa_author.author = fa_author_user.author
        WHERE           fa_author_user.user = ' . $_SESSION['user'] . '
        ORDER BY        fa_author.name
    ');

    while (list($id, $name, $public, $verify) = mysql_fetch_row($result)) {
        $author = '<strong><a href="../author.php?author=' . $id . '">' .
            $name . '</a></strong> [ <a href="authors.php?remove=' . $id .
            '"> Remove</a> ';

        if ($verify) {
            $pending[$id] = $author . ']';
        } elseif ($public) {
            $authors[$id] = $author . '| <a href="authors.php?private=' . $id .
                '">Make Private</a> ]';
        } else {
            $authors[$id] = $author . '| <a href="authors.php?public=' . $id .
                '">Make Public</a> ]';
        }
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT      author,
                    name
        FROM        fa_author
        WHERE       email = '" . $_SESSION['email'] . "'
        ORDER BY    name
    ");

    while (list($id, $name) = mysql_fetch_row($result)) {
        if ($pending[$id]) unset($pending[$id]);
        if (!$authors[$id]) $verified[$id] =
            '<strong><a href="../author.php?author=' . $id .  '">' .  $name .
            '</a></strong> [ <a href="?id=' . $id .  '">Add to Profile</a> ]';
    }

    mysql_free_result($result);

    if ($authors) echo '<p>', implode("<br>\n", $authors), "</p>\n";
    if ($pending) echo "<h3>Authors Pending Addition</h3>\n<p>These authors ",
        "are awaiting confirmation before they are added.</p>\n<p>",
        implode("<br>\n", $pending), "</p>\n";
    echo "<h3>Add an Author</h3>\n";
    if ($verified) echo '<p>You can add an author with the same email ',
        "address as your profile immediately:</p>\n<p>",
        implode("<br>\n", $verified), "</p>\n";
?>
<p>If you want to add an author to your profile and you still have access to
    the email address the author was registered, enter the author name below
    and hit 'Add'. A verification email will be sent to the author's address
    and once you confirm it, the author will be added to your profile. If you
    no longer have access to the email address, you'll need to contact the
    moderators.</p>
<form method="POST" action="authors.php">
<input name="name" size="40"> <input type="submit" value="Add">
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
