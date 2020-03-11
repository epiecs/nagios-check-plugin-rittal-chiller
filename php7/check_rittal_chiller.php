#!/usr/bin/php
<?php

/**
 * You can test with
 *
 * php php7/check_rittal_chiller.php -H 10.1.31.193 -C coldairtemp -W 30 -M 45
 * php php7/check_rittal_chiller.php -H 10.1.31.193 -C hotairtemp -W 30 -M 45
 * php php7/check_rittal_chiller.php -H 10.1.31.193 -C fanspeedrpm -W 2200 -M 3300
 * php php7/check_rittal_chiller.php -H 10.1.31.193 -C fanspeedpercentage -W 60 -M 90
 *
 * @var checkRittalChiller
 */

$checkRittalChiller = new checkRittalChiller();

$checkRittalChiller->check(getopt("hC:H:W:M:", ["help"]));

class checkRittalChiller
{
    const STATE_OK       = 0;
    const STATE_WARNING  = 1;
    const STATE_CRITICAL = 2;
    const STATE_UNKNOWN  = 3;

    private $communityString = 'public';

    private $oid = [
        'webinterface'       => '1.3.6.1.4.1.2021.2.1.100.2',
        'hotairtemp'         => '1.3.6.1.4.1.9839.2.1.2.21.0',
        'coldairtemp'        => '1.3.6.1.4.1.9839.2.1.2.22.0',
        'fanspeedpercentage' => '1.3.6.1.4.1.9839.2.1.3.28.0',
        'fanspeedrpm'        => '1.3.6.1.4.1.9839.2.1.3.29.0'
    ];

    function __construct()
    {
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    }

    /**
     * Entry function that switches to the correct subfunction depending on the device and check
     *
     * @param  array $options array containing cli options
     */

    public function check(array $options) :void
    {
        if(isset($options['h']) || isset($options['help']))
        {
            $this->help();
        }

        $check  = strtolower($options['C']);

        try {
            $snmpValue = snmp2_get($options['H'], $this->communityString, $this->oid[$check]);
        } catch (Exception $e) {
            echo "Check Failed";
            exit(self::STATE_UNKNOWN);
        }

        switch ($check)
        {
            case "webinterface":
                $this->checkWebinterface($snmpValue);
            break;
            case "hotairtemp":
            case "coldairtemp":
                $this->checkAirTemp($snmpValue, $options['W'], $options['M']);
            break;
            case "fanspeedpercentage":
                $this->checkFanSpeed($snmpValue, 'percentage', $options['W'], $options['M']);
            break;
            case "fanspeedrpm":
                $this->checkFanSpeed($snmpValue, 'rpm', $options['W'], $options['M']);
            break;
            default:
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the cold air temp and gives a warning when the temp is too low
     *
     * @param int $temperature        The current temperature
     * @param int $warningTemperature Temperature that should raise an warning
     * @param int $maxTemperature     Critical temperature
     */

    private function checkAirTemp(int $temperature, int $warningTemperature, int $maxTemperature) :void
    {
        $temperature = $temperature/10;

        switch (true)
        {
                case $temperature < $warningTemperature:
                echo "{$temperature}C|'Celcius'={$temperature};{$warningTemperature};{$maxTemperature};0;50";
                exit(self::STATE_OK);

                case $temperature < $maxTemperature:
                echo "{$temperature}C|'Celcius'={$temperature};{$warningTemperature};{$maxTemperature};0;50";
                exit(self::STATE_WARNING);

                case $temperature >= $maxTemperature:
                echo "{$temperature}C|'Celcius'={$temperature};{$warningTemperature};{$maxTemperature};0;50";
                exit(self::STATE_CRITICAL);

                default:
                echo "0C|'Celcius'={$temperature};{$warningTemperature};{$maxTemperature};0;50";
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the temperature values of the chiller
     * @param  float  $fanSpeed        current fanspeed measurement in percentage or rpm
     * @param  string $measuringUnit   percentage or rpm
     * @param  int    $warningFanSpeed warning rpm or percentage
     * @param  int    $maxFanSpeed     max rpm or percentage
     * @return void
     */

    private function checkFanSpeed(int $fanSpeed, string $measuringUnit, int $warningFanSpeed, int $maxFanSpeed) :void
    {
        switch ($measuringUnit) {
            case 'percentage':
                    $unit = '%';
                    $maxValue = '100';
                break;
            case 'rpm':
                    $unit = 'rpm';
                    $maxValue = '3700';
                break;
            default:
                    exit(self::STATE_UNKNOWN);
                break;
        }

        switch (true)
        {
                case $fanSpeed < 1:
                echo "{$fanSpeed}{$unit}|'{$unit}'={$fanSpeed};{$warningFanSpeed};{$maxFanSpeed};0;{$maxValue}";
                exit(self::STATE_CRITICAL);

                case $fanSpeed < $warningFanSpeed:
                echo "{$fanSpeed}{$unit}|'{$unit}'={$fanSpeed};{$warningFanSpeed};{$maxFanSpeed};0;{$maxValue}";
                exit(self::STATE_OK);

                case $fanSpeed < $maxFanSpeed:
                echo "{$fanSpeed}{$unit}|'{$unit}'={$fanSpeed};{$warningFanSpeed};{$maxFanSpeed};0;{$maxValue}";
                exit(self::STATE_WARNING);

                case $fanSpeed >= $maxFanSpeed:
                echo "{$fanSpeed}{$unit}|'{$unit}'={$fanSpeed};{$warningFanSpeed};{$maxFanSpeed};0;{$maxValue}";
                exit(self::STATE_CRITICAL);

                default:
                echo "0{$unit}|'{$unit}'={$fanSpeed};{$warningFanSpeed};{$maxFanSpeed};0;{$maxValue}";
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks if the webinterface is still running
     * @param int $thttpdStatus     Status of the web process. 0 or 1
     *
     * @return void
     */

    private function checkWebinterface($thttpdStatus) :void
    {
        switch ($thttpdStatus)
        {
                case 0:
                    echo "Web interface running";
                    exit(self::STATE_OK);

                case 1:
                    echo "Web interface not running";
                    exit(self::STATE_CRITICAL);

                default:
                    echo "Web interface not running";
                    exit(self::STATE_CRITICAL);
        }
    }

    /**
     * Displays the help information
     */

    private function help() :void
    {
        echo "
        Check plugin for rittal pdus

        // Base parameters
        -H hostname
        -C check to run

        -W Warning value
        -M Max value

        \010\010\010\010\010\010\010\010";
        exit;
    }
}
