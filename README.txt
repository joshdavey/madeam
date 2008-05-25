/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

/* Installation (Root Directory)
-------------------------------------------------------- */

1. Unzip your folders into the root directory
2. Edit config/setup.php (Select your development environment - Defaults to 'development')
4. Visit http://yourwebsite.com/ (You will see an error because you haven't setup any controllers or views)

/* Adding A Controller
-------------------------------------------------------- */

1. Open the directory app/Controller/
2. Create a new file. Example: 'Index.php'
3. Define your controller's class:


<?php
class Controller_Index extends Controller_App {

}
?>


4. Visit http://yourwebsite.com/index (You will see an error because you need to add an action to your controller)

/* Adding An Action To A Controller
-------------------------------------------------------- */

1. Open one of your controllers. Example: 'app/Controller/Index.php'
2. Add a new method to your controller's class. The method should be named after your action. Example: 'index'


<?php
class Controller_Index extends Controller_App {
  function index() {

  }
}
?>


3. Visit http://yourwebsite.com/index/index (You will see an error because you haven't added a view for your action)

/* Adding An Action's View
-------------------------------------------------------- */

1. Open the directory app/View/[lowercase controller name]/ where the name of the controller is the controller the action is in
2. Create a new file. Example: index.html where 'index' is the name of the action
3. Visit http://yourwebsite.com/index/index


Note: You do not need to add a controller or action to view a static page.
For example if your http://yourwebsite.com/index/index is static then you just need to make a directory
in your views directory called 'index' and create an html file in the directory you just created called 'index.html'

Example: app/View/index/index.html


/* Creating A Model
-------------------------------------------------------- */

1. Open the directory app/Model
2. Create a new file. Example: Article.php (All model names must be singular)
3. Define your model's class:


<?php
class Model_Article extends Madeam_ActiveRecord {

}
?>