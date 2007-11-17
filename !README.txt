Webylene

1. Introduction
  Webylene is a MVC-ish web framework that tries to have a very small 
  learning   curve to get started, and doesn't tie your arms behind your back.
  
2. Directory Layout
  webylene root/
    bootstrap.php 	-	the bootstrap. don't mess with it.
    /classes	-	-	classes dir.
	  /core	-	-	-	core classes. don't mess with them.
	    ...
	  /plugins	-	-	plugin classes. place plugins here.
	    ...
	  ...	-	-	-	place classes you'll use here
	/config	-	-	-	config dir.
	  /delayed			
	    ...		-	-	place configs that shouldn't be autoloaded here
	  env.php	-	-	environment config. you'll likely need to modify it.
	  ...			
    /libs	-	-	-	libs go here
	  /core	-	-	-	core libs. don't mess with them.
	  ...	-	-	-	place libs here. libs shouldn't define any classes.
	/scripts	-	-	page-specific scripts
	  ...	-	-	-	put your scripts here. the router will map 
						  urls to scripts. see the router config.
	/templates	-	-	templates (a.k.a. views) should be here
	  ...	-	-	-	templates are the actual output to browser.
	/web	-	-	-	server root. DON'T put php scripts here.
	  css/	-	-	-	put css here
	  js/	-	-	-	put js here
	  index.php	-	-	don't mind me. don't edit me, either.
	  .htaccess	-	-	Apache htaccess that directs url handling to the router
	  

3. Getting Started
  edit config/env.php
  edit config/app.yaml - set database stuff and env stuff
  edit config/routes.yaml - make some routes
  write scripts to scripts/
  look at config.yaml
  make templates in templates/
  
4. How does it work?
  wouldn't you like to know?
  
5. Who made it?
  Leo Ponomarev
  
6. What licence is it distributed under?
  see licence.txt