# pi-hole-openvpn

Installs OpenVPN and Pi-Hole, preconfigured to function together correctly. Uses Nginx rather than Pi-hole's default Lighttpd due to personal preference. 

Upstream DNS is set to a local caching recursive server via Bind9 on a private IP (default: `192.168.2.1`) and is only accessible over the VPN interface. 

Tested on Debian Stretch 9.1
