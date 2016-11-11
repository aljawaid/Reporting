Kanboard Plugin for notes
==========================

This plugin adds a budget/reporting feature to Kanboard.

Plugin for [Kanboard](https://github.com/fguillot/kanboard)

Author
------

- [TTJ](https://gitlab.com/u/ThomasTJ)
- License MIT

Installation
------------

- Decompress the archive in the `plugins` folder

or

- Create a folder **plugins/Reporting**
- Copy all files under this directory

or

- Clone folder with git

Use
---

Add a budget line for the projects.
Add used budget to each project with detailed comment.
Create report over budget and used budget with latest comment.

Todo
----

- Make datepicker for reporting
- Improve CSS for print
- Markups as Kanboard
- Create specific sidebar for the plugin
- Remove 2 decimals from percentage when 100%
- Refresh div after deleting, refreshing or adding

Security issues and bugs
------------------------

- Verify that un-authorized users cant access others data
- Data is user-specific

Tested on
---------

- Application version: 1.0.32
- PHP version: 5.5.9-1ubuntu4.17
- PHP SAPI: apache2handler
- OS version: Linux 3.13.0-74-generic
- Database driver: sqlite
- Database version: 3.8.2
- Browser: Chromium 51.0.2704.106 (64-bit)
