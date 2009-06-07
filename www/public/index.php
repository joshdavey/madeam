<?php 
/**
 * This file exists for servers that don't have mod_rewrite enabled and need a default
 * file to load when accessing a directory (normally index.html or index.php).
 * 
 * If mod_rewrite is enabled it is unecessary and should actually be removed so that cacheing
 * of the home page (index.html) is done properly. Otherwise the server may load index.php 
 * before index.html and your static page cache won't load.
 */
if (file_exists('index.html')) {
  require 'index.html';
} else {
  require 'dispatcher.php';
}