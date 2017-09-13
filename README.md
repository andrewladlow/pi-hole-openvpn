## pi-hole-openvpn

Installs OpenVPN and Pi-Hole, preconfigured to function together correctly. Uses Nginx rather than Pi-hole's default Lighttpd due to personal preference. 

Upstream DNS is set to a local caching recursive server via Bind9 on a private IP (default: `192.168.2.1`) and can only be queried via local interfaces. 

Tested on Debian Stretch 9.1.

Based on Nyr's [openvpn-install](https://github.com/Nyr/openvpn-install) script, and [pi-hole](https://github.com/pi-hole/pi-hole).
