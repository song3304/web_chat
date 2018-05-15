<?php
use Workerman\Worker;
use App\ChatServer;

$io = new ChatServer();

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
