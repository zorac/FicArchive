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
### FILE: admin/characters.php
###
### This page displays the list of characters and allows new characters to be
### added.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');
    require_once('admin.php');
    require_once('characters.php');

    $page_title = 'Admin - Characters';
    include('header.php');

    if ($_POST['add']) {
        $name = trim($_POST['full_name']);

        if (!$name) {
            $error = 'You must supply a full name.';
        } elseif (!$_POST['gender']) { #TODO
            $error = 'You must specify the gender.';
        } else {
            $result = mysql_query("
                INSERT INTO fa_nickname
                            (name)
                VALUES      ('" . mysql_escape_string($name) . "')
            ");

            if ($result) {
                $nickname = mysql_insert_id();

                mysql_query('
                    INSERT INTO fa_person
                                (nickname, gender)
                    VALUES      (' . $nickname . ", '" .
                                mysql_escape_string($_POST['gender']{0}) . "')"
                );

                $person = mysql_insert_id();

                mysql_query('
                    UPDATE  fa_nickname
                    SET     person = ' . $person . '
                    WHERE   nickname = ' . $nickname
                );
?>
<h2>Character added</h2>
<p><?=$name?> has been added to the database.
    <a href="character.php?person=<?=$person?>">Click here</a> to view the
    character page.</p>
<?php
                $backlinks[] = array('Character Admin', $_SERVER['PHP_SELF']);
                echo display_backlinks();

                if ($_POST['additional_names']) {
                    echo add_names($_POST['additional_names']);
                } else {
                    $name = explode(' ', $name);
                    $add = array();
                    $first = mysql_escape_string($name[0]);

                    $result = mysql_query("
                        SELECT  nickname
                        FROM    fa_nickname
                        WHERE   (name = '" . $first . "'
                            OR  name LIKE '" . $first . " %')
                          AND   nickname <> " . $nickname
                    );

                    if (mysql_num_rows($result) == 0) $add[] = $name[0];
                    mysql_free_result($result);

                    if (count($name) > 1) {
                        $last = mysql_escape_string($name[count($name) - 1]);

                        $result = mysql_query("
                            SELECT  nickname
                            FROM    fa_nickname
                            WHERE   (name = '" . $last . "'
                                OR  name LIKE '% " . $last . "')
                              AND   nickname <> " . $nickname
                        );

                        if (mysql_num_rows($result) == 0)
                            $add[] = array_pop($name);
                        mysql_free_result($result);
                    }

                    if ($add) {
?>
<p>The following additional names seem appropriate for this character. Hit the
    button to add them.</p>
<form action="character.php?person=<?=$person?>" method="post">
<input name="add_names" size="40" value="<?=implode(', ', $add)?>">
<input type="submit" name="add" value="Add these Names to the Character">
</form>
<?php
                    }
                }

                include('footer.php');
                exit(0);
            } else {
                $result = mysql_query("
                    SELECT  person
                    FROM    fa_nickname
                    WHERE   name = '" . mysql_escape_string($_POST['full_name']) . "'
                ");

                list($person) = mysql_fetch_row($result);
                mysql_free_result($result);

                $error = 'A character with the name '
                    . '<a href="character.php?person=' . $person . '">'
                    . $_POST['full_name'] . '</a> already exists.';
            }
        }
    }
?>
<h2>Character Administration</h2>
<p>The list of characters is needed to keep track of pairings and allow
    searches on them.</p>
<?=display_backlinks()?>
<?php
    $result = mysql_query("
        SELECT          fa_person.person,
                        fa_nickname.name
        FROM            fa_person
          INNER JOIN    fa_nickname
            ON          fa_nickname.nickname = fa_person.nickname
        ORDER BY        fa_nickname.name
    ");

    if (mysql_num_rows($result) > 0) {
?>
<h3>Modify an Existing Character</h3>
<p>To update the details of an existing character, select their name from the
    list below and hit Go.</p>
<form action="character.php" method="get">
<select name="person">
<option value="0">-- Select a Character --</option>
<?php
        while (list($person, $name) = mysql_fetch_row($result)) {
                echo '<option value="', $person, '">', $name, "</option>\n";
        }

        echo '</select> <input type="submit" value="Go">', "\n</form>\n";
    }

    mysql_free_result($result);
?>
<h3>Add a New Character</h3>
<?=($error ? "<p class=\"error\">$error</p>" : '')?>
<p>If you need to add a new character, enter their full name and gender below
    and hit Add. You can add additional nicknames separated by commas - if not,
    a best guess will be made.</p>
<form action="characters.php" method="post">
<table class="info">
<tr><th>Full name</th><td><input name="full_name" value="<?=$_POST['full_name']?>" size="40"></td></tr>
<tr><th>Additional names</th><td><input name="additional_names" value="<?=$_POST['additional_names']?>" size="40"></td></tr>
<tr><th>Gender</th><td><?=gender_select($_POST['gender'])?></td></tr>
<tr><td></td><td><input type="submit" name="add" value="Add New Character"></td></tr>
</table>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
