<?php
$c = curl_init();
curl_setopt($c,CURLOPT_URL,'http://espn.go.com/olympics/summer/2012/medals');
curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
$content = curl_exec($c);

// getting the medal counts for the top 3
$topThreeGold = array();
$topThreeTotal = array();
$data = array();
$countries = array();
preg_match_all("/<li class=\"gold\"\s\w+=[\"|\'][\w|\d|\.|;|:]+[\"|\']>(\d+)<\/li>/",$content,$topThreeGold);
preg_match_all("/<li class=\"total\">(\d+)<\/li>/",$content,$topThreeTotal);
preg_match_all("/country\/\d+\">([\w|\s]+)<\/a>/",$content,$countries);
for($i=0;$i<3;$i++){
    if(!isset($data[$countries[1][$i]])){ 
        $data[$countries[1][$i]]['gold'] = (int)$topThreeGold[1][$i];
        $data[$countries[1][$i]]['total'] = (int)$topThreeTotal[1][$i];
    }
}

// getting the medal counts for everyone else
$fourthPlaceGold= array();
$fourthPlaceTotal = array();
preg_match_all("/<td class=\"gold\">(\d+)<\/td>/",$content,$fourthPlaceGold);
preg_match_all("/<td class=\"total bold\">(\d+)<\/td>/",$content,$fourthPlaceTotal);
$fourthPlaceCount = count($fourthPlaceGold[1]);
for($i=0;$i<7;$i++){
    // Unlike the medal count, the countries are pulled at once, so we have to offset
    $offset = $i + 3;    
    if(!isset($data[$countries[1][$offset]])){
        $data[$countries[1][$offset]]['gold'] = (int)$fourthPlaceGold[1][$i];
        $data[$countries[1][$offset]]['total'] = (int)$fourthPlaceTotal[1][$i];
    }
}
$jsOutput = "['Country','Gold Medals','Total Medals'],\n";
foreach($data as $key=>$value){
    $jsOutput .= "['".$key."'," . $value['gold'] . "," . $value['total'] . "],\n";
}
?>
<html>
<head>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization","1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
        <?php print substr($jsOutput,0,-2);?>
        ]);
  
        var options = {
            title: '2012 Olympic Medal Breakdown',
            chartArea: {left:150,top:40,width:"70%",height:"100%"},
            vAxis: {title:'Countries',titleTextStyle: {color:'red'}}
        };

        var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
        chart.draw(data,options);
    }   
</script>
</head>
<body>
    <div id="chart_div" style="padding:0;margin:0;width:100%;height:100%;"></div>
</body>
</html>
