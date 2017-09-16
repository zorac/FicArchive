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
### FILE: profile.php
###
### This page displays a user's public profile.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('date.php');
    require_once('init.php');

    $user = $_GET['user'];
    if (!$user) $user = $_SESSION['user'];
    if (!$user) fa_error('User Profile Not Found',
        'The user profile you requested could not be found.');

    $result = mysql_query('
        SELECT  user,
                name,
                email,
                dob,
                type,
                profile,
                avatar
        FROM    fa_user
        WHERE   user = ' . (int)$user
    );

    if (mysql_num_rows($result) != 1) fa_error('User Profile Not Found',
        'The user profile you requested could not be found.');

    list($user, $name, $email, $dob, $type, $profile, $avatar) =
        mysql_fetch_row($result);
    if ($profile) $profile = unserialize($profile);
    mysql_free_result($result);

    if ($type == 'disabled') fa_error('User Disabled',
        "This user has been disabled and their profile may not be viewed.");
    if (!$profile['all'] && !$_SESSION['user']) fa_error('Access Denied',
        "This user's profile may only be viewed by registered users.");

    $page_title = "User Profile - $name";
    include('header.php');
    if ($avatar) echo '<img class="avatar" src="avatars/', $avatar, '">', "\n";
    echo "<h2>User Profile: $name</h2>\n";

    if ($user == $_SESSION['user']) {
        echo '<p>This is your public profile as other users will see it. You ',
            'can also <a href="profile/public.php">edit your profile</a>.',
            "</p>\n";
    } else {
        echo "<p>This is the public profile for $name, containing details ",
            "which they have chosen to share with you.</p>\n";
    }

    echo '<table class="info">', "\n";

    if ($profile['name']) echo '<tr><th>Name</th><td>', $profile['name'],
        "</td></tr>\n";

    if ($profile['dob'] > 0) echo '<tr><th>Birthday</th><td>',
        long_date($dob, ($profile['dob'] == 2)), "</td></tr>\n";

    if ($profile['location']) echo '<tr><th>Location</th><td>',
        $profile['location'], "</td></tr>\n";

    if ($profile['www']) echo '<tr><th>Web Site</th><td><a href="',
        $profile['www'], '">', $profile['www'], "</a></td></tr>\n";

    if ($profile['email'] > 0) {
?>
<tr><th>Email Address</th><td><a href="mailto:<?=(($profile['email'] == 2) ? fa_protect_email_address($email) : $email)?>"><?=$email?></a></td></tr>
<?php
    }

    if ($profile['yahoo']) {
?>
<tr><th>Yahoo! ID</th><td><img src="http://opi.yahoo.com/online?u=<?=$profile['yahoo']?>&amp;m=g&amp;t=0" witdth="12" height="12" class="usericon"> <a href="http://profiles.yahoo.com/<?=$profile['yahoo']?>"><?=$profile['yahoo']?></a> [ <a href="http://edit.yahoo.com/config/send_webmesg?.target=<?=$profile['yahoo']?>&amp;.src=pg">Send Message</a> | <a href="http://edit.yahoo.com/config/set_buddygrp?.src=&amp;.cmd=a&amp;.bg=Friends&amp;.bdl=<?=$profile['yahoo']?>">Add Buddy</a> ]</td></tr>
<?php
    }

    if ($profile['aol']) {
?>
<tr><th>AOL Screen Name</th><td><img src="http://big.oscar.aol.com/<?=$profile['aol']?>?on_url=http://www.aol.com/aim/gr/online.gif&amp;off_url=http://www.aol.com/aim/gr/offline.gif" width="14" height="17" class="usericon"> <?=$profile['aol']?> [ <a href="aim:goim?screenname=<?=$profile['aol']?>">Send Message</a> | <a href="aim:addbuddy?screenname=<?=$profile['aol']?>">Add Buddy</a> ]</td></tr>
<?php
    }

    if ($profile['icq']) {
?>
<tr><th>ICQ UIN</th><td><img src="http://web.icq.com/whitepages/online?icq=<?=$profile['icq']?>&amp;img=5" width="18" height="18" class="usericon"> <?=$profile['icq']?> [ <a href="http://wwp.icq.com/scripts/contact.dll?msgto=<?=$profile['icq']?>">Send Message</a> | <a href="http://wwp.icq.com/scripts/search.dll?to=<?=$profile['aim']?>">Add Buddy</a> ]</td></tr>
<?php
    }

    if ($profile['msn']) echo '<tr><th>MSN Username</th><td>',
        $profile['msn'], "</td></tr>\n";

    if ($profile['lj']) {
?>
<tr><th>LiveJournal</th><td><a href="http://www.livejournal.com/userinfo.bml?user=<?=$profile['lj']?>"><img src="http://stat.livejournal.com/img/userinfo.gif" width="17" height="17" class="usericon"></a><a href="http://www.livejournal.com/users/<?=$profile['lj']?>/"><b><?=$profile['lj']?></b></a></td></tr>
<?php
    }

    if ($profile['bio']) echo '<tr><th>Biography</th><td>', $profile['bio'],
        "</td></tr>\n";

    $result = mysql_query("
        SELECT      search,
                    name
        FROM        fa_bookmark_search
        WHERE       user = $user
          AND       public = 1
        ORDER BY    name
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<tr><th>Saved Searches</th><td>', "\n";
        $first = 1;

        while (list($search, $name, $public) = mysql_fetch_row($result)) {
            if ($first) {
                $first = 0;
            } else {
                echo '<br \>';
            }

            echo '<a href="../search.php?saved=', $search, '">', $name,
                "</a>\n";
        }

        echo "</td></tr>\n";
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_author.author,
                        fa_author.name
        FROM            fa_author
          INNER JOIN    fa_bookmark_author
            ON          fa_bookmark_author.author = fa_author.author
              AND       fa_bookmark_author.user = $user
              AND       fa_bookmark_author.public = 1
        ORDER BY        fa_author.name
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<tr><th>Author Bookmarks</th><td>', "\n";
        $first = 1;

        while (list($author, $name, $public) = mysql_fetch_row($result)) {
            if ($first) {
                $first = 0;
            } else {
                echo '<br \>';
            }

            echo '<a href="../author.php?author=', $author, '">', $name,
                "</a>\n";
        }

        echo "</td></tr>\n";
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_story.story,
                        fa_story.name,
                        fa_author.author,
                        fa_author.name
        FROM            fa_story
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
          INNER JOIN    fa_bookmark_story
            ON          fa_bookmark_story.story = fa_story.story
              AND       fa_bookmark_story.user = $user
              AND       fa_bookmark_story.public = 1
        WHERE           fa_story.hidden = 0
        ORDER BY        fa_story.name
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<tr><th>Story Bookmarks</th><td>', "\n";
        $first = 1;

        while (list($story, $title, $author, $name, $public)
                = mysql_fetch_row($result)) {
            if ($first) {
                $first = 0;
            } else {
                echo '<br \>';
            }

            echo '<a href="story.php?story=', $story, '">',
                $title, '</a> by <a href="author.php?author=', $author,
                '">', $name, "</a>\n";
        }

        echo "</td></tr>\n";
    }

    mysql_free_result($result);
    $result = mysql_query("
        SELECT          fa_series.series,
                        fa_series.name,
                        fa_author.author,
                        fa_author.name
        FROM            fa_series
          INNER JOIN    fa_author
            ON          fa_author.author = fa_series.author
          INNER JOIN    fa_bookmark_series
            ON          fa_bookmark_series.series = fa_series.series
              AND       fa_bookmark_series.user = $user
              AND       fa_bookmark_series.public = 1
        ORDER BY        fa_series.name
    ");

    if (mysql_num_rows($result) > 0) {
        echo '<tr><th>Series Bookmarks</th><td>', "\n";
        $first = 1;

        while (list($series, $title, $author, $name)
                = mysql_fetch_row($result)) {
            if ($first) {
                $first = 0;
            } else {
                echo '<br \>';
            }

            echo 'The <a href="series.php?series=', $series, '">',
                $title, '</a> series by <a href="author.php?author=',
                $author, '">', $name, "</a>\n";
        }

        echo "</td></tr>\n";
    }

    mysql_free_result($result);

    echo "</table>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
