## pi-hole-openvpn

OpenVPN client connections will seamlessly include DNS level ad blocking, with upstream DNS being handled locally. 

Installs OpenVPN and Pi-hole, preconfigured to function together correctly. Uses Nginx rather than Pi-hole's default Lighttpd due to personal preference. 

Upstream DNS is set to a local caching recursive server via Bind9 on a private IP (default: `192.168.2.1`) and can therefore only be queried via local interfaces.

Pi-hole is configured for OpenVPN's tun0 interface (default: `10.8.0.1`) to deny external access, as well as to allow for a standard site to be hosted on port `80` on the server's primary public IP, rather than the Pi-hole's admin web interface. 

Tested on Debian Stretch 9.1.

Based on Nyr's [openvpn-install](https://github.com/Nyr/openvpn-install) script, and [pi-hole](https://github.com/pi-hole/pi-hole).
