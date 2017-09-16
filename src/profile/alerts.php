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
### FILE: profile/alerts.php
###
### This page displays a list of configured email alerts.
###
### Does very little at the moment. Functionality pending...
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('alert.php');
    require_once('init.php');
    require_once('profile.php');

    if ($_POST['removeall']) {
        mysql_query('
            DELETE FROM fa_subscription
            WHERE       user = ' . $_SESSION['user']
        );
    } else {
        foreach ($_POST as $key => $value) {
            $key = explode('-', $key);

            if ($value && ($key[0] == 'subscription')) {
                mysql_query('
                    DELETE FROM fa_subscription
                    WHERE       user = ' . $_SESSION['user'] . '
                      AND       type = ' . (int)$key[1] . '
                      AND       data = ' . (int)$key[2]
                );
            }
        }
    }

    $subscriptions = display_subscriptions($_SESSION['user']);
    $page_title = 'Email Alerts - ' . $_SESSION['username'];
    include('header.php');
?>
<h2>Email Alerts for <?=$_SESSION['username']?></h2>
<p class="warning">This function is currently under development, so the chances
    of it actually sending you any emails is pretty darn slim.</p>
<p>You can set up email alerts to let you know when new stories have been
    uploaded to the archive matching conditions you give. Alerts for authors,
    stories and series can be added from the appropriate pages on this site.</p>
<?php
    if ($subscriptions) {
?>
<h3>Your Current Subscriptions</h3>
<p>All subscriptions on your account are listed below. If you no longer wish to
    recieve some of these alerts, you can select and remove them, or
    alternatively you can remove all of your subscriptions.</p>
<form action="alerts.php" method="POST">
<?php
        foreach (display_subscriptions($_SESSION['user']) as $subscription) {
           $id = $subscription[0] . '-' . $subscription[1];
            echo '<div class="subscription"><input type="checkbox" id="', $id,
                '" name="subscription-', $id, '"> <label for="', $id, '">',
                $subscription[2], "</label></div>\n";
        }
?>
<input type="submit" name="remove" value="Remove Selected">
<input type="submit" name="removeall" value="Remove All">
</form>
<?php
    } else {
?>
<h3>No Alerts Configured</h3>
<p>You are not currently subscribed to any alerts.</p>
<?php
    }

    include('footer.php')
###############################################################################
###############################################################################
?>
