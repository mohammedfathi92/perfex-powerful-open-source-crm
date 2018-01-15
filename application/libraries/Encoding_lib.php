<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
include_once(APPPATH.'third_party/Encoding.php');
use \ForceUTF8\Encoding;

class Encoding_lib
{
    public function toUTF8($string)
    {
        return Encoding::toUTF8($string);
    }
}
