<?php


class TestKademliaTask extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Task Test');
  }

/*
  public function testEnqueueSync() {
    $settings = new Kademlia\Settings;
    $settings->queue_system = 'sync';

    $called = 0;
    #$task = &new MockTestTask($settings);
    $task->expectOnce('callMe', [$settings, 'foo', 'bar']);
  }
*/
}
?>
