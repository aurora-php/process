#!/usr/bin/env php
<?php

require_once('../vendor/autoload.php');

class worker extends \Octris\Process\Child {
    function run() {
        sleep(2);
    }
}

$proc = new \Octris\Process();
$child = $proc->fork('worker');

do {

} while(true);

print "done\n";
