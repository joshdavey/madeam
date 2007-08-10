<?php
class indexController extends appController {
  
  function index() {
    /**
     * This function is the index controller's index action.
     * To edit this action's view open "/views/index/index.html"
     * 
     * Any PHP code here executes before the view is rendered. 
     * This is the perfect place to get information from your database
     * and send it to your view.
     * 
     * You can send data to your view using $this->set([variable_name], [value]);
     * 
     * For example below we set the page's title. You can see the variable we
     * set by opening "/views/_layouts/standard.html". Look betweent he <title> tags
     */    
    $this->set('page_title', 'Powered By Madeam PHP MVC Framework');
  }
  
}
?>