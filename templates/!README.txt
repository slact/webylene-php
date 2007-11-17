Webylene

templates

Templates are files that deal strictly with output to the browser. 
Your HTML should be placed here. 

When you create a template, it is magically (and invisibly) loaded 
  into the template config as having a default layout. 
  You can (and often will) override this behavior by configuring the 
  template explicitly in config/templates.yaml

Naming conventions:
  templates have a .tmpl extension
	  Stub templates -- templates that are not part of a layout (those 
	    configured in config/templates.yaml as stub: true) should begin
        with an underscore: _listItem.tmpl

