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

        while (true) {
            if (($msg = $this->messaging->recv()) !== false) {
                print trim($msg) . "\n";
            }

            sleep(1);
        }
    }
}

$main = new \Octris\Proc\Process();

$child = $main->fork('worker');
$child->send('Test');
