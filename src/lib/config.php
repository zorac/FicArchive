<?php
    ###########################################################################
    #
    #  SITE-SPECIFIC - YOU SHOULD CHANGE ALL OF THESE TO APPROPRIATE VALUES
    #
    ###########################################################################

    #
    # Details of your site - give it a name!
    #
    $fa_site_name       = 'FicArchive';

    #
    # Details of where this site is located. For example, a site located at
    # https://www.example.org/fic/ would enter 'www.example.org', '/fic', TRUE.
    # The path should be blank if you aren't in a subdirectory.
    #
    $fa_server_name     = 'www.example.org';
    $fa_server_path     = '';
    $fa_server_secure   = FALSE;

    #
    # The details of the MySQL database to use: the hostname of the server
    # on which the database is running, the username and password to connect
    # with, and the name of the database on that server.
    #
    $fa_mysql_server    = 'mysql';
    $fa_mysql_username  = 'fa';
    $fa_mysql_password  = 'changeme';
    $fa_mysql_database  = 'fa';

    #
    # Sender details for outgoing emails - the name and address that you' like
    # to appear as the 'From' address on outgoing emails.
    #
    $fa_email_address   = 'moderator@example.org';
    $fa_email_name      = "$fa_site_name Moderator";

    ###########################################################################
    #
    #  OPTIONAL STUFF - THERE'S NO NEED TO CHANGE THESE UNLESS YOU WANT TO
    #
    ###########################################################################

    #
    # The timezone you want to use for your archive.
    #
    $fa_timezone        = 'UTC';

    #
    # If you have a page with a privacy policy on it, then you can have a link
    # to it added automatically on any page which collects personal information
    # by adding the URL here.
    #
    #$fa_privacy_url    = '/site/privacy.php';

    #
    # You can set a minimum age (in years) for access to the site - persons
    # under that age will not be permitted to register. Leave as 0 to disable
    # this feature.
    #
    $fa_minimum_age     = 0;

    #
    # You can specify a disclaimer for the site - this will be displayed at
    # the top of every story or chapter.
    #
    $fa_disclaimer      = '';

    #
    # Names for the cookies used to set the session and other data. You
    # shouldn't need to change these unless you are running multiple archives
    # on the same website.
    #
    $fa_session_name    = 'fa_session';
    $fa_cookie_username = 'fa_username';
    $fa_cookie_autologin= 'fa_autologin';

    #
    # How long cookies (username and autologin) should last for, in seconds.
    #
    $fa_cookie_time     = 86400 * 28;

    #
    # Themes (CSS stylesheets) available for the site. The first one listed
    # will be used as the default for users who don't select one. The filename
    # parameter should be the path to the CSS file from the site's root.
    #
    $fa_themes = array(
        'Default'       => array(
            'desc'      => 'The default theme.',
            'file'      => 'css/default.css'
        )
    );

    #
    # Date ranges for the search page. Key is the number of days, value is the
    # text to display.
    #
    $fa_search_dates = array(
        ''      => 'All',
        7       => 'Last 7 Days',
        30      => 'Last 30 Days',
        92      => 'Last 3 Months',
        184     => 'Last 6 Months',
        366     => 'Last Year'
    );

    #
    # Per-page options for search results.
    #
    $fa_search_perpage = array(
        10      => '10',
        20      => '20',
        30      => '30',
        40      => '40',
        'all'   => 'All'
    );

    #
    # Absolute maximum and minimum search results per page (All results per
    # page will in fact mean the maximum value given here).
    #
    $fa_min_perpage = 10;
    $fa_max_perpage = 1000;

    #
    # If you're upgrading from a different archive system and need users to
    # re-register - or to register for the first time - set this to the text
    # you want displayed when a login fails.
    #
    #$fa_login_fail_text = 'We have recently upgraded to new software on our '
    #   . 'web site. Unfortunately, it was not possible to move existing '
    #   . 'logins onto the new system. If your old username and password '
    #   . 'do not work, simply <a href="' . $fa_server_path
    #   . '/profile/register.php">visit our registration page</a> and create '
    #   . 'a new account. Please accept our apologies for any inconvenience '
    #   . 'caused.';

    #
    # Set this to true to disable all posting of neew threads and comments to
    # the message boards and review boards.
    #
    $fa_disable_posting = FALSE;

    #
    # Enable debug mode - set this to true to add more debugging output when
    # errors occur (and, indeed, flag up more errors).
    #
    $fa_debug_mode      = TRUE;

    ###########################################################################
    #
    #  CONSTANTS - DON'T CHANGE THESE UNLESS YOU REALLY KNOW WHAT YOU'RE DOING
    #
    ###########################################################################

    #
    # The file number to use for prologues and epilogues.
    #
    $fa_fileno_prologue = 0;
    $fa_fileno_epilogue = pow(2, 31) - 1;

    #
    # The list of possible genders. The first letter of each MUST be unique.
    #
    $fa_gender_list     = array('Male', 'Female', 'Transgender', 'Agender',
                                'Bigender', 'Pangender', 'Non-binary',
                                'Genderfluid', 'Unknown');

    #
    # A regular expression to check for valid email addresses.
    #
    $fa_regex_email     = '/^[^@<>"\s]+@[^@<>"\s]+\.[^@<>"\s]+$/';

    #
    # An array of HTML tags to allow. Tag names are keys, values are arrays
    # with optional parameters for that tag: 'noclose' means not to bother
    # adding closing tags if omitted, 'allow' gives an array of allowed
    # parameters the tag may have, 'makelink' means replace the tag with a
    # link to the url in the given parameter.
    #
    $fa_html_tags       = array(
        'a'             => array('allow' => array('href' => TRUE)),
        'abbr'          => array('allow' => array('title' => TRUE)),
        'acronym'       => array('allow' => array('title' => TRUE)),
        'address'       => array(),
        'b'             => array(),
        'big'           => array(),
        'blockquote'    => array(),
        'br'            => array('noclose' => TRUE),
        'center'        => array(),
        'cite'          => array(),
        'code'          => array(),
        'dd'            => array('noclose' => TRUE),
        'del'           => array(),
        'dfn'           => array(),
        'dir'           => array(),
        'div'           => array('allow' => array('align' => TRUE)),
        'dl'            => array(),
        'dt'            => array('noclose' => TRUE),
        'em'            => array(),
        'font'          => array('allow' => array('face' => TRUE,
                                                  'size' => TRUE)),
        'h1'            => array(),
        'h2'            => array(),
        'h3'            => array(),
        'h4'            => array(),
        'h5'            => array(),
        'h6'            => array(),
        'hr'            => array('noclose' => TRUE),
        'i'             => array(),
        'img'           => array('noclose' => TRUE, 'makelink' => 'src'),
        'ins'           => array(),
        'kbd'           => array(),
        'li'            => array('noclose' => TRUE),
        'menu'          => array(),
        'ol'            => array(),
        'p'             => array('allow' => array('align' => TRUE),
                                                  'noclose' => TRUE),
        'pre'           => array(),
        'q'             => array(),
        's'             => array(),
        'samp'          => array(),
        'small'         => array(),
        'span'          => array(),
        'strike'        => array(),
        'strong'        => array(),
        'sub'           => array(),
        'sup'           => array(),
        'tt'            => array(),
        'u'             => array(),
        'ul'            => array(),
        'var'           => array()
    );
?>
