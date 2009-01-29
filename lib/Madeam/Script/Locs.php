<?php
class Madeam_Script_Locs extends Madeam_Console_Script {
  
  public function all() {    
    $appLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToApp);
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Application', $appLocs));
    
    $testLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToTests, array(
      Madeam_Framework::$pathToTests . 'Madeam' . DS,
      Madeam_Framework::$pathToTests . 'AllTests.php',
      Madeam_Framework::$pathToTests . 'Bootstrap.php',
    ));
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Tests', $testLocs));
    
    $libLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToLib, array(
      Madeam_Framework::$pathToMadeam
    ));
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Library', $libLocs));
    
    $staticLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToPublic, array(
      Madeam_Framework::$pathToPublic . 'index.php'
    ));
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Static', $staticLocs));
    
    $madeamLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToMadeam);
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Madeam', $madeamLocs));
    
    $madeamTestLocs = (int) $this->_countLOCs(Madeam_Framework::$pathToTests);
    Madeam_Console_CLI::out(sprintf("------------------------------"));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Madeam Tests', $madeamTestLocs));
    
    $totalLocs =  (int) $this->_countLOCs(Madeam_Framework::$pathToProject, array(
      Madeam_Framework::$pathToEtc,
      Madeam_Framework::$pathToProject . 'README',
      Madeam_Framework::$pathToProject . 'LICENSE',
      Madeam_Framework::$pathToProject . 'index.php',
      Madeam_Framework::$pathToProject . 'env.php',
      Madeam_Framework::$pathToPublic . 'index.php'
    ));
    Madeam_Console_CLI::out(sprintf("=============================="));
    Madeam_Console_CLI::out(sprintf("%-20s%10d", 'Total', $totalLocs));
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
      
      if (!$file->isLink() && !$file->isDot() && substr(basename($file->getFilename()), 0, 1) != '.' && !in_array($filePath, $ignore)) {
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