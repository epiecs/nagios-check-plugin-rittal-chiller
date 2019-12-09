<?php

$chillerIp     = "192.168.0.1";
$communityString = "public";

$chillerVars = [
    'uptime'             => '1.3.6.1.2.1.1.3.0',
    'hotAirTemp'         => '1.3.6.1.4.1.9839.2.1.2.21.0',
    'coldAirTemp'        => '1.3.6.1.4.1.9839.2.1.2.22.0',
    'fanSpeedPercentage' => '1.3.6.1.4.1.9839.2.1.3.28.0',
    'fanspeedRpm'        => '1.3.6.1.4.1.9839.2.1.3.29.0'
];


foreach($chillerVars as $valueName => $valueOid)
{
    $value = snmp2_get($chillerIp, $communityString, $valueOid);
    echo "{$valueName} == \t\t {$value}\n";
}
