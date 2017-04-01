<?php

use diversen\mirrorPath;

$m = new mirrorPath();
// $m->deleteBefore = true; // Delete before mirroring. Default setting
// $m->allowTypes = array ('md', 'php'); // Allow md and php files
// $m->disallowTypes = array('md'); // disallow md files. Now only php files will be mirrored
$m->mirror('./vendor', './vendor2'); // mirror
