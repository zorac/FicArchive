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
### FILE: admin/backup.php
###
### This page displays the list of available backup files and allows new
### backups to be taken.
###
### Currently non-functional on large databases.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('admin.php');
    require_once('date.php');
    require_once('incremental.php');
    require_once('init.php');
    require_once('numbers.php');

    $backup_skip_data = array(
        'fa_alert'          => 1,
        'fa_last_comment'   => 1,
        'fa_last_thread'    => 1,
        'fa_session'        => 1
    );

    function mysql_escape_and_quote($val) {
        if (is_null($val)) {
            return 'NULL';
        } elseif (is_numeric($val)) {
            return $val;
        } else {
            return("'" . mysql_escape_string($val) . "'");
        }
    }

    $page_title = 'Admin - Backups';

###############################################################################
# Initiate a MySQL backup.
###############################################################################
    if ($_POST['mysql']) {
        start_incremental();
        $incremental['filename'] = '../backup/' . todays_date() . '.sql.gz';
        $file = gzopen($incremental['filename'], 'wb');
        gzwrite($file, '# MySQL backup generated on ' . todays_date() . "\n");
        $tables = mysql_query("SHOW TABLES LIKE 'fa\\_%'");
        $incremental['type'] = 'MySQL';
        $incremental['table'] = array();
        $incremental['next'] = 0;

        while (list($table) = mysql_fetch_row($tables)) {
            $result = mysql_query("SHOW CREATE TABLE $table");
            list($table, $create) = mysql_fetch_row($result);
            mysql_free_result($result);
            gzwrite($file, "\n" . $create . ";\n\n");

            if (!$backup_skip_data[$table]) {
                preg_match('/PRIMARY KEY\s+\((.+?)\)/', $create, $matches);
                $result = mysql_query("SELECT COUNT(*) FROM $table");
                list($rows) = mysql_fetch_row($result);
                mysql_free_result($result);

                $incremental['table'][] = array(
                    'name'  => $table,
                    'key'   => $matches[1],
                    'rows'  => $rows,
                    'done'  => 0
                );
            }
        }

        mysql_free_result($tables);
        gzclose($file);
        next_incremental("<h2>Database Backup - Please Wait</h2>\n" .
            "<p>Table structure dumped.</p>\n");
###############################################################################
# Continue a MySQL backup.
###############################################################################
    } elseif (is_incremental() && ($incremental['type'] == 'MySQL')) {
        $file = gzopen($incremental['filename'], 'ab');
        $status = "<p>Table structure dumped.<br/>\n";

        for ($i = 0; $i < $incremental['next']; $i++) {
            $status .= $incremental['table'][$i]['name'] . ': all ' .
                $incremental['table'][$i]['rows'] . " rows dumped.<br/>\n";
        }

        while (incremental_can_continue()) {
            if ($incremental['next'] >= count($incremental['table'])) {
                gzclose($file);
                stop_incremental();

                include('header.php');
                $backlinks[] = array('Backup Admin', 'backup.php');
                echo "<h2>Database Backup Complete</h2>\n",
                    display_backlinks(), $status . "Backup complete!</p>\n";
                include('footer.php');

                exit(0);
            } else {
                $table = $incremental['table'][$i]['name'];
                $result = mysql_query("SELECT * FROM $table ORDER BY " .
                    $incremental['table'][$i]['key'] . ' LIMIT ' .
                    $incremental['table'][$i]['done'] . ',500');

                while ($row = mysql_fetch_row($result)) {
                    gzwrite($file, "INSERT INTO $table VALUES(" . implode(', ',
                        array_map('mysql_escape_and_quote', $row)) . ");\n");
                    $incremental['table'][$i]['done']++;
                }

                mysql_free_result($result);

                if ($incremental['table'][$i]['done'] >=
                        $incremental['table'][$i]['rows']) {
                    $status .= $incremental['table'][$i]['name'] . ': all ' .
                        $incremental['table'][$i]['rows'] .
                        " rows dumped.<br/>\n";
                    $incremental['next']++;
                    $i++;
                }
            }
        }

        gzclose($file);
        next_incremental("<h2>Database Backup - Please Wait</h2>\n" . $status .
            $incremental['table'][$i]['name'] . ': ' .
            $incremental['table'][$i]['done'] . ' of ' .
            $incremental['table'][$i]['rows'] . " rows dumped.</p>\n");
###############################################################################
# Display the list of backups.
###############################################################################
    } else {
        include('header.php');
?>
<h2>Backup Management</h2>
<p>This page allows you to create and manage backup files. You will need to
    maintain off-site copies of these files (and other data on the site) for
    them to be really useful.</p>
<?=display_backlinks()?>
<h3>Generate Backup Files</h3>
<form method="post" action="backup.php">
<p>The MySQL database contains all of the data on the site with the exception
    of the actual text of the stories. This includes all the data entered into
    the content management system, user profiles, message board posts, etc.<br>
<input type="submit" name="mysql" value="Backup the MySQL Database Now!">
</form>
<h3>Existing backup files</h3>
<table class="data">
<tr><th>Filename</th><th>Type</th><th>Size</th><th>Date/Time</th></tr>
<?php
        $dir = opendir('../backup');
        $files = array();

        while ($file = readdir($dir)) {
            if (preg_match('/^\d\d\d\d-\d\d-\d\d.sql.gz$/', $file)) {
                $files[$file] = 'MySQL database';
            }
        }

        closedir($dir);
        krsort($files, SORT_STRING);
        reset($files);

        foreach ($files as $file => $type) {
            $stat = stat("../backup/$file");
            echo "<tr><td>$file</td><td>$type</td><td>", nice_count($stat[7] /
                1024), ' Kb</td><td>', strftime('%e %b %Y, %I:%M %p',
                $stat[9]), "</td></tr>\n";
        }

        echo "</table>\n";
    }

    include('footer.php');
###############################################################################
###############################################################################
?>
