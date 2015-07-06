#!/usr/bin/env php
<?php

require_once('../vendor/autoload.php');

class worker extends \Octris\Process\Child {
    function run() {
        sleep(2);
        1 / 0;
    }
}

$proc = new \Octris\Process();
$cnt = 1;

while ($cnt < 4) {
    $child = $proc->fork('worker');

    while($child->isAlive()) {
        sleep(1);
    }

    print "child process died\n";

    ++$cnt;
}
