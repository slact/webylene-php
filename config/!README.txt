Webylene

config

Here be config files. They can be yaml or php. all configs here are loaded
automatically, except for anything in subdirectories (such as config/delayed)

php configs (such as env.php) are expected to only have define()s, 
and are loaded right after core libs. use them to set ENV and the like.

Default yaml config file list:
	app.yaml			general webylene config. place general env config here.
	templates.yaml		template config
	urls.yaml			url to script mappings. You'll want to play with this one.
