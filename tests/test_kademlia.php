<?php

class TestKademliaNetwork extends UnitTestCase {
  function __construct() {
  }


  private function generateSettings(&$all_settings) {
    $settings = new Kademlia\Settings;
    $settings->supported_protocols = [
      Kademlia\MockHttp\protocol_id => [
        'protocol' => 'http',
        'host' => '127.0.0.'.rand(0,254),
        'port' => rand(1,65535),
        'path' => '/kademlia',
        'all_settings' => &$all_settings
      ]
    ];
    return $settings;
  }


  private function settings2node($settings) {
    $node = new Kademlia\Node([
      'id' => $settings->own_node_id,
      'protocols' => $settings->supported_protocols
      ]);
    return $node;
  }


  public function testBootstrap($count = 8) {
    #print "skipping testBootstrap\n";
    #return;
    srand(0);

    $callback = &new TestCallback();

    $node_settings = [];
    for($i = 0; $i < $count; $i++) {
      $settings = $this->generateSettings($node_settings);

      if($i > 0) {
        $idx = rand(0, count($node_settings)-1);
        $boot_node = $this->settings2node($node_settings[$idx]);

        $task = new Kademlia\Bootstrap($settings, new Kademlia\NodeList([$boot_node]));
        $task->enqueue()->success([$callback, 'callme']);
        #print 'Bootstrap '.$i." done (".Kademlia\Node::binId2hex($settings->own_node_id)." using ".$boot_node->idStr().")\n";
      }
      else
        $settings->own_node_id = Kademlia\Node::randomNodeId();

      $node_settings[$i] = $settings;

      #foreach($node_settings as $j => $s) {
      #  print Kademlia\Node::binId2hex($s->own_node_id).': '. ($s->kbuckets->toNodeList()->size())."\n";
      #  foreach($s->kbuckets->toNodeList()->toArray() as $n) {
      #    print $n->idStr()."\n";
      #    assert(count($n->data['protocols']) > 0);
      #  }
      #  print "\n";
      #}
      #print "\n";
    }

    $callback->expectCallCount('callme', $count-1);
    return $node_settings;
  }


  public function testStoreAndFindValue() {
    $callback = &new TestCallback();
    $node_settings = $this->testBootstrap(5);

    # store from node 0
    $key_id = Kademlia\Node::randomNodeId();
    $value = 'foobar';
    $expire = 10;
    $task = new Kademlia\Store($node_settings[0], $key_id, $value, $expire);
    $task->enqueue()->success([$callback, 'callme']);
    $callback->expectCallCount('callme', 1);

    # find_value from node 4
    $cb = function($values) {
      $this->assertEqual($values, ['foobar']);
    };

    $task = new Kademlia\FindValue($node_settings[4], $key_id);
    $task->enqueue()->success([$callback, 'callme'])->success($cb);
    $callback->expectCallCount('callme', 1);
  }
}

?>
