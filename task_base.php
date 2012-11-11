<?php

namespace Kademlia;

abstract class TaskBase {
  protected $queues = [];
  protected $emitted_signals = [];

  function __construct(&$settings) {
    $this->settings = &$settings;
  }

  # 1st argument: name of static class function to call
  # other arguments: anything you want to pass to the function
  public function enqueue() {
    if(func_num_args() === 0)
      return $this->enqueueSelf();

    $args = func_get_args();
    $function = array_shift($args);

    if($this->settings->queue_system === 'sync') {
      call_user_func_array($function, $args);
    }
    else
      throw 'Queueing system unknown!';
  }


  abstract function enqueueSelf();


  public function enqueueSelfPrototype() {
    $class_name = get_class($this);
    $this->enqueue([$class_name, 'perform']);
    return $this;
  }


  # emit($type, $args...);
  public function emit() {
    $args = func_get_args();
    $type = array_shift($args);

    if(!isset($this->queues[$type]))
      $this->queues[$type] = [];

    $this->emitted_signals[$type] = $args;
    while(count($this->queues[$type]) > 0) {
      $callback = array_pop($this->queues[$type]);
      $this->enqueue($callback, $args);
    }

    if(in_array($type, ['success', 'failed'])) {
      $type = 'done';
      array_unshift($args, $type);
      call_user_func_array([$this, 'emit'], $args);
    }

    if(in_array($type, ['allSucceeded', 'allFailed'])) {
      $type = 'allDone';
      array_unshift($args, $type);
      call_user_func_array([$this, 'emit'], $args);
    }
  }


  public function signalAlreadyEmitted($type) {
    return isset($this->emitted_signals[$type]);
  }


  protected function registerCallback($type, $callback) {
    if($this->signalAlreadyEmitted($type))
      $this->enqueue($callback, $this->emitted_signals[$type]);
    else
      if(!isset($this->queues[$type]))
        $this->queues[$type] = [$callback];
      else
        array_push($this->queues[$type], $callback);
  }
}

?>
