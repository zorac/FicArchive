<?php
###############################################################################
###############################################################################
### FicArchive - A complete web-based fiction archive system
### Copyright (C) 2005 Mark Rigby-Jones <mark@rigby-jones.net>
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
### FILE: submit/index.php
###
### This is the main index page for the story submission area.
###############################################################################
###############################################################################
    ini_set('include_path', '../lib');
    require_once('init.php');

    $page_title = 'Submit a Story';
    include('header.php');
?>
<h2>Submit a Story</h2>
<p>Welcome to the <?=$fa_site_name?> submissions page! If you want to submit a
    story to appear in the archive, you'll need to have registered for an
    account and verified your email address. Please also read our
    <a href="../site/guidelines.php">submission guidelines</a>. If you've done
    all that, then start the process below:</p>
<p><form method="post" action="author.php">
<input type="submit" value="Submit a new story or chapter &gt;">
</form></p>
<?php
    include('footer.php');
###############################################################################
###############################################################################
?>
