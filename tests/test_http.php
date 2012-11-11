<?php

class TestKademliaHttp extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia HTTP Test');

    return; #############################

    $this->node = KademliaTestFactory::constructNode([
      'protocols' => [
        Kademlia\Http::protocol_id => [
          'protocol' => 'http',
          'host' => '10.0.0.1',
          'port' => 8080,
          'path' => '/kad/kademlia'
        ]
    ]]);
  }

  public function testUrlEncode() {
    return; #############################
    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);

    $data = [ 'a' => [ 'b' => 'c' ] ];

    $prot_info = $this->node->protocolInfo(Kademlia\Http::protocol_id);
    $true_url = $http->urlEncode($prot_info, $data);

    #$expected_url = 'http://10.0.0.1:8080/kad/kademlia?q='.urlencode(json_encode($data));
    $expected_url = 'http://10.0.0.1:8080/kad/kademlia?q=%7B%22a%22%3A%7B%22b%22%3A%22c%22%7D%7D';
    $this->assertEqual($true_url, $expected_url);
  }


  public function testSendPingRequest() {
    return; #############################

    $settings = new Kademlia\Settings;
    $mock_http = &new MockHttp($settings);
    $mock_http->returns('multiDownload', new TestTaskGroup($settings));

    $mock_http->sendPingRequest($this->node);

#    $data = [];
#    $urls = [ 'http://10.0.0.1:8080/kad/kademlia?q='.urlencode(json_encode($data)) ];
    $urls = [ 'http://10.0.0.1:8080/kad/kademlia?q=%5B%5D' ];
    $mock_http->expectOnce('multiDownload', [$urls, Kademlia\HttpMultiDownload::PING]);
  }


  public function testPingResponse() {
    return; #############################

    $request = [ 'type' => 'PING' ];

    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);

    $response = $http->processRequest(['q' => json_encode($request)]);

    $this->assertEqual($response, json_encode(['type' => 'PONG']));
  }


  public function testStoreResponseSucceedsForValidParameters() {
    return; #############################

    $id = str_repeat('00', N/8);
    $value = 'foobar';

    $request = [
      'type' => 'STORE',
      'key_id' => $id,
      'value' => base64_encode($value)
    ];

    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);

    $response = $http->processRequest(['q' => json_encode($request)]);

    $this->assertEqual($response, json_encode(['type' => 'STORED']));
    $this->assertEqual($settings->value_storage[$id], [$value]);
  }


  public function testStoreResponseFailsForInvalidKeyId() {
    return; #############################

    $request = [
      'type' => 'STORE',
      'key_id' => 'invalid',
      'value' => base64_encode('foobar')
    ];

    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);

    $response = $http->processRequest(['q' => json_encode($request)]);

    $this->assertFalse(isset(json_decode($response,true)['STORED']));
    $this->assertTrue(empty($settings->value_storage));
  }


  public function testStoreResponseFailsForInvalidValue() {
    return; #############################

    $request = [
      'type' => 'STORE',
      'key_id' => str_repeat('00', N/8),
      'value' => 'foo\nbar'
    ];

    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);

    $response = $http->processRequest(['q' => json_encode($request)]);

    $this->assertFalse(isset(json_decode($response,true)['type']));
    $this->assertTrue(empty($settings->value_storage));
  }


  public function testMultiDownload() {
    return; #############################

    print "testMultiDownload is disabled\n";
    return;
    $urls = [
      'https://encrypted.google.com/#q=foo',
      'http://yahoo.com/thisfiledoesntexistsoitwill404.php', // 404
      'http://www.google.com/#q=foo'
    ];

    $callback = new TestCallback();

    $settings = new Kademlia\Settings;
    $http = new Kademlia\Http($settings);
    $result = $http->multiDownload($urls, Kademlia\HttpMultiDownload::PING)->enqueue()->done([$callback, 'callme']);

    $callback->expectOnce('callme', [$urls]);
  }
}

?>
