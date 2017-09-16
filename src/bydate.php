<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2004,2005,2009 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE: bydate.php
###
### This page displays stories ordered by last update time.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('date.php');
    require_once('display.php');
    require_once('init.php');

    $result = mysql_query("SELECT UNIX_TIMESTAMP(MAX(updated)) FROM fa_story");
    list($date) = mysql_fetch_row($result);
    mysql_free_result($result);
    $date = localtime($date);

    $month = (int)$_REQUEST['month'];
    $year = array_key_exists('year', $_REQUEST) ? (int)$_REQUEST['year']
        : ($date[5] + 1900);

    if (($month < 1) || ($month > 12)) $month = $date[4] + 1;
    if ($year <= $date[5]) $year += 2000;
    if ($year <= 100) $year += 1900;

    $page_title = 'Stories by Date - ' . $fa_month_names[$month] . " $year";
    include('header.php');

    $result = mysql_query("
        SELECT DISTINCT YEAR(updated)   AS year,
                        MONTH(updated)  AS month
        FROM            fa_story
        WHERE           updated IS NOT NULL
        ORDER BY        year,
                        month
    ");

    $dates = array();

    while (list($y, $m) = mysql_fetch_row($result)) {
        if (!$dates[$y]) $dates[$y] = array();
        $dates[$y][$m] = $m;
    }

    mysql_free_result($result);
    $years = array();

    foreach (array_keys($dates) as $y) {
        if ($y == $year) {
            $years[] = "<strong>$year</strong>";
        } else {
            $years[] = '<a href="bydate.php?year=' . $y. '&amp;month=' . $month
                . '">' .  $y . '</a>';
        }
    }

    $years = implode(' | ', $years);
    $months = array();

    if ($dates[$year]) {
        foreach ($dates[$year] as $m) {
            if ($m == $month) {
                $months[] = "<strong>$fa_month_names_short[$m]</strong>";
            } else {
                $months[] = '<a href="bydate.php?year=' . $year
                    .  '&amp;month=' . $m .  '">' .  $fa_month_names_short[$m]
                    . '</a>';
            }
        }

        $months = join(' | ', $months);
    } else {
        $months = 'No stories updated in ' . $year;
    }

    echo "<h2>Stories by Date</h2>\n", '<p class="letters">', $years, '<br>',
        $months, "</p>\n<h3>$fa_month_names[$month] $year</h3>\n";

    $result = mysql_query("
        SELECT          fa_story.story          AS story_id,
                        fa_story.name           AS story_name,
                        fa_author.author        AS author_id,
                        fa_author.name          AS author_name,
                        fa_rating.name          AS rating_name,
                        fa_story.updated        AS updated,
                        fa_story.size           AS size,
                        fa_story.category_ids   AS category_ids,
                        fa_story.pairing_names  AS pairing_names,
                        fa_story.summary        AS summary,
                        fa_story.chapter        AS chapter,
                        fa_story.file_ids       AS file_ids,
                        fa_story.reviews        AS reviews
        FROM            fa_story
          INNER JOIN    fa_author
            ON          fa_author.author = fa_story.author
          INNER JOIN    fa_rating
            ON          fa_rating.rating = fa_story.rating
        WHERE           MONTH(fa_story.updated) = $month
          AND           YEAR(fa_story.updated) = $year
          AND           fa_story.hidden = 0
        ORDER BY        fa_story.updated DESC
    ");

    if (mysql_num_rows($result) == 0) {
        echo "<p>No stories found.</p>\n";
    }

    while ($story = mysql_fetch_assoc($result)) {
        echo story_display($story);
    }

    mysql_free_result($result);
    echo '<p class="letters">', $months, '<br>', $years, "</p>\n";
    include('footer.php');
###############################################################################
###############################################################################
?>
