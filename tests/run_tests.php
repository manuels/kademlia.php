<?php

ini_set('memory_limit', '50M');

require_once(dirname(__FILE__) . '/../lib/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/simpletest/mock_objects.php');

require_once(dirname(__FILE__) . '/../kademlia.php');
require_once(dirname(__FILE__) . '/test_factory.php');

require_once(dirname(__FILE__) . '/utils.php');

const N = Kademlia\N;

Mock::generate('Kademlia\Task', 'MockTask');
Mock::generate('Kademlia\Protocol', 'MockProtocol');
Mock::generate('Kademlia\Ping', 'MockPing');
Mock::generate('Kademlia\FindNode', 'MockFindNode');
Mock::generate('Kademlia\Node', 'MockNode');

require_once(dirname(__FILE__) . '/test_node.php');
require_once(dirname(__FILE__) . '/test_node_list.php');
require_once(dirname(__FILE__) . '/test_bucket.php');
require_once(dirname(__FILE__) . '/test_kbuckets.php');
require_once(dirname(__FILE__) . '/test_task.php');

require_once(dirname(__FILE__) . '/test_ping.php');
require_once(dirname(__FILE__) . '/test_find_node.php');

####
var_dump('skipping bootstrap test');
#require_once(dirname(__FILE__) . '/test_bootstrap.php');
####
