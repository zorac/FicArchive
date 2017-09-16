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
### FILE:       lib/display.php
###
### This file provides functions for displaying file story and series
### information.
###############################################################################
###############################################################################
    require_once('categories.php');
    require_once('date.php');
    require_once('init.php');
    require_once('numbers.php');

###############################################################################
# FUNCTION:     display_span
#
# ARGS: class   A CSS class for the span
#       text    The text
#
# RETURNS:      An HTML span
#
# This function wraps the given text in a span of the given class.
###############################################################################
    function display_span($class, $text) {
        return('<span class="' . $class . '">' . $text . '</span>');
    }

###############################################################################
# FUNCTION:     meta_display
#
# ARGS: meta    An associative array of metadata
#
# RETURNS:      HTMLised metadata
#
# This function takes the metadata for a story or file and generates some nice
# HTML for it.
###############################################################################
    function meta_display($meta) {
        global $fa_root;

        if (array_key_exists('author_id', $meta)) {
            $output = ' by <a href="' . $fa_root . 'author.php?author='
                . $meta['author_id'] . '">' . $meta['author_name'] . '</a>';
        } else {
            $output = '';
        }

        $info = array();

        if (array_key_exists('reviews', $meta) && ($meta['reviews'] > 0)
                && $_SESSION['options']['show_reviews'])
            $info[] = display_span('reviews', '<a href="' . $fa_root
                . '/boards/review.php?story=' . $meta['story_id'] . '">'
                . nice_count($meta['reviews']) . ' review'
                . (($meta['reviews'] == 1) ? '' : 's') . '</a>');

        if (!$_SESSION['options']['hide_rating'])
            $info[] = display_span('rating', $meta['rating_name']);

        if (array_key_exists('updated', $meta)
                && $_SESSION['options']['show_date'])
            $info[] = display_span('date', short_date($meta['updated']));

        if (array_key_exists('size', $meta)
                && $_SESSION['options']['show_size'])
            $info[] = display_span('size', nice_count($meta['size'])
                . ' words');

        if (array_key_exists('category_ids', $meta) && $meta['category_ids']
                && $_SESSION['options']['show_categories'])
            $info[] = display_span('categories',
                category_ids_to_names($meta['category_ids']));

        if (array_key_exists('pairing_names', $meta) && $meta['pairing_names']
                && !$_SESSION['options']['hide_pairings'])
            $info[] = display_span('pairings', $meta['pairing_names']);

        if ($info) $output .= ' (' . implode(' - ', $info) . ')';

        if (array_key_exists('summary', $meta)
                && !$_SESSION['options']['hide_summary']) {
            if (!$info) $output .= ' -';
            $output .= ' ' . display_span('summary', $meta['summary']);
        }

        return($output);
    }

###############################################################################
# FUNCTION:     file_display
#
# ARGS: file    An associative array of file data and metadata
#
# RETURNS:      HTML file information
#
# This function generates attractive HTML for the file's data.
###############################################################################
    function file_display($file) {
        global $fa_root;

        $output = '<div class="story"><strong><a href="' . $fa_root;

        if ($file['chapter']) {
           $output .= 'story.php?story=' . $file['story_id'];
        } else {
           $output .= 'file.php?file=' . $file['id'];
        }

        $output .= '">' . $file['story_name'] . '</a></strong>';

        if ($file['chapter']) $output .= ' - <strong><a href="' . $fa_root
            . 'file.php?file=' . $file['id'] . '">'
            . chapter_name($file['name'], $file['chapter'], $file['number'])
            . '</a></strong>';

        $output .= meta_display($file);

        if ($file['notes']) $output .= ' <span class="notes"><strong>Notes:'
            . '</strong> ' . $file['notes'] . '</span>';

        return("$output</div>\n");
    }

###############################################################################
# FUNCTION:     story_display
#
# ARGS: story   An associative array of story data and metadata
#
# RETURNS:      HTML story information
#
# This function generates attractive HTML for the story's data.
###############################################################################
    function story_display($story) {
        global $fa_root;

        if (!$story['file_ids']) return '';
        $file_ids = explode(',', $story['file_ids']);

        if ($story['chapter'] || (count($file_ids) == 0)) {
            $output = 'story.php?story=' . $story['story_id'];
        } else {
            $file = explode(':', $file_ids[0]);
            $output = 'file.php?file=' . $file[1];
        }

        $output = '<div class="story"><strong><a href="' . $fa_root . $output
            . '">' . $story['story_name'] . '</a></strong>'
            . meta_display($story);

        if ($story['chapter'] && (count($file_ids) > 0)
                && !$_SESSION['options']['hide_chapters']) {
            $output .= "\n" . '<span class="chapters">'
                .  pluralize($story['chapter']) . ':';

            while ($file = array_shift($file_ids)) {
                $file = explode(':', $file);
                $output .= '<a href="' . $fa_root . 'file.php?file=' . $file[1]
                    . '"> ';

                if ($file[0] == 'P') {
                    $output .= 'Prologue';
                } elseif ($file[0] == 'E') {
                    $output .= 'Epilogue';
                } else {
                    $output .= roman_numeralize($file[0]);
                }

                $output .= ' </a>';

                if (count($file_ids)) $output .= '|';
            }

            $output .= "</span>\n";
        }

        return($output . "</div>\n");
    }

###############################################################################
# FUNCTION:     series_display
#
# ARGS: series  An associative array of series data and metadata
#
# RETURNS:      HTML series information
#
# This function generates attractive HTML for the series and its stories' data.
###############################################################################
    function series_display($series) {
        global $fa_root;

        if (!$series['stories']) return '';

        $output = '<div class="series">The <strong><a href="' . $fa_root
            . 'series.php?series=' . $series['series_id'] . '">'
            . $series['series_name'] . '</a></strong> series';
        if (array_key_exists('author_id', $series)) $output .= ' by <a href="'
            . $fa_root . 'author.php?author=' . $series['author_id'] . '">'
            . $series['author_name'] . '</a>';
        $output .= ":\n";
        reset($series['stories']);

        while ($story = current($series['stories'])) {
            $output .= story_display($story);
            next($series['stories']);
        }

        return($output . "</div>\n");
    }
###############################################################################
###############################################################################
?>
