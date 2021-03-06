# Update

I have written a full MIB that you can find here: https://github.com/epiecs/carel-pco-mibs

# Installation

1 - upload check_rittal_chiller.php to /usr/local/nagios/libexec and chmod +x and check that you have the correct owner/group

2 - upload the contents of the nagios-import directory to /usr/local/nagios/etc/import and import the config files in the core config manager

# Example host config

```
###############################################################################
#
# Hosts configuration file
#
# Created by: Nagios CCM 3.0.3
# Date:       2019-09-24 15:09:20
# Version:    Nagios Core 4.x
#
# --- DO NOT EDIT THIS FILE BY HAND ---
# Nagios CCM will overwrite all manual settings during the next update if you
# would like to edit files manually, place them in the 'static' directory or
# import your configs into the CCM by placing them in the 'import' directory.
#
###############################################################################

define host {
    host_name         DC1 Chiller 1
    use               rittal-chiller
    address           192.168.1.2
    hostgroups        chillers, datacenter
    contact_groups    Networking
    register          1
}

###############################################################################
#
# Hosts configuration file
#
# END OF FILE
#
###############################################################################
```

```
# Example services config

###############################################################################
#
# Services configuration file
#
# Created by: Nagios CCM 3.0.3
# Date:       2019-09-24 15:11:14
# Version:    Nagios Core 4.x
#
# --- DO NOT EDIT THIS FILE BY HAND ---
# Nagios CCM will overwrite all manual settings during the next update if you
# would like to edit files manually, place them in the 'static' directory or
# import your configs into the CCM by placing them in the 'import' directory.
#
###############################################################################

define service {
    host_name                DC1 Chiller 1
    service_description      Pco module
    servicegroups            Datacenter devices
    check_command            check_rittal_chiller_webinterface!!!!!!!!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                DC1 Chiller 1
    service_description      Cold air temperature
    servicegroups            chillers, datacenter
    check_command            check_rittal_chiller_coldairtemp!25!28!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                DC1 Chiller 1
    service_description      Hot air temperature
    servicegroups            chillers, datacenter
    check_command            check_rittal_chiller_hotairtemp!26!30!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                DC1 Chiller 1
    service_description      Fan speed percentage
    servicegroups            chillers, datacenter
    check_command            check_rittal_chiller_fanspeedpercentage!60!90!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

###############################################################################
#
# Services configuration file
#
# END OF FILE
#
###############################################################################
```
