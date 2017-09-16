# FicArchive

A single-fandom fanfiction archive. This software was originally developed for
the (now-defunct) RestrictedSection.org website; as such it's now abandoned,
and some planned features are unfinshed. You probably don't want to use this
for creating a new archive.

## Features

* Supports single- and multi-chapter stories, and multi-story series
* Easy browsing of stories by title, author and date
* Extensive search features, including advanced pairing searches
* Age ratings and access restrictions
* User registration and validation, with public profiles
* Advanced bookmarking, including public bookmarks
* Comment, review and cusomisable global message boards with threading
* Moderator and administrator funcionality with large admin section
* No JavaScript, CSS for basic styling only

## Un-Features

* User-upload of stories is not implemented; this must be done by moderators
* Alerting of new stories is only implemented for a global mailing list
* Requires old and insecure versions of PHP, MySQL, etc
* Has other security issues

## Running

FicArchive expects an Apache HTTPD web server with PHP 5.6 and the old mysql
extension, and a MySQL 5.5 database server. The included Docker configs give
an example test environment.
