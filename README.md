## pi-hole-openvpn

Installs OpenVPN and Pi-Hole, preconfigured to function together correctly. Uses Nginx rather than Pi-hole's default Lighttpd due to personal preference. 

Upstream DNS is set to a local caching recursive server via Bind9 on a private IP (default: `192.168.2.1`) and can therefore only be queried via local interfaces.

Pi-Hole is configured for OpenVPN's tun0 interface (default: `10.8.0.1`) to ensure internal only access, as well as to allow for a standard site to be hosted on port `80` on the server's primary public IP, rather than the pi-hole's admin web interface. 

Tested on Debian Stretch 9.1.

Based on Nyr's [openvpn-install](https://github.com/Nyr/openvpn-install) script, and [pi-hole](https://github.com/pi-hole/pi-hole).
