<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'third_party/elfinder/elFinderConnector.class.php';
include_once APPPATH.'third_party/elfinder/elFinder.class.php';
include_once APPPATH.'third_party/elfinder/elFinderVolumeDriver.class.php';
include_once APPPATH.'third_party/elfinder/elFinderVolumeLocalFileSystem.class.php';

class Elfinder_lib
{
  public function __construct($opts)
  {
    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
  }
}
