Webylene

scripts

Page-specific scripts go here.

When a page is requested, the router matches the url and GET and POST 
  parameters (the "path") to a script (the "target). these routes can
  be configured in config/routes.yaml
  
If you want View and Controller separatio, don't output anything from 
  these scripts. Use template::out("templateName") when appropriate.