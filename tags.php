<h1>TinyWebDB API and Log Tail</h1>
<h2> <a href=index.php>HOME</a>  <a href=tags.php>TAGS</a></h2>
<form method="POST" action="">
<?php
setlocale(LC_TIME, "ja_JP");
date_default_timezone_set('Asia/Tokyo');
$listLog = array();
$listTxt = array();
if ($handler = opendir("./")) {
    while (($sub = readdir($handler)) !== FALSE) {
        if (substr($sub, -4, 4) == ".txt") {
            $listTxt[] = $sub;
        } elseif (substr($sub, 0, 10) == "tinywebdb_") {
            $listLog[] = $sub;
        }
    }
    closedir($handler);
}

echo "<h3>TinyWebDB Tags</h3>";
echo "<table border=1>";
echo "<thead><tr>";
echo "<th> </th>";
echo "<th> Tag Name </th>";
echo "<th> Size </th>";
echo "<th> Ver </th>";
echo "<th> localIP </th>";
echo "<th> Temp </th>";
echo "<th> Pres </th>";
echo "<th> battery_Vcc </th>";
echo "<th> localTime </th>";
echo "</tr></thead>\n";
if ($listTxt) {
    $now = time();
    sort($listTxt);
    foreach ($listTxt as $sub) {
	$tagValue = file_get_contents($sub);
        $obj = json_decode($tagValue);
	$tim_stmp = $obj->{'localTime'} - 9*3600;
        if(($now-$tim_stmp) > 600){
            echo "<tr bgcolor=#AAAAAA>";
        } else {
            echo "<tr>";
        }
        echo "<td> <input type=checkbox name='tagList[]' value=" . substr($sub, 0, -4) . "></td>\n";
        echo "<td><a href=tags.php?tag=" . substr($sub, 0, -4) . ">" .substr($sub, 0, -4) . "</a></td>\n";
        echo "<td>" . filesize("./" . $sub) . "</td>\n";
	$tagValue = file_get_contents($sub);
	$obj = json_decode($tagValue);
	echo "<td>" . $obj->{'Ver'} . "</td>\n";
        echo "<td>" . $obj->{'localIP'} . "</td>\n";
        echo "<td>" . $obj->{'temperature'} . "</td>\n";
        echo "<td>" . $obj->{'pressure_hpa'} . "</td>\n";
        echo "<td>" . $obj->{'battery_Vcc'} . "</td>\n";
        echo "<td>" . strftime("%D %T", (int)$tim_stmp) . "</td>\n";
        echo "</tr>";
    }
}
echo "</table>";
echo "<input type=submit value=submit>";
echo "</form>";

if (isset($_GET['tag'])) {
    $tagName = $_GET['tag'];
    echo "<h2>tagName : " . $tagName . "</h2>";
    $tagValue = file_get_contents($tagName . ".txt");
    $obj = json_decode($tagValue);
    $clientList = $obj->{'clientList'};
    foreach ($clientList as $mac => $rssi ) {
	echo $mac . " -> " . $rssi . "<br>";
    }
}

if (isset($_POST['tagList'])) {
    $clientMix = array();
    echo "<h3>TinyWebDB Tags</h3>";
    echo "<table border=1>";
    echo "<thead><tr>";
    echo "count = " . count($clientMix) . "<br>";
    foreach($_POST['tagList'] as $tagName) {
	echo "<th> ";
	echo $tagName . "<br>";
	echo "</th>";
	$tagValue = file_get_contents($tagName . ".txt");
    	$obj = json_decode($tagValue);
    	$clientList = $obj->{'clientList'};
	array_merge($clientMix, $clientList);
    echo "count = " . count($clientList, COUNT_RECURSIVE) . "<br>";
    foreach ($clientList as $mac => $rssi ) {
        echo $mac . " -> " . $rssi . "<br>";
    }

	$objList[$tagName] = $obj;
    }
    echo "</tr> ";
    echo "<tr> ";
    foreach($_POST['tagList'] as $tagName) {
        echo "<td>" . $objList[$tagName]->{'localTime'} . "</td>\n";
    }
    echo "</tr></thead>\n";
    echo "</table>";
    foreach ($clientMix as $mac => $rssi ) {
	echo $mac . " -> " . $rssi . "<br>";
    }
}
