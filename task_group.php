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
    $this->emit('eachDone', $args);

    if(count($this->subresults['done']) === count($this->tasks))
      $this->emit('allDone', $args);
  }


  public function subtaskFailed() {
    $args = func_get_args();
    array_push($this->subresults['failed'], $args);
    $this->emit('eachFailed', $args);

    if(count($this->subresults['failed']) === count($this->tasks))
      $this->emit('allFailed', $args);
  }


  public function subtaskSucceeded() {
    $args = func_get_args();
    array_push($this->subresults['success'], $args);
    $this->emit('eachSuccess', $args);

    if(count($this->subresults['success']) === count($this->tasks))
      $this->emit('allSucceeded', $args);
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
