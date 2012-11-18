BP Mystery Man Hunt
===================

BP Mystery Man Hunt is a BuddyPress bp-default child theme. it features a collection of tips to try to encourage network members to have an avatar. In other words, we hunt mystery men avatars !
I wrote a detailed description about it on [my blog](http://imath.owni.fr/2012/11/18/buddypress-avatar-management/)
This theme is available in french and in english.



BuddyBooth
----------

Inside the theme, you'll find this little component to add a photobooth utility. Users will still be able to use Gravatar, to upload an image in the BuddyPress powered network and they will be able to take a picture of themselves. Depending on the getUserMedia() support of the browser, the component will load alternatively in HTML5 or Flash.
You can watch a demo of it on my [vimeo](https://vimeo.com/53758215)



BP Profile Progression
----------------------

If you are using this plugin, the theme is adding some new behaviors such as taking in account the avatar in the progression.
To disable these features, simply comment lines 140 to 142 of the functions.php file



CubePoints and CubePoints BuddyPress Integration
------------------------------------------------

The theme also check for these plugins and if it finds them, then will display the points of each user under their avatar.
You'll also be able to order the members by their amount of points in BuddyPress members directory / user's friends. If you use BP Show Friends, it will also do the same with it.
To disable these features, simply comment lines 131 to 133 of the functions.php file


Avatar tricks
-------------

+ adds extra info under the avatar
+ explains how to switch default avatar by user associating an xprofile value with an image
+ uses an ugly animated gif to encourage users to add an avatar to their profile
+ replaces the user's avatar in blog loops by a featured image of the blog.

