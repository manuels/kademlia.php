<?php

namespace Kademlia;

class TaskGroup extends Task {
  private $tasks = [];

  private $subresults = [
    'done' => [],
    'failed' => [],
    'success' => []
  ];

  function __construct(&$settings) {
    parent::__construct($settings);
  }

  public function add($task) {
    assert($task !== $this);
    $task->done([$this, 'subtaskIsDone']);
    $task->failed([$this, 'subtaskFailed']);
    $task->success([$this, 'subtaskSucceeded']);
    array_push($this->tasks, $task);
  }


  public function enqueueSelf() {
    foreach($this->tasks as $task)
      $task->enqueue();
    return $this;
  }


  public function subtaskIsDone() {
    $args = func_get_args();
    array_push($this->subresults['done'], $args);
#    $this->emit('eachDone', $args);
    $eachDone_args = $args;
    array_unshift($eachDone_args, 'eachDone');
    call_user_func_array([$this, 'emit'], $eachDone_args);

    if(count($this->subresults['done']) === count($this->tasks)) {
#      $this->emit('allDone', $this->subresults['done']);
      call_user_func_array([$this, 'emit'], ['allDone', $this->subresults['done']]);
    }
  }


  public function subtaskFailed() {
    $args = func_get_args();
    array_push($this->subresults['failed'], $args);
#    $this->emit('eachFailed', $args);
    $eachFailed_args = $args;
    array_unshift($eachFailed_args, 'eachFailed');
    call_user_func_array([$this, 'emit'], $eachFailed_args);

    if(count($this->subresults['failed']) === count($this->tasks)) {
#      $this->emit('allFailed', $this->subresults['failed']);
      call_user_func_array([$this, 'emit'], ['allFailed', $this->subresults['failed']]);
    }
  }


  public function subtaskSucceeded() {
    $args = func_get_args();
    array_push($this->subresults['success'], $args);
#    $this->emit('eachSuccess', $args);
    $eachSuccess_args = $args;
    array_unshift($eachSuccess_args, 'eachSuccess');
    call_user_func_array([$this, 'emit'], $eachSuccess_args);

    if(count($this->subresults['success']) === count($this->tasks)) {
      #$this->emit('allSucceeded', $this->subresults['success']);
      call_user_func_array([$this, 'emit'], ['allSucceeded', $this->subresults['success']]);
    }
  }


  public function allDone($callback) {
    $this->registerCallback('allDone', $callback);
  }


  public function allFailed($callback) {
    $this->registerCallback('allFailed', $callback);
  }


  public function allSucceeded($callback) {
    $this->registerCallback('allSucceeded', $callback);
  }


  public function eachDone($callback) {
    $this->registerCallback('eachDone', $callback);
  }


  public function eachFailed($callback) {
    $this->registerCallback('eachFailed', $callback);
  }


  public function eachSucceeded($callback) {
    $this->registerCallback('eachFuccess', $callback);
  }
}

?>
