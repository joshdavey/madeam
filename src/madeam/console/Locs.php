<?php
class madeam\Console_Script_Locs extends madeam\Console_Script {
  
  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
  public function all() {
    $totalLocs =  (int) $this->_countLOCs(Framework::$pathToRoot, array(
      Framework::$pathToEtc,
      Framework::$pathToRoot . 'README',
      Framework::$pathToRoot . 'LICENSE',
      Framework::$pathToRoot . 'index.php',
      Framework::$pathToRoot . 'env.php',
      Framework::$pathToPub . 'index.php',
      Framework::$pathToPub . 'dispatcher.php'
    ));
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Project', $totalLocs));
    madeam\console\CLI::out(sprintf("------------------------------"));
  }
  
  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
  public function madeam() {
    $madeamLocs = (int) $this->_countLOCs(Framework::$pathToLib . 'Madeam' . DS . 'src');
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Madeam', $madeamLocs));
    
    $madeamTestLocs = (int) $this->_countLOCs(Framework::$pathToLib . 'Madeam' . DS . 'tests' . DS);
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Madeam Tests', $madeamTestLocs));
    
    $totalLocs = $madeamLocs + $madeamTestLocs;
    madeam\console\CLI::out(sprintf("=============================="));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Total', $totalLocs));
  }
  
  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
  public function app() {
    $appLocs = (int) $this->_countLOCs(Framework::$pathToApp . 'src' . DS);
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Application', $appLocs));
    
    $testLocs = (int) $this->_countLOCs(Framework::$pathToApp . 'tests' . DS);
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Tests', $testLocs));
    
    $libLocs = (int) $this->_countLOCs(Framework::$pathToLib, array(
      Framework::$pathToLib . 'Madeam' . DS
    ));
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Library', $libLocs));
    
    $staticLocs = (int) $this->_countLOCs(Framework::$pathToPub, array(
      Framework::$pathToPub . 'dispatcher.php',
      Framework::$pathToPub . 'index.php'
    ));
    madeam\console\CLI::out(sprintf("------------------------------"));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Static', $staticLocs));
    
    $totalLocs = $appLocs + $testLocs + $libLocs + $staticLocs;
    madeam\console\CLI::out(sprintf("=============================="));
    madeam\console\CLI::out(sprintf("%-20s%10d", 'Total', $totalLocs));
  }
    
  /**
   * undocumented
   * @author Joshua Davey
   */
  private function _countLOCs($path, $ignore = array(), $exts = array('php', 'html', 'xml', 'css', 'js', 'inc')) {
    $locs = 0;
    foreach (new DirectoryIterator($path) as $file) {
      if ($file->isDir()) {
        $filePath = $file->getPathname() . DS;
      } else {
        $filePath = $file->getPathname();
      }
      
      $ext = substr($file->getFilename(), strrpos($file->getFilename(), '.') + 1);
      
      if (!$file->isLink() && !$file->isDot() && substr($file->getFilename(), 0, 1) != '.' && !in_array($filePath, $ignore)) {
        if ($file->isDir()) {
          $locs += $this->_countLocs($filePath);
        } elseif (in_array($ext, $exts)) {
          $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $locs += count($lines);
        }
      }
    }
    return $locs;
  }
  
}