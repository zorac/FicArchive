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
### FILE: recent.php
###
### This page displays recently uploaded stories and chapters and generates
### RSS and ATOM feeds.
###############################################################################
###############################################################################
    ini_set('include_path', 'lib');
    require_once('categories.php');
    require_once('date.php');
    require_once('display.php');
    require_once('init.php');
    require_once('numbers.php');

    function xml_cleanup($text) {
        return preg_replace('/&/', '&amp;',
            preg_replace('/<.*?>/', ' ', $text));
    }

    $result = mysql_query("
        SELECT  MAX(UNIX_TIMESTAMP(updated))
        FROM    fa_file
    ");

    list($updated) = mysql_fetch_row($result);
    mysql_free_result($result);

    $count = (int)$_GET['count'];
    if ($count < 10) $count = 25;
    if ($count > 50) $count = 50;
    $page = (int)$_REQUEST['page'];
    if ($page < 1) $page = 1;
    $skip = ($page - 1) * $count;

    $result = mysql_query("
        SELECT          SQL_CALC_FOUND_ROWS
                        fa_file.file                    AS id,
                        fa_file.number                  AS number,
                        fa_file.size                    AS size,
                        fa_file.name                    AS name,
                        UNIX_TIMESTAMP(fa_file.updated) AS updated,
                        fa_story.story                  AS story_id,
                        fa_story.name                   AS story_name,
                        fa_author.author                AS author_id,
                        fa_author.name                  AS author_name,
                        fa_rating.name                  AS rating_name,
                        fa_story.category_ids           AS category_ids,
                        fa_story.pairing_names          AS pairing_names,
                        fa_story.summary                AS summary,
                        fa_file.notes                   AS notes,
                        fa_story.chapter                AS chapter
        FROM            fa_file
          INNER JOIN    fa_story
            ON          fa_file.story = fa_story.story
          INNER JOIN    fa_author
            ON          fa_story.author = fa_author.author
          INNER JOIN    fa_rating
            ON          fa_file.rating = fa_rating.rating
        WHERE           fa_story.hidden = 0
        ORDER BY        fa_file.updated DESC,
                        fa_file.number DESC
        LIMIT           $skip, $count
    ");

    if (($_GET['format'] == 'rss') && preg_match('/^(0.9[124]|2.0)$/',
            $_GET['version'])) {
        $rss2 = preg_match('/^(0.94|2.0)$/', $_GET['version']);
        header('Content-type: application/rss+xml');
        $updated = rfc822_date($updated);
        echo '<?xml version="1.0"?>', "\n";
?>
<rss version="<?=$_GET['version']?>">
 <channel>
  <title><?=$fa_site_name?></title>
  <link><?=$fa_server_root?>/</link>
  <description>Recently Added Stories and Chapters.</description>
  <language>en</language>
  <managingEditor><?=$fa_email_address?></managingEditor>
  <lastBuildDate><?=$updated?></lastBuildDate>
<?php
        if ($rss2) echo "  <pubDate>$updated</pubDate>\n";

        while ($file = mysql_fetch_assoc($result)) {
            $title = xml_cleanup($file['story_name'] . ' by '
                . $file['author_name']);
            if ($file['chapter']) $title .= ' - ' . xml_cleanup(chapter_name(
                $file['name'], $file['chapter'], $file['number']));
            $file['summary'] = xml_cleanup($file['summary']);
?>
  <item>
   <title><?=$title?></title>
   <link><?=$fa_server_root?>/file.php?file=<?=$file[id]?></link>
   <description><?=$file['summary']?></description>
<?php
            if ($rss2) {
                $tag = 'tag:' . $fa_server_name . ','
                    . tag_date($file['updated']) . ':' . 'file'
                    . $fa_server_path . '.' . $file['id'];
?>
   <comments><?=$fa_server_root?>/boards/review.php?file=<?=$file[id]?></comments>
   <guid><?=$tag?></guid>
   <pubDate><?=rfc822_date($file['updated'])?></pubDate>
<?php
            }

            echo "  </item>\n";
        }

        echo " </channel>\n</rss>\n";
    } elseif (($_GET['format'] == 'atom') && ($_GET['version'] == '0.3')) {
        header('Content-type: application/atom+xml');
        echo '<?xml version="1.0"?>', "\n";
?>
<feed version="0.3" xmlns="http://purl.org/atom/ns#">
 <title><?=$fa_site_name?></title>
 <link rel="alternate" type="text/html" href="<?=$fa_server_root?>/"/>
 <author>
  <name><?=$fa_email_name?></name>
  <email><?=$fa_email_address?></email>
 </author>
 <tagline>Recently Added Stories and Chapters.</tagline>
 <modified><?=w3c_date($updated)?></modified>
<?php
        while ($file = mysql_fetch_assoc($result)) {
            $title = xml_cleanup($file['story_name'] . ' by '
                . $file['author_name']);

            if ($file['chapter']) {
                $chapter_name = xml_cleanup(chapter_name($file['name'],
                    $file['chapter'], $file['number']));
                $title .= ' - ' . $chapter_name;
            }

            $updated = w3c_date($file['updated']);
            $tag = 'tag:' . $fa_server_name . ',' . tag_date($file['updated'])
                . ':' . 'file' . $fa_server_path . '.' . $file['id'];
            $file['summary'] = xml_cleanup($file['summary']);
            $file['notes'] = xml_cleanup($file['notes']);
?>
 <entry>
  <title><?=$title?></title>
  <link rel="alternate" type="text/html" href="<?=$fa_server_root?>/file.php?file=<?=$file[id]?>"/>
  <author>
   <name><?=$file['author_name']?></name>
   <url><?=$fa_server_root?>/author.php?author=<?=$file['author_id']?></url>
  </author>
  <id><?=$tag?></id>
  <modified><?=$updated?></modified>
  <issued><?=$updated?></issued>
  <summary><?=$file['summary']?></summary>
  <content type="text/html" mode="escaped">
   <![CDATA[
    <table>
     <tr valign="top">
      <th align="right">Title:</th>
<?php
            if ($chapter_name) {
?>
      <td><a href="<?=$fa_server_root?>/story.php?story=<?=$file['story_id']?>"><?=$file['story_name']?></a><br/>
          <a href="<?=$fa_server_root?>/file.php?file=<?=$file['id']?>"><?=$chapter_name?></a></td>
     </tr>
<?php
            } else {
?>
      <td><a href="<?=$fa_server_root?>/file.php?file=<?=$file['id']?>"><?=$file['story_name']?></a></td>
<?php
            }
?>
     </tr>
     <tr valign="top">
      <th align="right">Author:</th>
      <td><a href="<?=$fa_server_root?>/author.php?author=<?=$file['author_id']?>"><?=$file['author_name']?></a></td>
     </tr>
     <tr valign="top">
      <th align="right">Rating:</th>
      <td><?=$file['rating_name']?></td>
     </tr>
     <tr valign="top">
      <th align="right">Updated:</th>
      <td><?=nice_date($file['updated'])?></td>
     </tr>
     <tr valign="top">
      <th align="right">Length:</th>
      <td><?=nice_count($file['size'])?> words</td>
     </tr>
     <tr valign="top">
      <th align="right">Pairings:</th>
      <td><?=$file['pairing_names']?></td>
     </tr>
     <tr valign="top">
      <th align="right">Categories:</th>
      <td><?=category_ids_to_names($file['category_ids'])?></td>
     </tr>
     <tr valign="top">
      <th align="right">Summary:</th>
      <td><?=$file['summary']?></td>
     </tr>
<?php
            if ($file['notes']) {
?>
     <tr valign="top">
      <th align="right">Notes:</th>
      <td><?=$file['notes']?></td>
     </tr>
<?php
            }
?>
    </table>
   ]]>
  </content>
 </entry>
<?php
        }

        echo "</feed>\n";
    } else {
        $results = mysql_found_rows();
        $pages = ceil($results / $count);

        $page_title = "Recent Updates";
        $add_to_head ='<link rel="alternate" type="application/atom+xml" '
            . 'title="Atom" href="' . $fa_server_root . '/recent.php?format='
            . 'atom&amp;version=0.3"/>' . "\n" .  '<link rel="alternate" '
            . 'type="application/rss+xml" title="RSS 2.0" href="'
            . $fa_server_root . '/recent.php?format=rss&amp;version=2.0"/>'
            . "\n";
        include('header.php');
        echo "<h2>Recently Added Stories and Chapters</h2>\n";

        if ($results == 0) {
            echo '<p class="warning">You have no bookmarked stories.</p>', "\n";
        } elseif (mysql_num_rows($result) == 0) {
            echo '<p class="warning">Ther are no more bookmarked stories. ',
                '<a href="recent.php?count=', $count, '&amp;page=', $pages,
                '">Go back</a> to the last page of stories.</p>', "\n";
        } else {
            $links = ($pages > 1) ? ('<p class="commentlinks">' . page_links(
                $page, $pages, 'recent.php?') . "</p>\n") : '';
            echo $links;

            while ($file = mysql_fetch_assoc($result)) {
                $file['updated'] = mysql_date($file['updated']);
                echo file_display($file);
            }

            echo $links;
        }

        include('footer.php');
    }

    mysql_free_result($result);
###############################################################################
###############################################################################
?>
