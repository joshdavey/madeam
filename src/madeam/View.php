<?php
namespace madeam;

class View {
  
  
  /**
   * A map of all the file formats to their associated serialization method
   * @author Joshua Davey
   */
  static public $formats = array(
    'xml'   => array('madeam\serialize\Xml',  'encode'),
    'json'  => array('madeam\serialize\Json', 'encode'),
    'sphp'  => array('madeam\serialize\Sphp', 'encode')
  );
  
  /**
   * Full path to templates directory
   *  example: /var/www/project/app/views/
   */
  static public $path = null;
  
  /**
   * Renders the output of the request. Can also be used to render partials and other views.
   * examples: 
   *  madeam\Controller::render(array('template' => 'posts/read', 'data' => array('post' => $post)));
   *  madeam\Controller::render(array('template' => 'posts/read', 'layout' => array('master'), 'data' => array('post' => $post)));
   * 
   *  madeam\View::render(array('template' => 'posts/read', 'data' => array(), 'layout' => array()));
   *  madeam\View::render(array('template' => 'posts/read', 'layout' => array('master'), 'data' => array('post' => $post)));
   * 
   *  madeam\Framework::request(array('_controller' => 'posts', '_action' => 'read'));
   *  madeam\Framework::request('posts/read/32');
   * @return string
   */
  static public function render($__settings) {
    // set format
    $__format = substr($__settings['template'], strrpos($__settings['template'], '.'));
    
    // set layout
    isset($__settings['layout']) ?: $__settings['layout'] = array();
    
    // set default value for data
    isset($__settings['data']) ?: $__settings['data'] = array();
    
    // set template
    $__template = self::$path . str_replace('/', DIRECTORY_SEPARATOR, strtolower($__settings['template'])) . $__format;
    
    // check if the view exists
    // if the view doesn't exist we need to serialize it.
    if (file_exists($__template)) {
      // extract data to view and layout
      extract($__settings['data']);
      
      // render view's content
      ob_start();
        require($__template);
        $_content = ob_get_contents();
      ob_end_clean();
      
      // apply layout to view's content
      if (isset($__settings['layout'])) {
        foreach ($__settings['layout'] as $__layout) {
          if ($__format != null) {
            $__format = '.layout' . $__format;
          }
          $__layout = self::$path . $__layout . $__format;
          
          // render layouts
          ob_start();
            require($__layout);
            $_content = ob_get_contents();
          ob_clean();
        }
      }
    } else {
      throw new controller\exception\MissingView('Missing View: <strong>' . $__template . "</strong> and unknown serialization format \"<strong>" . $__format . '</strong>"' . "\n Create File: <strong>" . $__template . "</strong>");
    }
    
    return $_content;
  }
  
}