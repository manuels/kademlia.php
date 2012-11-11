<?php

class TestTaskGroup extends Kademlia\TaskGroup {
  public function enqueueSelf() {
    return enqueueSelfPrototype();
  }
}

class TestKademliaTaskGroup extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia TaskGroup Test');
  }


  private function setupTaskAndCallbacks() {
    $settings = new Kademlia\Settings;
    $settings->queue_system = 'sync';

    $task_group = &new TestTaskGroup($settings);
    $tasks = [];
    for($i = 0; $i < 10; $i++) {
      $tasks[$i] = &new TestTask($settings);
      $task_group->add($tasks[$i]);
    }

    $callback_all_done = &new TestCallback();
    $callback_all_succeeded = &new TestCallback();
    $callback_all_failed = &new TestCallback();

    return [
      'tasks' => $tasks,
      'task_group' => $task_group,
      'callback_all_done' => &$callback_all_done,
      'callback_all_succeeded' => &$callback_all_succeeded,
      'callback_all_failed' => &$callback_all_failed
    ];
  }


  public function testSuccessCallbackAfterAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    foreach($res['tasks'] as &$task) {
      $task->emit('success', ['foo', 'bar']);
    }
    
    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_succeeded']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectNever('callMe');
  }


  public function testSuccessCallbackBeforeAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    foreach($res['tasks'] as $task) {
      $task->emit('success', ['foo', 'bar']);
    }
    
    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_succeeded']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectNever('callMe');
  }


  public function testFailedCallbackAfterAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    foreach($res['tasks'] as $task) {
      $task->emit('failed', ['foo', 'bar']);
    }
    
    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_succeeded']->expectNever('callMe');
  }


  public function testFailedCallbackBeforeAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    foreach($res['tasks'] as $task) {
      $task->emit('failed', ['foo', 'bar']);
    }
    
    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_succeeded']->expectNever('callMe');
  }


  public function testDoneCallbackAfterAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    foreach($res['tasks'] as $task) {
      $task->emit('done', ['foo', 'bar']);
    }
    
    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectNever('callMe');
    $res['callback_all_succeeded']->expectNever('callMe');
  }


  public function testDoneCallbackBeforeAllEmit() {
    $res = $this->setupTaskAndCallbacks();

    $res['task_group']->allDone([$res['callback_all_done'], 'callme']);
    $res['task_group']->allSucceeded([$res['callback_all_succeeded'], 'callme']);
    $res['task_group']->allFailed([$res['callback_all_failed'], 'callme']);

    foreach($res['tasks'] as $task) {
      $task->emit('done', ['foo', 'bar']);
    }
    
    $res['callback_all_done']->expectOnce('callMe', ['foo', 'bar']);
    $res['callback_all_failed']->expectNever('callMe');
    $res['callback_all_succeeded']->expectNever('callMe');
  }
}
?>
