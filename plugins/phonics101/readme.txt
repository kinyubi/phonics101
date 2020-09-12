=== Phonics 202 ===
Contributors: carlbaker@gmail.com
Stable tag: 1.1.2
Tested up to: 5.4
Requires at least: 4.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a custom plugin for integrating readxyz.org content with readxyz.com WordPress content

== Description ==

This is a custom plugin for integrating readxyz.org content with readxyz.com WordPress content. This plugin creates three shortcodes:

== phonics202 shortcode ==

The shortcode tag is ``[phonics202]``. Without any parameters specified, it passes in the current logged in user's username and passes it to phonics202 for validation. There are three flavors of usernames:
1. an alphanumeric username (not an email address),
2. an email address, or
3. an email address followed by a hyphen and a student's first name.

**Alphanumeric username**. When the username is in this form, it will search the phonics202 database for the given username. If such a username is found and the user is associated with one or more students, then it will proceed to automatically log the user into the phonics lessons subsystem and the shortcode will generate an `iframe` displaying the *Lessons* screen. If the username is not found or the username is not associated with a student, an error message is displayed. ***NOTE:*** *If the `studentfirstname` parameter is specified in the shortcode, there will be enough information for the phonics subsystem to register the username-student as a valid account and display the ***Lessons*** screen.*

**Email address**. When the username is an email address, it is treated exactly like an alphanumeric username. ***NOTE:*** *If the `studentfirstname` parameter is specified in the shortcode, there will be enough information for the phonics subsystem to register the username-student as a valid account and display the ***Lessons*** screen.*

**Email address hyphen studentname**. Current readXYZ users have a username that is comprised of an email address, followed by a hyphen and then the student's first name. For example `janedoe@gmail.com-bobby` would be considered a valid readXYZ username. In this instance, a search of the phonics database for this username-student combination is made. If found, the user is automatically logged into the phonics lessons subsystem and the shortcode will generate an `iframe` displaying the ***Lessons*** screen.

If the username-student combination is not found, an account for this user and student is registered into the phoncs lessons subsystem, the proper housekeeping is performed and the user is automatically logged into the phonics lessons subsystem with the newly created credentials and the shortcode will generate an `iframe` displaying the ***Lessons*** screen.

This shortcode has two optional parameters `login` and `studentfirstname`. 

**login=username**. If a specific username is specified, the given username will override the username of the currently logged in WordPress user.

**studentfirstname=name**. If a student first name is specified, it will be used to create an account on the phonics subsystem for the username and and the student. ***WARNING:*** *This should not be used if the username is of the format `emailaddress-student`*.