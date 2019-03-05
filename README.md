Edwiser Course Format - Description
===========================
Edwiser Course Format plugin comes with two layouts, List and Cards.
List layout is one of the simplest looking yet power-packed Course Formats out there. You can display all your course topics in the form of Lists!
Card layout lets your learners view all the topics in the form of Cards. With this Card Format, you can display the activities of each section, listed down on the Cards itself.

Edwiser Course Format comes packed with the following features
====================================================================
* List Layout
* List Layout give option to show all sections on single the page.
* List Layout give option to show single section on page.
* Card Layout
* Card Layout also render all the activities from the section in card layout.
* Conditional Logic
* Compatible with Boost Theme.
* Compatible with Fordson Theme.
* Responsive

Plugin Version
==============
v1.0.0 - Plugin Released

Required version of Moodle
==========================
This version works with Moodle 3.4+ version 2017111300.00 (Build: 20171113) and above until the next release.

Please ensure that your hardware and software complies with 'Requirements' in 'Installing Moodle' on
'docs.moodle.org/36/en/Installing_Moodle'.

Free Software
=============
The Edwiser Course Format is 'free' software under the terms of the GNU GPLv3 License, please see 'COPYING.txt'.

The primary source is on add-git-downloadable-link

If you download from the development area - https://github.com/WisdmLabs/moodle-courseformat_remuiformat - consider that
the code is unstable and not for use in production environments.  This is because I develop the next version in stages
and use GitHub as a means of backup.  Therefore the code is not finished, subject to alteration and requires testing.

You have all the rights granted to you by the GPLv3 license.  If you are unsure about anything, then the
FAQ - http://www.gnu.org/licenses/gpl-faq.html - is a good place to look.

If you reuse any of the code then I kindly ask that you make reference to the format.

Support
=======
The Edwiser Course Format comes with NO support.  If you would like support from me (WisdmLabs) then I'm happy to provide it
for a fee (please see my contact details below).  Otherwise, the 'Courses and course formats' forum:add-edwiser-forum-course-format-link is an excellent place to ask questions.

Installation
============
1. Ensure you have the version of Moodle as stated above in 'Required version of Moodle'.  This is essential as the
   format relies on underlying core code that is out of my control.
2. Put Moodle in 'Maintenance Mode' (docs.moodle.org/en/admin/setting/maintenancemode) so that there are no
   users using it bar you as the administrator - if you have not already done so.
3. Copy 'remuiformat' to '/course/format/' if you have not already done so.
4. Go back in as an administrator and follow standard the 'plugin' update notification.  If needed, go to
   'Site administration' -> 'Notifications' if this does not happen.
5. Put Moodle out of Maintenance Mode.
6. You may need to check that the permissions within the 'remuiformat' folder are 755 for folders and 644 for files.

Uninstallation
==============
1. Put Moodle in 'Maintenance Mode' so that there are no users using it bar you as the administrator.
2. It is recommended but not essential to change all of the courses that use the format to another.  If this is
   not done Moodle will pick the last format in your list of formats to use but display in 'Edit settings' of the
   course the first format in the list.  You can then set the desired format.
3. In '/course/format/' remove the folder 'remuiformat'.
4. Put Moodle out of Maintenance Mode.

File information
================

Languages
---------
The remuiformat/lang folder contains the language files for the format.

Note that existing formats store their language strings in the main
moodle.php, which you can also do, but this separate file is recommended
for contributed formats.

Of course you can have other folders as well as English etc. if you want to
provide multiple languages.

Styles
------
The file remuiformat/styles.css contains the CSS styles for the format.

Roadmap
=============
1. Will give option tp add new section and activities without turn on editing mode.

History
=======
See Changes.md

Author
------
Wisdmlabs

Provided by
-----------

[![alt text](https://git.wisdmlabs.net/edwiser/remuiformat/tree/dev/images/readme-img.png)](https://edwiser.org)
