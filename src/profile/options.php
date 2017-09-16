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
### FILE: profile/options.php
###
### This page displays the site options for a profile and allows the user to
### customise information display and the site theme.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('config.php');
    require_once('init.php');
    require_once('profile.php');

    if ($_POST['update']) {
        $options = array();

        if ($_POST['opt_reviews']) $options['show_reviews'] = TRUE;
        if (!$_POST['opt_rating']) $options['hide_rating'] = TRUE;
        if ($_POST['opt_date']) $options['show_date'] = TRUE;
        if ($_POST['opt_size']) $options['show_size'] = TRUE;
        if ($_POST['opt_category']) $options['show_categories'] = TRUE;
        if (!$_POST['opt_pairing']) $options['hide_pairings'] = TRUE;
        if (!$_POST['opt_summary']) $options['hide_summary'] = TRUE;
        if (!$_POST['opt_chapter']) $options['hide_chapters'] = TRUE;
        if ($_POST['opt_search'] == 'advanced')
            $options['advanced_search'] = TRUE;
        if ($_POST['opt_disclaimer'] == 'session')
            $options['per_session'] = TRUE;
        if ($_POST['opt_disclaimer'] == 'password')
            $options['password_always'] = TRUE;
        if ($_POST['theme'] && array_key_exists($_POST['theme'], $fa_themes))
            $options['theme'] = $_POST['theme'];

        $_SESSION['options'] = $options;

        mysql_query("
            UPDATE      fa_user
            SET         options = '" . mysql_escape_string(serialize($options))
                                    . "'
            WHERE       user = " . $_SESSION['user']
        );
    }

    $page_title = 'User Profile - ' . $_SESSION['username'] . ' - Options';
    include('header.php');
    echo '<h2>User Profile: ', $_SESSION['username'], "</h2>\n",
        '<p><a href="./">Back to the main Profile page</a></p>', "\n",
        "<h3>Site Customisation Options</h3>\n";
    if ($_POST['update']) echo '<p class="notice">Your customisation options',
        " have been updated.</p>\n";
?>
<form method="post" action="options.php">
<p class="options">You can choose what additional information you want to see
    on the search results and story browsing pages:<br>
<input id="reviews" name="opt_reviews" type="checkbox"<?=($_SESSION['options']['show_reviews'] ? ' checked' : '')?>> <span class="reviews"><label for="reviews">The number of reader reviews of the story</label>.</span><br>
<input id="rating" name="opt_rating" type="checkbox"<?=($_SESSION['options']['hide_rating'] ? '' : ' checked')?>> <span class="rating"><label for="rating">The story's rating</label>.</span><br>
<input id="date" name="opt_date" type="checkbox"<?=($_SESSION['options']['show_date'] ? ' checked' : '')?>> <span class="date"><label for="date">The date the story was last updated</label>.</span><br>
<input id="size" name="opt_size" type="checkbox"<?=($_SESSION['options']['show_size'] ? ' checked' : '')?>> <span class="size"><label for="size">The word count of the story</label>.</span><br>
<input id="category" name="opt_category" type="checkbox"<?=($_SESSION['options']['show_categories'] ? ' checked' : '')?>> <span class="categories"><label for="category">The categories the story is in</label>.</span><br>
<input id="pairing" name="opt_pairing" type="checkbox"<?=($_SESSION['options']['hide_pairings'] ? '' : ' checked')?>> <span class="pairings"><label for="pairing">The pairings featured in the story</label>.</span><br>
<input id="summary" name="opt_summary" type="checkbox"<?=($_SESSION['options']['hide_summary'] ? '' : ' checked')?>> <label for="summary">The story's summary</label>.<br>
<input id="chapter" name="opt_chapter" type="checkbox"<?=($_SESSION['options']['hide_chapters'] ? '' : ' checked')?>> <label for="chapter">Chapter links</label> (multi-chaptered story only).</p>
<?php
    if (count($fa_themes) > 1) {
?>
<p>You can choose a display theme for the website (if your browser supports
    stylesheets):
<?php
        foreach ($fa_themes as $theme_name => $theme) {
?>
<br><input id="theme-<?=$theme_name?>" name="theme" type="radio"<?=(($theme_name == $session_theme) ? ' checked' : '')?> value="<?=$theme_name?>"> <strong><label for="theme-<?=$theme_name?>"><?=$theme_name?></label></strong> - <?=$theme['desc']?>
<?php
        }

        echo "</p>\n";
    }
?>
<p>You can select which search page is displayed by default:<br>
<input id="simple" name="opt_search" type="radio" value="simple"<?=($_SESSION['options']['advanced_search'] ? '' : ' checked')?>> <strong><label for="simple">Simple</label></strong> - the uncomplicated <a href="../search.php?mode=simple">default search page</a>.<br>
<input id="advanced" name="opt_search" type="radio" value="advanced"<?=($_SESSION['options']['advanced_search'] ? ' checked' : '')?>> <strong><label for="advanced">Advanced</label></strong> - the feature-rich <a href="../search.php?mode=simple">full search page</a>.<p>
<p>You can also select how often the site should show you the age warning/disclaimer page:<br>
<input id="session" name="opt_disclaimer" type="radio" value="session"<?=($_SESSION['options']['per_session'] ? ' checked' : '')?>> <strong><label for="session">Once per session</label></strong> - you will see the disclaimer once after you log in, but not again until you log out or close your web browser (most convenient if only adults use your computer).<br>
<input id="always" name="opt_disclaimer" type="radio" value="always"<?=(($_SESSION['options']['per_session'] || $_SESSION['options']['password_always']) ? '' : ' checked')?>> <strong><label for="always">Every story</label></strong> - you will see the disclaimer for each story or chapter you read.<br>
<input id="password" name="opt_disclaimer" type="radio" value="password"<?=($_SESSION['options']['password_always'] ? ' checked' : '')?>> <strong><label for="password">Always request password</label></strong> - not only will you always see the disclaimer page, but your password is required for each story you view (safest if children also use your computer).</p>
<p><input type="submit" name="update" value="Update all of your profile's options"></p>
</form>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
