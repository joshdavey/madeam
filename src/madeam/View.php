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
  static public function render($_settings) {
    // set format
    $_format = isset($_settings['format']) ? '.' . $_settings['format'] : $_settings['format'] =  null;
    
    // set layout
    isset($_settings['layout']) ?: $_settings['layout'] = array();
    
    // set default value for data
    isset($_settings['data']) ?: $_settings['data'] = array();
    
    // set template
    $_template = self::$path . str_replace('/', DIRECTORY_SEPARATOR, strtolower($_settings['template'])) . $_format;
    
    // check if the view exists
    // if the view doesn't exist we need to serialize it.
    if (file_exists($_template)) {
      // extract data to view and layout
      extract($_settings['data']);
      
      // render view's content
      ob_start();
        include($_template);
        $_content = ob_get_contents();
      ob_end_clean();
      
      // apply layout to view's content
      if (isset($_settings['layout'])) {
        foreach ($_settings['layout'] as $_layout) {
          if ($_format != null) {
            $_format = '.layout' . $_format;
          }
          $_layout = self::$path . $_layout . $_format;
          
          // render layouts
          ob_start();
            include($_layout);
            $_content = ob_get_contents();
          ob_clean();
        }
      }
    } else {
      // serialize output
      if (isset(self::$formats[$_settings['format']]) && method_exists(self::$formats[$_settings['format']][0], self::$formats[$_settings['format']][1])) {
        $_content = call_user_func(self::$formats[$_settings['format']], $_settings['data']);
      } else {
        throw new controller\exception\MissingView('Missing View: <strong>' . $_template . "</strong> and unknown serialization format \"<strong>" . $_settings['format'] . '</strong>"' . "\n Create File: <strong>" . $_template . "</strong>");
      }
    }
    
    return $_content;
  }
  
}