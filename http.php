<?php

namespace Kademlia\Http;

const N = \Kademlia\N;
const protocol_id = 80;

class Protocol extends \Kademlia\Protocol {
  public $protocol_id = protocol_id;

  public function updateKBuckets($node) {
    if($this->settings->verbosity >= 4) {
      print "Updating kbuckets for ".\Kademlia\Node::binId2hex($this->settings->own_node_id)." with ".$node->idStr()."\n";
      var_dump($node->data);
    }

    $this->settings->kbuckets->nodeOnline($node);
  }


  public function parseSenderNode($request) {
    try {
      $keys = ['id' => '', 'protocols', ''];
      $data = [
        'id' => $request['id'],
        'protocols' => $request['protocols']
      ];

      $node = new \Kademlia\Node($data);
      #var_dump($request);#['protocols']);
#      if(count($data['protocols']) === 0)
#        debug_print_backtrace();
#      assert(count($data['protocols']) > 0);
#      assert(count($node->data['protocols']) > 0);
    }
    catch(Exception $e) {
      $node = NULL;
    }

    return $node;
  }


  public function processRequest($req) {
    $request = json_decode($req['q'], true);

    $sender_node = $this->parseSenderNode($request);
    if($sender_node !== NULL)
      $this->updateKBuckets($sender_node);
    
    switch($request['query']['type']) {
      case 'FIND_NODE':
        $needle_id = \Kademlia\Node::hexId2bin($request['query']['node_id']);
        $response = @json_encode($this->createFindNodeResponse($needle_id, $sender_node));
        break;
      case 'FIND_VALUE':
        $key_id = \Kademlia\Node::hexId2bin($request['query']['key_id']);
        $data = $this->createFindValueResponse($key_id, $sender_node);
        $data['values'] = array_map(base64_encode, $data['values']);
        $response = @json_encode($data);
        break;
      case 'STORE':
        $key_id = \Kademlia\Node::hexId2bin($request['query']['key_id']);
        $value = base64_decode($request['query']['value']);
        $expire = $request['query']['expire'];
        $this->createStoreResponse($sender_node, $key_id, $value, $expire);

        $response = '{}';
        break;
      default:
        $response = '{}';
        break;
    }

    return $response;
  }
}


class HttpTask extends \Kademlia\Task {
  function __construct(&$settings, $recipients_node_list) {
    assert(gettype($recipients_node_list) === 'object');
    assert(get_class($recipients_node_list) === 'Kademlia\NodeList');

    parent::__construct($settings);
    $this->recipients_node_list = $recipients_node_list;
  }


  public function perform($data) {
    $urls = [];

    assert(is_string($this->settings->own_node_id));
    assert(strlen($this->settings->own_node_id) === N/8);

    foreach($this->recipients_node_list->toArray() as $node) {
      $my_protocols = $this->settings->supported_protocols;
      unset($my_protocols["-80"]['all_settings']);
      unset($my_protocols["-80"]['value_storage']);

      $str = '?q='.urlencode(@json_encode([
        'id'        => \Kademlia\Node::binId2hex($this->settings->own_node_id),
        'protocols' => $my_protocols,
        'query'     => $data
      ]));

      $url = parse_url($node->data['protocols'][80]['url']);
      $str = $url['scheme'].'://'.$url['host'].$url['path'].$str;

      $urls[$str] = $node;
    }
    $responses = $this->download($urls);

    if($this->settings->verbosity > 0) {
      print \Kademlia\Node::binId2hex($this->settings->own_node_id)." got these responses:\n";
      foreach($responses as $r)
        var_dump(json_decode($r['data'], true));
      print "# end of responses\n";
    }

    foreach($responses as $resp) {
      if($resp['data'] !== NULL)
        $this->settings->kbuckets->nodeOnline($resp['node']);
      else
        $this->settings->kbuckets->nodeOffline($resp['node']);
    }

    return $responses;
  }


  public function enqueueSelf() {
    return parent::enqueueSelfPrototype();
  }


