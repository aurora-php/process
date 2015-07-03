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
        while (true) {
            print "child\n";

            if (($msg = $this->messaging->recv()) !== false) {
                $this->messaging->send(strrev($msg));
            }

            sleep(1);
        }
    }
}

class main extends \Octris\Proc\Process {
    function run() {
        $child = $this->fork('worker');

        $cnt = 1;

        while ($cnt < 4) {
            $child->send('Test #' . $cnt);

            do {
                if (($msg = $child->recv()) !== false) {
                    print trim($msg) . "\n";
                    sleep(1);
                    break;
                }
            } while(true);

            ++$cnt;
        }
    }
}

$main = new main();
$main->run();
