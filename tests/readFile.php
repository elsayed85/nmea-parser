<?php

$cliArgs = getopt('f:h', ['file:', 'help']);

if (isset($cliArgs['h']) || isset($cliArgs['help'])) {
    echo
        'Usage : ./tests/readFile.php -f="<path>"'."\n"
        ."\n"
        .'Options :'."\n"
        .' -f, --file="<path>" : File to read'."\n"
        .' -h, --help          : Display this message'."\n"
    ;
    return;
}

$file = null;
if (isset($cliArgs['f'])) {
    $file = $cliArgs['f'];
} elseif (isset($cliArgs['file'])) {
    $file = $cliArgs['file'];
}

if ($file === null) {
    echo 'A file path should be passed with -f or --file parameter.'."\n";
    return;
}

if (!file_exists($file)) {
    echo 'The file '.$file.' not exist.'."\n";
    return;
}

require_once(__DIR__.'/../vendor/autoload.php');

$parser = new \Elsayed85\NMEA\Parser;
$fop    = fopen($file, 'r');

while ($line = fgets($fop)) {
    echo '$line : '.$line."\n";
    
    try {
        $frame = $parser->readLine($line);
    } catch (\Exception $e) {
        echo 'EXCEPTION : ['.$e->getCode().'] '.$e->getMessage()."\n";
        echo '---------------------------------------------------------------'."\n";
        continue;
    }
    
    echo $frame;
    
    $frameType = $frame->getFrameType();
    if ($frameType === 'GGA' || $frameType === 'GLL' || $frameType === 'RMC') {
        $latDeg     = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDegree(
            $frame->getLatitude(),
            $frame->getLatitudeDirection(),
            false,
            true
        );
        $latDec     = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDec(
            $frame->getLatitude()
        );
        $latGmapObj = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDegree(
            $frame->getLatitude()
        );
        
        $longDeg     = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDegree(
            $frame->getLongitude(),
            $frame->getLongitudeDirection(),
            true,
            true
        );
        $longDec     = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDec(
            $frame->getLongitude(),
            true
        );
        $longGmapObj = \Elsayed85\NMEA\Utils\Coordinates::convertGPSDataToDegree(
            $frame->getLongitude(),
            null,
            true
        );
        
        $latGmap  = $latGmapObj->degree.'° '
            .$latGmapObj->minute.'.'
            .$latGmapObj->second.'\' '
            .$frame->getLatitudeDirection();
        $longGmap = $longGmapObj->degree.'° '
            .$longGmapObj->minute.'.'
            .$longGmapObj->second.'\''
            .$frame->getLongitudeDirection();
        
        echo "\n".'Conversion coordinates : '."\n";
        echo 'Latitude : '.$latDeg.' / '.$latDec."\n";
        echo 'Longitude : '.$longDeg.' / '.$longDec."\n";
        echo 'Gmaps : '.$latGmap.' '.$longGmap."\n";
    }
    
    echo '---------------------------------------------------------------'."\n";
}

fclose($fop);
