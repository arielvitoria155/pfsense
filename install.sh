!#/bin/sh

pkg add http://pkg.freebsd.org/FreeBSD:11:amd64/latest/All/axel-2.4_2.txz
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-squid
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-squidguard
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-cron
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-zabbix-agent34 
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-zabbix-proxy34
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-openvpn-client-export
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-Lightsquid 
env ASSUME_ALWAYS_YES=YES pkg install pfsense-pkg-Service_Watchdog

axel http://cloud.gruposopro.com.br:5850/sharing/KKJou4gM0

cp sgerror.php /usr/local/www




