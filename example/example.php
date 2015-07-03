#!/usr/bin/env php
<?php

require_once('../libs/Process.php');
require_once('../libs/Child.php');
require_once('../libs/Messaging.php');
require_once('../libs/ProcessController.php');
require_once('../libs/ProcessException.php');
require_once('../libs/SocketException.php');

class worker extends \Octris\Proc\Child {
    function run() {
        print "child\n";
        sleep(5);
    }
}

$child = \Octris\Proc\Process::fork('worker');
