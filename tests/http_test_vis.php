<html>

<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/2.10.0/d3.v2.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.2/underscore-min.js"></script>


<script>
data = 
<?php
$path = '/var/www/kademlia.php/tests/tmp/';
$str='[';
foreach(scandir($path) as $file) {
  if(($file === '.') or ($file === '..'))
    continue;
  $data = json_decode(file_get_contents($path.$file), true);
  $str .= json_encode($data).",\n";
}
$str.=']';
echo $str;
?>
;
data = _(data).sortBy(function(x) {return x? x.own_node_id : ""});
</script>
</head>

<body>
<div id="vis"></div>
</body>

<script>

function hilightConnected(d, i) {
  var id;
  d3.selectAll('circle').style("fill", "white");

  id = d.own_node_id;
  d3.select('circle#_'+id).style("fill", "green");

  for(i in d.kbuckets) {
    id = d.kbuckets[i].id;
    if(!id)
      continue;
    d3.select('circle#_'+id).style("fill", "red");
  }
}

var vis = d3.select("#vis")
    .append("svg")
    .attr("width", 1000)
    .attr("height", 1000);

vis.selectAll("circle")
    .data(data)
    .enter().append("circle")
    .style("stroke", "gray")
    .style("fill", "white")
//    .attr("r", function(d, i){return 5+Math.log(d ? d.kbuckets.length : 1);})
    .attr("r", function(d, i){return 5+(d ? d.kbuckets.length : 1)/10;})
    .attr("cx", function(d, i){return 5+(i%50)*20})
    .attr("cy", function(d, i){return 5+Math.floor(i/50)*20})
    .attr("id", function(d, i){ return "_"+(d ? d.own_node_id : ""); })
    .on("mouseover", hilightConnected)
    .append("svg:title")
    .text(function(d,i) { return d ? d.own_node_id+" : "+d.supported_protocols[80].url : ""; })
/*    .data(function(d,i) { return d['kbuckets']; })
    .enter()
    .append('line')
    .x(*/
</script>

</html>
