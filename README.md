# Post'Em
A single app, with simplified infrastructure, no token request flow. 

Post'Em was created for people who don't like the Canvas gradebook, or who simply have a preference for storing and calculating their grades locally, and using Canvas only to provide secure, private student access to their grades. It shamelessly copies the same-named Sakai plugin. To learn more about the plugin, visit the help page inside the postem subfolder.

While I have modified these files somewhat in order to simplify installation, there are a fair number of steps you will have to complete to get going. Here is a list of the files you will have to modify. 

canvas_lti/
  sitepaths.php - provides the correct urls for both php and html site-relative access to the canvas_lti folder
  findvalidtoken.php - you need to provide an API access token here. Note that this simplified version has no error catching or reporting, so if your app stops working unexpectedly, check the validity of your token.
postem/
  index.php provide correct path to your sitepaths.php file, also, change the secret to the one you use when you install the app
  common.php - provide a path to a writable file above your site root. This is necessary for the file upload.
  
I don't think that's really all, but that's all I can think of right now.
