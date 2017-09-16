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
### FILE: profile/register.php
###
### This page allows new users to register on the site.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('config.php');
    require_once('date.php');
    require_once('init.php');
    require_once('login.php');

    $fa_no_login = TRUE;

    if ($_POST['register']) {
        $date = make_date($_POST['dob_day'], $_POST['dob_month'],
            $_POST['dob_year']);
        $age = get_age($date);

        if (!$_POST['username']) {
            $error = 'You must select a username.';
        } elseif (!$_POST['newpw1'] && !$_POST['newpw2']) {
            $error = 'You did not supply a password.';
        } elseif ($_POST['newpw1'] != $_POST['newpw2']) {
            $error = 'The two passwords do not match.';
        } elseif (!$_POST['dob_day'] || !$_POST['dob_month']) {
            $error = 'You have not entered your date of birth.';
        } elseif (!$date) {
            $error = "The date of birth you gave isn't a real date.";
        } elseif ($age < 0) {
            $error = "You don't appear to be born yet.";
        } elseif ($fa_minimum_age && ($age < $fa_minimum_age)) {
            $error = "Sorry, but you are too young to register.";
        } elseif ($age > 150) {
            $error = 'You appear to be implausibly old.';
        } elseif ($_POST['email'] && !preg_match($fa_regex_email,
                $_POST['email'])) {
            $error = 'The email address you gave does not seem to be valid.';
        } elseif (!$_POST['iagree']) {
            $error = 'You must agree to the "I confirm..." statement before '
                . 'you can register.';
        } else {
            $verify = ($_POST['confirmation'] && $_POST['email']) ?
                create_verification_code($_POST['username']) : '';
            mysql_query("
                INSERT INTO fa_user
                            (name, password, email, dob, verify)
                VALUES      ('" . mysql_escape_string($_POST['username'])
                       . "', '" . mysql_escape_string($_POST['newpw1'])
                       . "', '" . mysql_escape_string($_POST['email'])
                       . "', '" . $date . "', '" . $verify . "')
            ");

            if (mysql_affected_rows() == 1) {
                $_SESSION['user'] = mysql_insert_id();
                fa_get_user('user');
                $page_title = 'Registration Complete';
                include('header.php');
?>
<h2>Registration complete</h2>
<p>Thank you for registering - your account has now been activated. You can
    <a href="./">view your profile</a> and customize your account, or you could
    jump right in and <a href="../search.php">search</a> or
    <a href="../byauthor.php">browse</a> for stories.</p>
<?php
                include('footer.php');
                if ($verify) send_verification_code($_POST['email'],
                    $_POST['username'], $_POST['newpw1'], $verify);
                exit(0);
            } else {
                $error = 'That username is already in use.';
            }
        }
    }

    $dob = '<select name="dob_day">' . "\n<option></option>\n";

    for ($i = 1; $i < 32; $i++) {
        $dob .= (($i == $_POST['dob_day']) ? '<option selected>' : '<option>')
            . $i . "</option>\n";
    }

    $dob .= '</select> / <select name="dob_month">' . "<option></option>";

    for ($i = 1; $i < 13; $i++) {
        $dob .= '<option value="' . $i
            . (($i == $_POST['dob_month']) ? '" selected>' : '">')
            . $fa_month_names[$i] . "</option>\n";
    }

    $dob .= '</select> / <input name="dob_year" value="' . $_POST['dob_year'] .
        '" size="5" maxlength="4">';

    $page_title = 'User Registration';
    include('header.php');
?>
<h2>User Registration</h2>
<p>To register for full access to this site, please select a username and
    password. We ask that you enter your date of birth to allow you to view
    age-related stories. If you enter your email address, we can send you a
    confirmation email, and is needed if you ever require a password reminder.
    Some areas of the site will require you verify your email address - we'll
    send you a verification code in your confirmation email.
    <?=($fa_minimum_age ? ('You must be at least ' . $fa_minimum_age . ' years of age to register on this site.') : '')?></p>
<?=($error ? "<p><strong>$error</strong></p>\n" : '')?>
<form action="register.php" method="post">
<table class="info">
<tr><th>Username</th><td><input name="username" value="<?=$_POST['username']?>"></td></tr>
<tr><th>Password</th><td><input type="password" name="newpw1" value="<?=$_POST['newpw1']?>"></td></tr>
<tr><th>Repeat Password</th><td><input type="password" name="newpw2" value="<?=$_POST['newpw2']?>"></td></tr>
<tr><th>Date of Birth</th><td><?=$dob?></td></tr>
<tr><th>Email Address</th><td><input name="email" value="<?=$_POST['email']?>" size="30"></td></tr>
<tr><td class="checkbox"><input type="checkbox" name="confirmation" checked></td><td>Please send me a confirmation email.</td></tr>
<tr><td class="checkbox"><input type="checkbox" name="iagree"<?=($_POST['iagree'] ? ' checked' : '')?>></td><td>I confirm that I have read and accepted the <a href="../site/terms.php">Terms of Use</a> and that the date of birth I have supplied is correct.<?=($fa_minimum_age ? (' I also confirm that I am at least ' . $fa_minimum_age . ' years of age.') : '')?></td></tr>
<tr><td></td><td><input type="submit" name="register" value="Register with <?=$fa_site_name?>"></td></tr>
<?php
    if ($fa_privacy_url) echo '<tr><td></td><td><a href="', $fa_privacy_url,
        '">View our Privacy Policy</a></td></tr>', "\n";
    echo "</table>\n</form>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
