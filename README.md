Elgg Web Services
=================

List of Web Services
--------------------

### Core

 * site.test Heartbeat method to test whether web services are up
 * site.getinfo Get basic information about this Elgg site

### User

 * user.profilelabels Get profile labels
 * user.getprofile    Get profile information
 * user.updateprofile Update profile information
 * user.getbyemail    Get all users registered with an email ID
 * user.checkavail    Check availability of username
 * user.register      Register user
 * user.addfriend     Add a user as a friend
 * user.removefriend  Remove a user from friend
 * user.getfriend     Get friends of a user
 * user.getfriendof   Obtains the people who have made a given user a friend

### Blog

 * blog.post   Make a blog post
 * blog.read   Read a blog post
 * blog.delete Delete a blog post
 * blog.friend Get latest blog post by friends
 * blog.user   Latest blog post by a user
 
### Group

 * group.join        Joining a group
 * group.leave       Leaving a group
 * group.post        Posting a new topic to a group
 * group.deletepost  Deleting a topic from a group
 * group.latest      Get latest post in a group
 * group.getreplies  Get replies on a post
 * group.reply       Post a reply
 * group.deletereply Delete a reply

### Wire

 * wire.post   Making a wire post
 * wire.read   Read latest wire post of user
 * wire.friend Read latest wire post by friends
 
### File

 * file.all    Get file list by all users
 * file.friend Get file list by friends
 * file.user   Get file list by a users
