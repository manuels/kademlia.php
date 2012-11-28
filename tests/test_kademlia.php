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


  public function testBootstrap() {
    srand(0);

    $count = 5;
    $callback = &new TestCallback();

    $node_settings = [];
    for($i = 0; $i < $count; $i++) {
      $settings = $this->generateSettings($node_settings);

      if($i > 0) {
        $boot_node = $this->settings2node($node_settings[rand(0, count($node_settings)-1)]);

        $task = new Kademlia\Bootstrap($settings, new Kademlia\NodeList([$boot_node]));
        $task->enqueue()->success([$callback, 'callme']);
        print 'Bootstrap '.$i." done (".Kademlia\Node::binId2hex($settings->own_node_id).")\n";
      }
      else
        $settings->own_node_id = Kademlia\Node::randomNodeId(); #Kademlia\Node::hexId2bin(str_repeat('00', N/8));
      $node_settings[$i] = $settings;
    }


    foreach($node_settings as $i => $s) {
      print Kademlia\Node::binId2hex($s->own_node_id).': '. ($s->kbuckets->toNodeList()->size())."\n";
      foreach($s->kbuckets->toNodeList()->toArray() as $n)
        print $n->idStr()."\n";
      print "\n";
    }

    $callback->expectCallCount('callme', $count-1);
    return $node_settings;
  }

}

?>
