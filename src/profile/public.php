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
### FILE: profile/public.php
###
### This page allows a user to customise what information appears on their
### public profile page.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('date.php');
    require_once('init.php');
    require_once('profile.php');

    $profile = array();

    if ($_POST['update']) {
        foreach (array('name', 'dob', 'location', 'www', 'email', 'yahoo',
                'aol', 'icq', 'msn', 'lj', 'bio', 'all') as $item) {
            if ($_POST[$item]) $profile[$item] = $_POST[$item];
        }

        mysql_query("
            UPDATE  fa_user
            SET     profile = '" . mysql_escape_string(serialize($profile)) . "'
            WHERE   user = " . $_SESSION['user']
        );
    } else {
        $result = mysql_query('
            SELECT  profile
            FROM    fa_user
            WHERE   user = ' . $_SESSION['user']
        );

        if (mysql_num_rows($result) > 0) {
            list($tmp) = mysql_fetch_row($result);
            if ($tmp) $profile = unserialize($tmp);
        }

        mysql_free_result($result);
    }

    $page_title = 'User Profile - ' . $_SESSION['username'] . ' - Public';
    include('header.php');
    echo '<h2>User Profile: ', $_SESSION['username'], "</h2>\n",
        '<p><a href="./">Back to the main Profile page</a></p>', "\n",
        "<h3>Public Profile</h3>\n";
    if ($_POST['update']) echo '<p class="notice">Your public profile has ',
        "been updated.</p>\n";

    $emails = array('Not Displayed', $_SESSION['email'],
        fa_protect_email_address($_SESSION['email']));
    $dates = array('Not Displayed', long_date($_SESSION['dob'], FALSE),
        long_date($_SESSION['dob'], TRUE));
    $views = array('Registered Users Only', 'Anyone');
?>
<p>Here you can select what (if any) information appears on your
    <a href="../profile.php">public profile page</a>, and who can view it.
    There's a seperate page which allows you to
    <a href="avatar.php">select an avatar</a> to represent you on the site, and
    you can choose to make some or all of your
    <a href="bookmarks.php">bookmarks</a> public.</p>
<form method="post" action="public.php">
<table class="info">
<tr><th>Your Name</th><td><input name="name" size="40" value="<?=$profile['name']?>"></td></tr>
<tr><th>Birthday</th><td><select name="dob">
<?php
    for ($i = 0; $i < count($dates); $i++) {
        echo '<option value="', $i, (($i == $profile['dob']) ? '" selected>'
            : '">'), $dates[$i], "</option>\n";
    }
?>
</select></td></tr>
<tr><th>Location</th><td><input name="location" size="40" value="<?=$profile['location']?>"></td></tr>
<tr><th>Web Site</th><td><input name="www" size="40" value="<?=$profile['www']?>"></td></tr>
<tr><th>Email Address</th><td><select name="email">
<?php
    for ($i = 0; $i < count($emails); $i++) {
        echo '<option value="', $i, (($i == $profile['email']) ? '" selected>'
            : '">'), $emails[$i], "</option>\n";
    }
?>
</select></td></tr>
<tr><th>Yahoo! ID</th><td><input name="yahoo" size="40" value="<?=$profile['yahoo']?>"></td></tr>
<tr><th>AOL Screen Name</th><td><input name="aol" size="40" value="<?=$profile['aol']?>"></td></tr>
<tr><th>ICQ UID</th><td><input name="icq" size="40" value="<?=$profile['icq']?>"></td></tr>
<tr><th>MSN Username</th><td><input name="msn" size="40" value="<?=$profile['msn']?>"></td></tr>
<tr><th>LiveJournal User</th><td><input name="lj" size="40" value="<?=$profile['lj']?>"></td></tr>
<tr class="multiline"><th>Biography</th><td><textarea name="bio" rows="5" cols="40"><?=$profile['bio']?></textarea></td></tr>
<tr><th>Profile Visible To</th><td><select name="all">
<?php
    for ($i = 0; $i < count($views); $i++) {
        echo '<option value="', $i, (($i == $profile['all']) ? '" selected>'
            : '">'), $views[$i], "</option>\n";
    }
?>
</select></td></tr>
<tr><td></td><td><input type="submit" name="update" value="Update Your Public Profile"></td></tr>
<?php
    if ($fa_privacy_url) echo '<tr><td></td><td><a href="', $fa_privacy_url,
        '">View our Privacy Policy</a></td></tr>', "\n";
    echo "</table>\n</form>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
