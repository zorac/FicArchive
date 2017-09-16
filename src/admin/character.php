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
### FILE: admin/character.php
###
### This page displays the details of a character and allows those details to
### be updated.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('characters.php');
    require_once('init.php');
    require_once('numbers.php');

    $backlinks[] = array('Character Admin', 'characters.php');
    $info = array();

    $result = mysql_query('
        SELECT          fa_person.person,
                        fa_person.nickname,
                        fa_person.gender,
                        fa_nickname.name
        FROM            fa_person
          INNER JOIN    fa_nickname
            ON          fa_nickname.nickname = fa_person.nickname
        WHERE           fa_person.person = ' . (int)$_REQUEST['person']
    );

    if (list($person, $main_nickname, $gender, $main_name)
            = mysql_fetch_row($result)) {
        mysql_free_result($result);
        $result = mysql_query("
            SELECT          COUNT(fa_pairing_nickname.pairing)
            FROM            fa_pairing_nickname
              INNER JOIN    fa_nickname
                ON          fa_nickname.nickname =
                                fa_pairing_nickname.nickname
                  AND       fa_nickname.person = $person
        ");

        list($pairings) = mysql_fetch_row($result);
        mysql_free_result($result);
    } else {
        admin_error('Character Not Found',
            'No character with the supplied ID could be found.');
    }

    if ($_POST['add']) {
        $info[] = add_names($person, $_POST['add_names']);
    } elseif ($_POST['update']) {
        if ($main_nickname != $_POST['main_name']) {
            $result = mysql_query('
                SELECT  nickname,
                        name
                FROM    fa_nickname
                WHERE   nickname = '. (int)$_POST['main_name'] . '
                AND     person = ' . $person
            );

            if (mysql_num_rows($result) == 1) {
                list($main_nickname, $main_name) = mysql_fetch_row($result);
                mysql_free_result($result);

                mysql_query("
                    UPDATE  fa_person
                    SET     nickname = $main_nickname
                    WHERE   person = $person
                ");

                $info[] = 'Main name changed.';
            } else {
                $info[] = 'Could not set main name.';
            }
        }

        if ($gender != $_POST['gender']) { #TODO
            mysql_query("
                UPDATE  fa_person
                SET     gender = '" . mysql_escape_string($_POST['gender']) . "'
                WHERE   person = $person
            ");

            if (mysql_affected_rows()) {
                $gender = $_POST['gender'];
                $info[] = 'Gender changed.';
            } else {
                $info[] = 'Could not set gender.';
            }
        }

        $result = mysql_query("
            SELECT      nickname,
                        name
            FROM        fa_nickname
            WHERE       person = $person
            ORDER BY    name
        ");

        while (list($nickname, $name) = mysql_fetch_row($result)) {
            if (!$_POST["nickname$nickname"]) {
                $tmp = mysql_query("
                    SELECT  pairing
                    FROM    fa_pairing_nickname
                    WHERE   nickname = $nickname
                ");

                if (mysql_num_rows($tmp)) {
                    $info[] = "Nickname '$name' is in use in a pairing.";
                } elseif ($nickname == $main_nickname) {
                    $info[] = "You can't delete the main name.";
                } else {
                    mysql_query("
                        DELETE FROM fa_nickname
                        WHERE       nickname = $nickname
                    ");

                    $info[] = "Nickname '$name' removed.";
                }

                mysql_free_result($tmp);
            } elseif ($_POST["name$nickname"] != $name) {
                mysql_query("
                    UPDATE  fa_nickname
                    SET     name = '" . mysql_escape_string(
                                            $_POST["name$nickname"]) . "'
                    WHERE   nickname = $nickname
                ");

                if (mysql_affected_rows()) {
                    $info[] = "Nickname changed from '$name' to '" .
                        $_POST["name$nickname"] . "'.";
                } else {
                    $info[] = "The nickname '" . $_POST["name$nickname"]
                        . "' is already in use.";
                }
            }
        }
    } elseif ($_POST['remove']) {
        if ($pairings == 0) {
            mysql_query("
                DELETE FROM fa_nickname
                WHERE       person = $person
            ");

            mysql_query("
                DELETE FROM fa_person
                WHERE       person = $person
            ");

            $page_title = 'Admin - Character Removed';
            include('header.php');
            echo "<h2>Character Removed</h2>\nThe character '$main_name' has ",
                "been removed.\n", display_backlinks();
            include('footer.php');
            exit(0);
        } else {
            $info[] = 'This character cannot be removed as there are still ' .
                $pairings . ' pairings referencing it. Sorry.';
        }
    }

    $page_title = "Admin - Character - $main_name";
    include('header.php');
?>
<h2>Character profile: <?=$main_name?></h2>
<?=display_backlinks()?>
<?=$info ? ('<p class="warning">' . implode('<br>', $info) . '</p>') : ''?>
<h3>Update Character Details</h3>
<p>The first column (radio buttons) selects which name is the character's main
    name. The second column (checkboxes) can be unticked to delete names
    (provided that they are not in use). Please do <em>not</em> edit names
    other than to correct typographical errors.</p>
<form action="character.php?person=<?=$person?>" method="post">
<table class="info">
<?php
    $result = mysql_query("
        SELECT      nickname,
                    name
        FROM        fa_nickname
        WHERE       person = $person
        ORDER BY    name
    ");

    $start = '<tr><th>Names</th>';

    while (list($nickname, $name) = mysql_fetch_row($result)) {
        echo $start, '<td><input type="radio" name="main_name" value="',
            $nickname, (($nickname == $main_nickname) ? '" checked>' : '">'),
            '</td><td><input type="checkbox" name="nickname', $nickname,
            '" checked></td><td><input name="name', $nickname,
            '" size="40" value="', $name, '">', "</td></tr>\n";
        $start = '<tr><td></td>';
    }

    mysql_free_result($result);
?>
</p>
<tr><th colspan="3">Gender</th><td><?=gender_select($gender)?></td></tr>
<tr><th colspan="3">Pairings</th><td><?=nice_count($pairings)?></td></tr>
<tr><td colspan="3"></td><td><input type="submit" name="update" value="Update this Character Profile"></td></tr>
</table>
</form>
<h3>Add new names</h3>
<p>To add new names to this character, enter them comma-separated in the box below.</p>
<form action="character.php?person=<?=$person?>" method="post">
<input name="add_names" size="40">
<input type="submit" name="add" value="Add Names to this Character">
</form>
<h3>Remove this Character</h3>
<p>You can only remove a character if it is not referenced in any pairings.</p>
<?php
    if ($pairings == 0) {
?>
<form action="character.php?person=<?=$person?>" method="post">
<input type="submit" name="remove" value="Remove this Character">
</form>
<?php
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
