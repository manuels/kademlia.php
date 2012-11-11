<?php

ini_set('memory_limit', '50M');
ini_set('display_errors', 'on');
#error_reporting(E_ERROR | E_PARSE | E_WARNING);
assert_options(ASSERT_BAIL, true);

require_once(dirname(__FILE__) . '/../lib/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/simpletest/mock_objects.php');

require_once(dirname(__FILE__) . '/../kademlia.php');
require_once(dirname(__FILE__) . '/test_factory.php');

require_once(dirname(__FILE__) . '/utils.php');

const N = Kademlia\N;

Mock::generate('Kademlia\Task', 'MockTask');
Mock::generate('Kademlia\Protocol', 'MockProtocol');
Mock::generatePartial('Kademlia\Http', 'MockHttp', ['multiDownload']);
Mock::generate('Kademlia\Ping', 'MockPing');
Mock::generate('Kademlia\FindNode', 'MockFindNode');
Mock::generate('Kademlia\Node', 'MockNode');
Mock::generate('Kademlia\Settings', 'MockSettings');

require_once(dirname(__FILE__) . '/test_node.php');
require_once(dirname(__FILE__) . '/test_node_list.php');
require_once(dirname(__FILE__) . '/test_bucket.php');
require_once(dirname(__FILE__) . '/test_kbuckets.php');

require_once(dirname(__FILE__) . '/test_task.php');
require_once(dirname(__FILE__) . '/test_task_group.php');

require_once(dirname(__FILE__) . '/test_protocol.php');
require_once(dirname(__FILE__) . '/test_http.php');

require_once(dirname(__FILE__) . '/test_ping.php');
require_once(dirname(__FILE__) . '/test_find_node.php');
require_once(dirname(__FILE__) . '/test_find_value.php');
require_once(dirname(__FILE__) . '/test_store.php');

require_once(dirname(__FILE__) . '/test_kademlia.php');

####
var_dump('skipping bootstrap test');
#require_once(dirname(__FILE__) . '/test_bootstrap.php');
####
