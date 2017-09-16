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
### FILE: profile/index.php
###
### This is the main index page for the user profile area.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('date.php');
    require_once('init.php');
    require_once('profile.php');

    $page_title = 'User Profile - ' . $_SESSION['username'];
    include('header.php');
    echo '<h2>User Profile: ', $_SESSION['username'], "</h2>\n<ul>\n";

    $result = mysql_query('
        SELECT      name
        FROM        fa_rating
        WHERE       age <= ' . get_age($_SESSION['dob']). '
        ORDER BY    rating DESC
    ');

    if (mysql_num_rows($result) > 0) {
        echo '<li>You can read stories with the following <a ',
            'href="../rating.php">ratings</a>: ';
        $count = mysql_num_rows($result);

        while (list($rating) = mysql_fetch_row($result)) {
            $count--;

            if ($count == 0) {
                echo $rating;
            } elseif ($count == 1) {
                echo "$rating and ";
            } else {
                echo "$rating, ";
            }
        }

        echo ".</li>\n";
    }

    mysql_free_result($result);

    if (fa_has_mod_access()) echo '<li>Your account has ',
        (fa_has_admin_access() ? 'administrator' : 'moderator'),
        ' privileges on this site. <a href="../admin/">Click here</a> to',
        " enter the administration pages.</li>\n";

    echo "<li>If you've posted to our review or discussion boards, then you ",
        'can view <a href="../boards/recent.php?user=', $_SESSION['user'],
        '">your latest comments</a> or <a href="../boards/recent.php?user=',
        $_SESSION['user'], '&amp;type=reply">recent replies to your comments',
        "</a>.</li>\n";

    if (!$fa_unset_autologin && ($_COOKIE[$fa_cookie_autologin]
            || $fa_set_autologin)) {
        echo '<li>You have currently enabled auto-login for this computer. <a ',
            'href="index.php?unset_autologin=1">Click here</a> to turn off ',
            "auto-login.</li>\n";
    } else {
        echo '<li>You can set your web browser to automatically log you in to ',
            'this web site (you will still be prompted for your password the ',
            'first time you view a story). <a href="index.php?set_autologin=1',
            '">Click here</a> to turn on auto-login.</li>', "\n";
    }

    echo "</ul>\n";

    if (!fa_is_verified()) {
?>
<h3>Email Address Verification</h3>
<form method="post" action="verify.php">
<input type="hidden" name="username" value="<?=$_SESSION['username']?>">
<p>If you have have an email with a verification code, you can enter it below
    to verify your account. We can <a href="verify.php?send=email">send you an
    email</a> with a new code if you don't already have one.<br>
<input name="code" size="40"> <input type="submit" value="Verify"></p>
</form>
<?php
    }
?>
<h3>Update Your Profile</h3>
<dl>
<dt><a href="account.php">Account Details</a>
<dd>View your account details and change your username or password.
<dt><a href="options.php">Site Customization Options</a>
<dd>Change the way the site looks or how it works to better suit you.
<dt><a href="public.php">Public Profile</a>
<dd>Update the information which appears on your
    <a href="../profile.php">public profile</a>.
<dt><a href="avatar.php">Avatar</a>
<dd>Select an avatar to represent you on the site.
<!--
<dt><a href="alerts.php">Email Alerts</a>
<dd>Set up email alerts for when new stories are uploaded.
-->
<dt><a href="bookmarks.php">Bookmarks</a>
<dd>View and manage your bookmarks.
</dl>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
