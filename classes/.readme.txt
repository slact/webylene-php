Webylene

classes

Place classes you write here. 
Webylene uses the php __autoload interpreter hook to auto-include classes.
Thus a file defining class fOo must be called fOo.php, case-sensitivity 
and all.
Unlike stuff ib classes/core/ and classes/plugins, these classes are not
checked for event listeners. If you need to do certain things at a particular 
point of execution in the core (say, right before routing), you will need to
make your class a plugin.