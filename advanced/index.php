<?php
/* Detailed Pi-hole Block Page: Show "Website Blocked" if user browses to site, but not to image/file requests based on the work of WaLLy3K for DietPi & Pi-Hole */

function validIP($address){
	if (preg_match('/[.:0]/', $address) && !preg_match('/[1-9a-f]/', $address)) {
		// Test if address contains either `:` or `0` but not 1-9 or a-f
		return false;
	}
	return !filter_var($address, FILTER_VALIDATE_IP) === false;
}

$uri = escapeshellcmd($_SERVER['REQUEST_URI']);
$serverName = escapeshellcmd($_SERVER['SERVER_NAME']);
$url =  escapeshellcmd($_SERVER['HTTP_HOST']);

// If the server name is 'pi.hole', it's likely a user trying to get to the admin panel.
// Let's be nice and redirect them.
if ($url == 'pi.hole')
{
    header('HTTP/1.1 301 Moved Permanently');
    header("Location: /admin/");
}

// Retrieve server URI extension (EG: jpg, exe, php)
ini_set('pcre.recursion_limit',100);
$uriExt = pathinfo($uri, PATHINFO_EXTENSION);

// Define which URL extensions get rendered as "Website Blocked"
$webExt = array('asp', 'htm', 'html', 'php', 'rss', 'xml');

// Get IPv4 and IPv6 addresses from setupVars.conf (if available)
$setupVars = parse_ini_file("/etc/pihole/setupVars.conf");
$ipv4 = isset($setupVars["IPV4_ADDRESS"]) ? explode("/", $setupVars["IPV4_ADDRESS"])[0] : $_SERVER['SERVER_ADDR'];
$ipv6 = isset($setupVars["IPV6_ADDRESS"]) ? explode("/", $setupVars["IPV6_ADDRESS"])[0] : $_SERVER['SERVER_ADDR'];

$AUTHORIZED_HOSTNAMES = array(
	$ipv4,
	$ipv6,
	str_replace(array("[","]"), array("",""), $_SERVER["SERVER_ADDR"]),
	"pi.hole",
	"localhost");
// Allow user set virtual hostnames
$virtual_host = getenv('VIRTUAL_HOST');
if (!empty($virtual_host))
	array_push($AUTHORIZED_HOSTNAMES, $virtual_host);

// Immediately quit since we didn't block this page (the IP address or pi.hole is explicitly requested)
if(validIP($url) || in_array($url,$AUTHORIZED_HOSTNAMES))
{
	http_response_code(404);
	die();
}

if(in_array($uriExt, $webExt) || empty($uriExt))
{
	// Requested resource has an extension listed in $webExt
	// or no extension (index access to some folder incl. the root dir)
	$showPage = true;
}
else
{
	// Something else
	$showPage = false;
}

// Handle incoming URI types
if (!$showPage)
{
?>
<html>
<head>
<script>window.close();</script></head>
<body>
<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
</body>
</html>
<?php
	die();
}

// Get Pi-hole version
$piHoleVersion = exec('cd /etc/.pihole/ && git describe --tags --abbrev=0');

