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
### FILE: admin/alerts.php
###
### This page displays the list of pending email alerts, and also handles the
### the sending of those alerts.
###
### Currently only 'All Story' alerts are sent.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('categories.php');
    require_once('numbers.php');

    $page_title = 'Admin - Alerts';
    include('header.php');

    $result = mysql_query('
        SELECT  COUNT(*)
        FROM    fa_alert
    ');

    list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    if ($count == 0) {
?>
<h2>No Pending Alerts</h2>
<p>There are no alerts waiting to be sent.</p>
<?=display_backlinks()?>
<?php
    } elseif ($_POST['send']) {
        $result = mysql_query("
            SELECT          fa_file.file            AS file_id,
                            fa_file.number          AS file_number,
                            fa_file.name            AS file_name,
                            fa_story.story          AS story_id,
                            fa_story.name           AS story_name,
                            fa_story.category_ids   AS category_ids,
                            fa_story.pairing_names  AS pairing_names,
                            fa_story.summary        AS summary,
                            fa_story.chapter        AS chapter,
                            fa_author.name          AS author_name,
                            fa_author.email         AS author_email
            FROM            fa_alert
              INNER JOIN    fa_file
                ON          fa_file.file = fa_alert.file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
            ORDER BY        fa_story.name,
                            fa_author.name,
                            fa_file.number
        ");

        $email_text = $fa_site_name . " has been updated!\nThe following "
            . ((mysql_num_rows($result) == 1) ? 'story has' : 'stories have')
            . " been uploaded:\n";
        $last = 0;
        $file_url = 'http' . ($fa_server_secure ? 's' : '') . '://' .
            $fa_server_name . $fa_server_path . '/file.php?file=';

        while ($story = mysql_fetch_array($result)) {
            if ($story['story_id'] != $last) {
                $last = $story['story_id'];
                $email_text .= "\n\nTitle: " . $story['story_name'] .
                    "\nAuthor: " . $story['author_name'] .
                    "\nAuthor Email: " . $story['author_email'] .
                    "\nCategory: " .
                        category_ids_to_names($story['category_ids']) .
                    "\nPairings: " . $story['pairing_names'] .
                    "\n\nSummary: " . $story['summary'] . "\n";
            }

            if ($story['chapter']) {
                $email_text .= "\nRead " . chapter_name($story['file_name'],
                    $story['chapter'], $story['file_number']) . " here:\n";
            } else {
                $email_text .= "\nRead the story here:\n";
            }

            $email_text .= $file_url . $story['file_id'] . "\n";
        }

        mysql_free_result($result);

        mysql_query('
            DELETE FROM fa_alert
        ');

        $result = mysql_query("
            SELECT          fa_user.email
            FROM            fa_subscription
              INNER JOIN    fa_user
                ON          fa_subscription.user = fa_user.user
                  AND       fa_subscription.type = 1
            WHERE           fa_user.type = 'list'
        ");

        while (list($email) = mysql_fetch_row($result)) {
            mail($email, "$fa_site_name Story Update", $email_text,
                "From: $fa_email_name <$fa_email_address>");
        }

        mysql_free_result($result);
?>
<h2>Email Alerts Sent</h2>
<p>All pending email alerts have been sent.</p>
<?=display_backlinks()?>
<?php
    } else {
        $result = mysql_query("
            SELECT          fa_file.file,
                            fa_story.name,
                            fa_story.chapter,
                            fa_file.number,
                            fa_author.name,
                            fa_user.name,
                            fa_alert.time
            FROM            fa_alert
              INNER JOIN    fa_file
                ON          fa_file.file = fa_alert.file
              INNER JOIN    fa_story
                ON          fa_story.story = fa_file.story
              INNER JOIN    fa_author
                ON          fa_author.author = fa_story.author
              INNER JOIN    fa_user
                ON          fa_user.user = fa_alert.user
            ORDER BY        fa_alert.time DESC
        ");
?>
<h2>Pending Alerts</h2>
<?=display_backlinks()?>
<p>The following alerts are waiting to be sent:</p>
<table class="data">
<tr><th>Story/Author</th><th>Uploader</th><th>Date/Time</th></tr>
<?php
        while (list($file, $title, $chapter, $number, $name, $uploader, $time)
                = mysql_fetch_row($result)) {
            echo '<tr><td><a href="file.php?file=', $file, '">', $title;
            if ($chapter) echo ': ', chapter_name('', $chapter, $number);
            echo "</a> by $name</td><td>$uploader</td><td>$time</td></tr>\n";
        }
?>
</table>
<form action="alerts.php" method="POST">
<p><input type="submit" name="send" value="Send Pending Email Alerts"></p>
</form>
<?php
        mysql_free_result($result);
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