  public function download($urls) {
    $mh = curl_multi_init();
    $results = [];

    foreach($urls as $u => $node) {
      $ch = curl_init();
      curl_setopt_array($ch, [
        CURLOPT_URL => $u,
        CURLOPT_HEADER => 0,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_RETURNTRANSFER => true
      ]); 
      curl_multi_add_handle($mh, $ch);
      $channels[$u] = $ch;
    }

    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }


    foreach($urls as $u => $node) {
      $ch = $channels[$u];
      $data = curl_multi_getcontent($ch);
      $results[$u] = ['node' => $node, 'data' => $data];
      curl_multi_remove_handle($mh, $ch);
    }

    curl_multi_close($mh);
    return $results;
  }
}



class Find extends HttpTask {
  function __construct(&$settings, $needle_id, $recipients_node_list) {
    parent::__construct($settings, $recipients_node_list);

    $n = new \Kademlia\Node(['id' => $needle_id]);
    $this->needle_id = $n->idBin();
  }


  public function enqueueSelf() {
    return parent::enqueueSelfPrototype();
  }


  public function parseNodeList($encoded_result = '{}') {
    $result = @json_decode($encoded_result, true);
    if($result === NULL)
      $result = [];

    $nodes = [];

    if(!is_array($result['nodes']))
      $result['nodes'] = [];
    
    foreach($result['nodes'] as $data) {
      $n = new \Kademlia\Node($data);
      assert(count($data['protocols']) > 0);

      if(count($n->data['protocols']) === 0) {
        print_r($result['nodes']);
        assert(false);
      }

      if($n->isValid())
        array_push($nodes, $n);
    }
  
    return new \Kademlia\NodeList($nodes);
  }
}



class FindNode extends Find {
  public function perform() {
    $data = [
      'type' => 'FIND_NODE',
      'node_id' => \Kademlia\Node::binId2hex($this->needle_id)
    ];

    $results = parent::perform($data);

    $node_list = new \Kademlia\NodeList;
    foreach($results as $res) {
      $nodes = $this->parseNodeList($res['data']);
      $node_list->addNodeList($nodes);
    }

    $emitted_result = [ 'node_list' => $node_list ];

    if($this->settings->verbosity >= 4) {
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": FindNode process found this result\n";
      var_dump($emitted_result['node_list']);
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": end of FindNode process result\n";
    }

    $this->emit('done', $emitted_result);
  }
}






class FindValue extends Find {
  public function parseValues($json) {
    $values = [];
    $data = json_decode($json, true);

    if(!isset($data['values']))
      $data['values'] = [];
 
    foreach($data['values'] as $v) {
      array_push($values, base64_decode($v));
    }
    return $values;
  }


  public function perform() {
    $data = [
      'type' => 'FIND_VALUE',
      'key_id' => \Kademlia\Node::binId2hex($this->needle_id)
    ];

    $results = parent::perform($data);

    $node_list = new \Kademlia\NodeList;
    $values = [];
    foreach($results as $res) {
      $nodes = $this->parseNodeList($res['data']);
      $values = array_merge($values, $this->parseValues($res['data']));
      $node_list->addNodeList($nodes);
    }

    $emitted_result = [ 'node_list' => $node_list, 'values' => $values ];

    if($this->settings->verbosity >= 4) {
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": FindValue process found these nodes\n";
      var_dump($emitted_result['node_list']);
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": end of FindNode process found nodes\n";
    }

    $type = 'done';
    if(count($values) > 0)
      $type = 'success';

    $this->emit($type, $emitted_result);
  }
}



class Store extends HttpTask {
  function __construct(&$settings, $key_id, $value, $expire, $recipients_node_list) {
    $this->key_id = $key_id;
    $this->value = $value;
    $this->expire = $expire;

    parent::__construct($settings, $recipients_node_list);
  }

  public function enqueueSelf() {
    return parent::enqueueSelfPrototype();
  }

  public function perform() {
    $data = [
      'type'   => 'STORE',
      'key_id' => \Kademlia\Node::binId2hex($this->key_id),
      'value'  => base64_encode($this->value),
      'expire'  => $this->expire,
    ];

    $results = parent::perform($data);

    $stored = false;
    foreach($results as $r) {
      if($r['data'] === "{}") {
        $stored = true;
        break;
      }
    }

    if($stored)
      $this->emit('success');
    else
      $this->emit('failed');
  }
}

?>
