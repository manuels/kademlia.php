<?php

class TestTask extends Kademlia\Task {
  public function enqueueSelf() {
    return enqueueSelfPrototype();
  }
}

class TestKademliaTask extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Task Test');
  }


  private function setupTaskAndCallbacks() {
    $settings = new Kademlia\Settings;
    $settings->queue_system = 'sync';

    $task = &new TestTask($settings);

    $callback_done = &new TestCallback();
    $callback_success = &new TestCallback();
    $callback_failed = &new TestCallback();

    return [
      'task' => $task,
      'callback_done' => $callback_done,
      'callback_success' => $callback_success,
      'callback_failed' => $callback_failed
    ];
  }


  public function testSuccessCallbackAfterEmit() {
    $res = $this->setupTaskAndCallbacks();
    $res['task']->emit('success', ['foo', 'bar']);

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_failed']->expectNever('callMe');
  }


  public function testSuccessCallbackBeforeEmit() {
    $res = $this->setupTaskAndCallbacks();

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['task']->emit('success', ['foo', 'bar']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_failed']->expectNever('callMe');
  }


  public function testFailedCallbackAfterEmit() {
    $res = $this->setupTaskAndCallbacks();
    $res['task']->emit('failed', ['foo', 'bar']);

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectNever('callMe');
    $res['callback_failed']->expectOnce('callMe', ['foo', 'bar']);
  }


  public function testFailedCallbackAfterBefore() {
    $res = $this->setupTaskAndCallbacks();

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['task']->emit('failed', ['foo', 'bar']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectNever('callMe');
    $res['callback_failed']->expectOnce('callMe', ['foo', 'bar']);
  }


  public function testDoneCallbackAfterEmit() {
    $res = $this->setupTaskAndCallbacks();
    $res['task']->emit('done', ['foo', 'bar']);

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectNever('callMe');
    $res['callback_failed']->expectNever('callMe');
  }


  public function testDoneCallbackAfterBefore() {
    $res = $this->setupTaskAndCallbacks();

    $res['task']->done([$res['callback_done'], 'callme']);
    $res['task']->success([$res['callback_success'], 'callme']);
    $res['task']->failed([$res['callback_failed'], 'callme']);

    $res['task']->emit('done', ['foo', 'bar']);

    $res['callback_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_success']->expectNever('callMe');
    $res['callback_failed']->expectNever('callMe');
  }
}
?>
