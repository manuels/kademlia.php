# kademlia.php
A distributed hash table for PHP based on [Kademlia](https://en.wikipedia.org/wiki/Kademlia).

Currently in alpha status

# example code

    <?php
    require('../kademlia.php');

    # setup some storage for the current configuration and kbuckets
    $filename = './tmp/kademlia-node';
    $json = "";
    if(file_exists($filename))
      $json = @file_get_contents($filename);


    # define the url to the local node
    $kad = new Kademlia\Instance($json, []);
    $kad->settings->supported_protocols = [
      80 => [
        'url' => 'http://my.superhost.com/kademlia-node.php'
      ]
    ];

    # bootstrap if necessary
    if(strlen($kad->settings->own_node_id) !== Kademlia::N/8) {
      # bootstrap
      $n = new Kademlia\Node([
        'protocols' => [80 => [ 'url' => 'http://manuels2.aries.uberspace.de/kademlia.php' ] ] # a 'supernode'
      ]);
      $nodes = new Kademlia\NodeList([$n]);
      $kad->bootstrap($nodes);
    }

    # process request
    echo $kad->processRequest(80, $_REQUEST);

    # store configuration
    file_put_contents($filename, json_encode($kad,  JSON_PRETTY_PRINT));
    @chmod($filename, 0600);
    ?>

