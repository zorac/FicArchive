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
### FILE:       lib/disclaimer.php
###
### This file contains a disclaimer for age-restricted stories.
###############################################################################
###############################################################################
?>
<div class="inverse" id="disclaimer">
<h1 class="center">Warning</h1>
<p>The story you are about to read is AGE-RESTRICTED. We believe that this
    story would qualify for the following equivalent film rating:</p>
<table id="ratings"><tr><th><?=$file['rating_name']?></th><td><?=$file['rating_description']?></td></tr></table>
<p>This story is not available to anyone under the age of
    <?=$file['rating_age']?>, or to anyone who believes he, she or we would
    violate federal, state or local laws by accessing them or by allowing this
    access. This may include American states such as Utah or countries with
    anti-pornography laws.</p>
<p>If you are NOT SURE of the laws in your area, DO NOT access the following
    story until you are sure that you will not be violating your local laws.</p>
<blockquote>
I do not believe that I am legally able to access the following story, or I do
not wish to.
<div class="center">
<form method="get" action="http://www.google.com/">
<input type="submit" value="Leave">
</form>
</div>
I certify that I am over the age of <?=$file['rating_age']?> and that accessing
the following story will not violate the laws of my country or local ordinances.
<div class="center">
<form action="file.php?file=<?=$id?>" method="post">
<?php
    if ($fa_login_failed) {
        echo '<p class="error">Your login attempt failed. ',
            ($fa_login_fail_text ? $fa_login_fail_text : 'Please check ' .
            'your username and password and try again'), '</p>', "\n";
    } elseif (get_age($_SESSION['dob']) < $file['rating_age']) {
        echo '<p class="error">You are too young to read this story.</p>', "\n";
    }

    if ($require_login) {
?>
<p>
<table class="login">
<tr><th>Username</th><td><input name="username" value="<?=$_REQUEST['username']?>"></td></tr>
<tr><th>Password</th><td><input type="password" name="password"></td></tr>
</table>
<?php
    }
?>
<input type="hidden" name="accept" value="1">
<input type="submit" value="Enter">
</form>
</div>
<?php
    if ($require_login) {
?>
</p>
If you do not have a username and password, please
<a href="profile/register.php">click here</a>to register.
<?php
    }
?>
</blockquote>
</div>
