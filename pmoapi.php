<?php
/**
 * pmoAPI <https://github.com/DanielKrasny/pmoAPI>
 * @version 1.0
 * @author Daniel Krásný <https://github.com/DanielKrasny>
 * 
 * Working with: Povodí Moravy
 * For other 'Povodí' pages use PovodiAPI <https://github.com/DanielKrasny/PovodiAPI>
 * 
 * Required values:
 * channel - Available: nadrze, sap, srazky
 * station - number of weather station (available from https://raw.githubusercontent.com/DanielKrasny/pmoAPI/master/stations/[nadrze/sap/srazky].txt)
 * response - method of responding. Available: json, rss
 * [for channels 'nadrze', 'sap'] values - do you want the latest value or all values available? Choose from: all, latest
 * Optional values:
 * [for channel 'srazky'] values - do you want only total value, all values available or the latest and total value? Choose from: total (default), all, latest
 * [for channel 'srazky', RSS response and set "values" to all] temp - allow showing temperature in RSS. Available: yes, no (default)
 * 
 */

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
$channel = $_GET["channel"];
if ($channel != 'nadrze' && $channel != 'sap' && $channel != 'srazky'){
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Invalid channel. Allowed channels are nadrze, sap, srazky.', 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'));
} else {
$station = $_GET["station"];
if ($station == null or '') {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Invalid station number. Please check https://raw.githubusercontent.com/DanielKrasny/pmoAPI/master/stations/'.$channel.'.txt', 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'));
} else if (get_headers('http://www.pmo.cz/portal/'.$channel.'/cz/mereni_'.$station.'.htm')[0] == 'HTTP/1.1 404 Not Found') {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Invalid station number. Please check https://raw.githubusercontent.com/DanielKrasny/pmoAPI/master/stations/'.$channel.'.txt', 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'));
} else {
$response = $_GET["response"];
$values = $_GET["values"];
if ($values != 'all' && $values != 'latest' && $channel != 'srazky'){
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Invalid value. Available options: all, latest', 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'));
}
$ppage = file_get_contents('http://www.pmo.cz/portal/'.$channel.'/cz/mereni_'.$station.'.htm');
$pod = '<meta charset="utf-8">'."\n".str_replace('&nbsp;', '', $ppage);
$dom = new DOMDocument;
@$dom->loadHTML($pod);
$tables = $dom->getElementsByTagName('table');
if ($channel == 'nadrze') {
    $data = $tables->item(5)->getElementsByTagName('tr');
    $infos = $tables->item(1)->getElementsByTagName('tr');
    $td[0] = $infos[1]->getElementsByTagName('table')->item(0)->getElementsByTagName('font')[0];
    $td[1] = $infos[1]->getElementsByTagName('table')->item(0)->getElementsByTagName('font')[2];
} else if ($channel == 'sap') {
    $data = $tables->item(6)->getElementsByTagName('tr');
    $infos = $tables->item(1)->getElementsByTagName('tr');
    $td[0] = $infos[0]->getElementsByTagName('table')->item(1)->getElementsByTagName('td')->item(1)->getElementsByTagName('font')[1];
    $td[1] = $infos[0]->getElementsByTagName('table')->item(1)->getElementsByTagName('td')->item(2)->getElementsByTagName('font')[1];
} else if ($channel == 'srazky') {
    $data = $tables->item(3)->getElementsByTagName('tr');
    $infos = $tables->item(1)->getElementsByTagName('tr');
    $td[0] = $infos[0]->getElementsByTagName('table')->item(0)->getElementsByTagName('td')->item(0)->getElementsByTagName('font')[0];
    $td[1] = $infos[0]->getElementsByTagName('table')->item(0)->getElementsByTagName('td')->item(2)->getElementsByTagName('font')[0];
}
if ($response == 'json') {
    $arr = array();
    if ($channel == 'srazky'){
    $minmax = array();
    $totalrain = array();
    $temp = $_GET["temp"];
    }
} else if ($response == 'rss') {
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";
    echo "<rss version='2.0'>\n";
    echo "<channel>\n";
    echo "<title>Povodí Moravy</title>\n";
    echo "<link>http://www.pmo.cz/</link>\n";
    echo "<description>Aktuální data z meteorologických stanic. RSS vytvořil skript pmoAPI od @DanielKrasny.</description>\n";
    echo "<language>cs-cz</language>\n";
    echo "<item>\n";
    echo "<title>Data z meteorologické stanice ".$td[0]->nodeValue." na toku ".$td[1]->nodeValue."</title>\n";
    echo "<link>http://www.pmo.cz/</link>\n";
    echo "<description></description>\n";
    echo "</item>\n";
}
foreach ($data as $i => $string) {
    if ($i > 0) {
        $cols = $string->getElementsByTagName('td');
        if ($channel == 'srazky') {
        if ($response == 'json') {
                if ($values == 'all') {
                $arr[] = array(
                    'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                    'rain' => $cols[1]->nodeValue.' mm/hod',
                    'temperature' => $cols[2]->nodeValue.' °C'
                );
            } else if ($values == 'latest' && $i == 1) {
                $arr[] = array(
                    'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                    'rain' => $cols[1]->nodeValue.' mm/hod',
                    'temperature' => $cols[2]->nodeValue.' °C'
                );
            }
                $totalrain[] = $cols[1]->nodeValue;
                $minmax[] = $cols[2]->nodeValue;    
            } else if ($response == 'rss') {
                if ($values == 'all') {
                echo "<item>\n";
                if ($temp == 'yes') {
                    echo "<title>Srážky z ".$cols[0]->nodeValue." byly ".$cols[1]->nodeValue." mm/h, teplota ".$cols[2]->nodeValue."°C</title>\n";
                } else {
                    echo "<title>Srážky z ".$cols[0]->nodeValue." byly ".$cols[1]->nodeValue." mm/h</title>\n";
                }
                echo "<link>http://www.pmo.cz/</link>\n";
                echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
                echo "</item>\n";
                } else if ($values == 'latest' && $i == 1) {
                    echo "<item>\n";
                    echo "<title>Aktuální informace o srážkách z ".$cols[0]->nodeValue." jsou ".$cols[1]->nodeValue." mm/h, byla naměřena teplota ".$cols[2]->nodeValue."°C.</title>\n";
                    echo "<link>http://www.pmo.cz/</link>\n";
                    echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
                    echo "</item>\n";
                }
    }
} else if ($channel == 'sap') {
    if ($i > 1){
    if ($response == 'json') {
            if($values == 'all'){
            $arr[] = array(
                'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                'water-status' => $cols[1]->nodeValue.' cm',
                'flow' => $cols[2]->nodeValue.' m³.s¯¹'
            );
        } else if ($values == 'latest' && $i == 1) {
            $arr[] = array(
                'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                'water-status' => $cols[1]->nodeValue.' cm',
                'flow' => $cols[2]->nodeValue.' m³.s¯¹'
            );
        }
    } else if ($response == 'rss') {
            if ($values == 'all') {
            echo "<item>\n";
            echo "<title>Hladina vody z ".$cols[0]->nodeValue." byla ".$cols[1]->nodeValue." cm, průtok ".$cols[2]->nodeValue." m³.s¯¹.</title>\n";
            echo "<link>http://www.pmo.cz/</link>\n";
            echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
            echo "</item>\n";
            } else if ($values == 'latest' && $i == 1) {
                echo "<item>\n";
                echo "<title>Aktuální informace o hladině vody z ".$cols[0]->nodeValue.": ".$cols[1]->nodeValue." cm, průtok ".$cols[2]->nodeValue." m³.s¯¹.</title>\n";
                echo "<link>http://www.pmo.cz/</link>\n";
                echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
                echo "</item>\n";
            }
    }
}
} else if ($channel == 'nadrze') {
    if ($response == 'json') {
            if($values == 'all'){
            $arr[] = array(
                'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                'surface' => $cols[1]->nodeValue.' m n. m.',
                'outflow-rate' => $cols[2]->nodeValue.' m³.s¯¹'
            );
        } else if ($values == 'latest' && $i == 1) {
            $arr[] = array(
                'date' => strtotime(str_replace(date('.y'), date('.Y'), $cols[0]->nodeValue)),
                'surface' => $cols[1]->nodeValue.' m n. m.',
                'outflow-rate' => $cols[2]->nodeValue.' m³.s¯¹'
            );
        }
    } else if ($response == 'rss') {
            if ($values == 'all') {
            echo "<item>\n";
            echo "<title>Hladina vody z ".$cols[0]->nodeValue." byla ".$cols[1]->nodeValue." m n. m., odtok ".$cols[2]->nodeValue." m³.s¯¹.</title>\n";
            echo "<link>http://www.pmo.cz/</link>\n";
            echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
            echo "</item>\n";
            } else if ($values == 'latest' && $i == 1) {
                echo "<item>\n";
                echo "<title>Aktuální informace o hladině vody z ".$cols[0]->nodeValue.": ".$cols[1]->nodeValue." m n. m., odtok ".$cols[2]->nodeValue." m³.s¯¹.</title>\n";
                echo "<link>http://www.pmo.cz/</link>\n";
                echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
                echo "</item>\n";
            }
    }
}
}
}
}
}
if ($response == 'rss') {
    if ($channel == 'srazky') {
    echo "<item>\n";
    echo "<title>Úhrn srážek za posledních 24 hodin je ".array_sum($totalrain).". Minimální teplota byla ".min($minmax)."°C a maximální teplota byla ".max($minmax)."°C.</title>\n";
    echo "<link>http://www.pmo.cz/</link>\n";
    echo "<description>Data z meteorologické stanice ".$td[0]->nodeValue."</description>\n";
    echo "</item>\n";
    }
    echo "</channel>\n";
    echo "</rss>\n";
}
if ($response == 'json') { 
    if ($channel == 'srazky'){
        $arr[] = array('totalrain' => array_sum($totalrain).' mm', 'minimal-temperature' => min($minmax).' °C', 'maximum-temperature' => max($minmax).' °C'); 
    }
    print(json_encode(array('success' => true, 'info' => array('source' => 'Povodí Moravy', 'station' => $td[0]->nodeValue, 'watercourse' => $td[1]->nodeValue, 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'), 'data' => $arr)));
}
if ($response != 'json' && $response != 'rss') {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Response method is not valid. Allowed methods are json, rss.', 'thanks-to' => 'pmoAPI by DanielKrasny', 'script-link' => 'https://github.com/DanielKrasny/pmoAPI'));
}
?>