// Don't show the URI if it is the root directory
if($uri == "/")
{
	$uri = "";
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset='UTF-8'/>
	<title>Website Blocked</title>
	<link rel='stylesheet' href='http://pi.hole/pihole/blockingpage.css'/>
	<link rel='shortcut icon' href='http://pi.hole/admin/img/favicon.png' type='image/png'/>
	<meta name='viewport' content='width=device-width,initial-scale=1.0,maximum-scale=1.0, user-scalable=no'/>
	<meta name='robots' content='noindex,nofollow'/>
</head>
<body id="body">
<header>
	<h1><a href='/'>Website Blocked</a></h1>
</header>
<main>
	<div>Access to the following site has been blocked:<br/>
	<span class='pre msg'><?php echo $url; ?></span></div>
	<div>If you have an ongoing use for this website, please ask the owner of the Pi-hole in your network to have it whitelisted.</div>
	<input id="domain" type="hidden" value="<?php echo $url; ?>">
	<input id="quiet" type="hidden" value="yes">
	<button id="btnSearch" class="buttons blocked" type="button" style="visibility: hidden;"></button>
	This page is blocked because it is explicitly contained within the following block list(s):
	<pre id="output" style="width: 100%; height: 100%;" hidden="true"></pre><br/>
	<div class='buttons blocked'>
		<a class='safe33' href='javascript:history.back()'>Go back</a>
		<a class='safe33' id="whitelisting">Whitelist this page</a>
		<a class='safe33' href='javascript:window.close()'>Close window</a>
	</div>
		<div style="width: 98%; text-align: center; padding: 10px;" hidden="true" id="whitelistingform">
			<p>Note that whitelisting domains which are blocked using the wildcard method won't work.</p>
			<p>Password required!</p><br/>
		<form>
			<input name="list" type="hidden" value="white"><br/>
			Domain:<br/>
			<input name="domain" value="<?php echo $url ?>" disabled><br/><br/>
			Password:<br/>
			<input type="password" id="pw" name="pw"><br/><br/>
			<button class="buttons33 safe" id="btnAdd" type="button">Whitelist</button>
		</form><br/>
		<pre id="whitelistingoutput" style="width: 100%; height: 100%; padding: 5px;" hidden="true"></pre><br/>
		</div>
</main>
<footer>Generated <?php echo date('D g:i A, M d'); ?> by Pi-hole <?php echo $piHoleVersion; ?></footer>
<script src="http://pi.hole/admin/scripts/vendor/jquery.min.js"></script>
<script>
// Create event for when the output is appended to
(function($) {
    var origAppend = $.fn.append;

    $.fn.append = function () {
        return origAppend.apply(this, arguments).trigger("append");
    };
})(jQuery);
</script>
<script src="http://pi.hole/admin/scripts/pi-hole/js/queryads.js"></script>
<script>
function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

// Try to detect if page is loaded within iframe
if(inIframe())
{
    // Within iframe
    // hide content of page
    $('#body').hide();
    // remove background
    document.body.style.backgroundImage = "none";
}
else
{
    // Query adlists
    $( "#btnSearch" ).click();
}

$( "#whitelisting" ).on( "click", function(){ $( "#whitelistingform" ).removeAttr( "hidden" ); });

// Remove whitelist functionality if the domain was blocked because of a wildcard
$( "#output" ).bind("append", function(){
	if($( "#output" ).contents()[0].data.indexOf("Wildcard blocking") !== -1)
	{
		$( "#whitelisting" ).hide();
		$( "#whitelistingform" ).hide();
	}
});

function add() {
	var domain = $("#domain");
	var pw = $("#pw");
	if(domain.val().length === 0){
		return;
	}

	$.ajax({
		url: "/admin/scripts/pi-hole/php/add.php",
		method: "post",
		data: {"domain":domain.val(), "list":"white", "pw":pw.val()},
		success: function(response) {
			$( "#whitelistingoutput" ).removeAttr( "hidden" );
			if(response.indexOf("Pi-hole blocking") !== -1)
			{
				// Reload page after 5 seconds
				setTimeout(function(){window.location.reload(1);}, 5000);
				$( "#whitelistingoutput" ).html("---> Success <---<br/>You may have to flush your DNS cache");
			}
			else
			{
				$( "#whitelistingoutput" ).html("---> "+response+" <---");
			}

		},
		error: function(jqXHR, exception) {
			$( "#whitelistingoutput" ).removeAttr( "hidden" );
			$( "#whitelistingoutput" ).html("---> Unknown Error <---");
		}
	});
}
// Handle enter button for adding domains
$(document).keypress(function(e) {
    if(e.which === 13 && $("#pw").is(":focus")) {
        add();
    }
});

// Handle buttons
$("#btnAdd").on("click", function() {
    add();
});
</script>
</body>
</html>
