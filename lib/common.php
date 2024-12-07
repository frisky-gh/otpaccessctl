<?php

require("Twig/autoload.php");
require("Monolog/autoload.php");
require("Bacon/BaconQrCode/autoload.php");
require("php-totp/src/Base32.php");
require("php-totp/src/Hotp.php");
require("php-totp/src/Totp.php");

function handle_error_and_throw_it_as_exception($severity, $message, $filename, $lineno) {
	log_info("exception: {$severity}: {$message} in {$filename}:{$lineno}");
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
}
set_error_handler('handle_error_and_throw_it_as_exception');
ini_set('display_errors', 1);

//// functions about logging
date_default_timezone_set("Asia/Tokyo");

$logformatter = new \Monolog\Formatter\LineFormatter("%datetime% %channel% %level_name% %message% %context%\n", "Y-m-d H:i:s", false, true);
$loghandler = new \Monolog\Handler\RotatingFileHandler(__DIR__."/../log/app.log");
$loghandler->setFilenameFormat('{filename}_{date}', 'Y-m-d');
$loghandler->setFormatter($logformatter);
$logger = new \Monolog\Logger('app');
$logger->pushHandler($loghandler);

function set_channel_of_log($channelname) {
	global $logger;
	$logger = $logger->withName($channelname);
	return true;
}

function log_info($message, $context = []) {
	global $logger;
	$logger->info($message, $context);
}

//// functions about settings
function load_setting() {
	$setting = parse_ini_file( __DIR__."/../conf/setting.ini", true );

	// ldap section
	$setting["ldap"] ??= [];
	$setting["ldap"]["uri"] ??= "ldap://localhost";
	$setting["ldap"]["search_binddn"]     ??= "";
	$setting["ldap"]["search_bindpasswd"] ??= "";
	$setting["ldap"]["search_basedn"]     ??= "dc=example,dc=com";
	$setting["ldap"]["search_filter"]     ??= "(objectClass=inetOrgPerson)";
	$setting["ldap"]["search_nameattr"]   ??= "cn";
	$setting["ldap"]["search_mailattr"]   ??= "mail";

	// maildomain section
	$setting["maildomain"] ??= [];
	$setting["maildomain"]["domain"] ??= "example.com";

	// totp section
	$setting["totp"] ??= [];
	$setting["totp"]["issuer"] ??= "example.com";
	$setting["totp"]["digits"] ??= "6";
	$setting["totp"]["period"] ??= "30";

	// cron section
	$setting["cron"] ??= [];
	$setting["cron"]["period"] ??= [];
	$setting["cron"]["interval_sec_of_acceptance"]   ??= 10;
	$setting["cron"]["interval_min_of_exec_by_cron"] ??= 1;
	$setting["cron"]["lifetime_min_of_pass"]         ??= 60;
	$setting["cron"]["write_command"]                ??= "bin/write_apache_setting.php";
	$setting["cron"]["reload_command"]               ??= "";

	// web section
	$setting["web"]["base_url"] ??= "https://example.com/otpaccessctl/";
	$setting["web"]["base_url"] = preg_replace('|/$|', '', $setting["web"]["base_url"] );
	$setting["web"]["expiration_min_of_issuance"] ??= 15;
	$setting["web"]["auth_method"] ??= "maildomain";
	$setting["web"]["app_name"]    ??= "OTPAccessCtl";
	$setting["web"]["org_name"]    ??= "example.com";

	return $setting;
}

function validate_inputs() {
	$_GET["message"] ??= null;

	if( !array_key_exists("username", $_GET)  ) $_GET ["username"] = null;
	else if( !preg_match('|^[-+.\w]{1,80}$|', $_GET ["username"]) )  $_GET ["username"] = null;
	if( !array_key_exists("username", $_POST) ) $_POST["username"] = null;
	else if( !preg_match('|^[-+.\w]{1,80}$|', $_POST ["username"]) ) $_POST["username"] = null;

	if( !array_key_exists("password", $_POST) ) $_POST["password"] = null;
	else if( !preg_match('|^[\x20-\x7e]{1,80}$|', $_POST ["password"]) ) $_POST["password"] = null;

	if( !array_key_exists("sessionkey", $_GET)  ) $_GET ["sessionkey"] = null;
	else if( !preg_match('|^[0-9a-fA-F]{1,256}$|', $_GET ["sessionkey"]) ) $_GET ["sessionkey"] = null;
	if( !array_key_exists("sessionkey", $_POST) ) $_POST["sessionkey"] = null;
	else if( !preg_match('|^[0-9a-fA-F]{1,256}$|', $_POST["sessionkey"]) ) $_POST["sessionkey"] = null;

	if( !array_key_exists("token", $_POST) ) $_POST["token"] = null;
	else if( !preg_match('|^\d{1,16}$|', $_POST["token"]) ) $_POST["token"] = null;

	return true;
}

//// functions about LDAP
function auth_by_ldap($setting, $username, $password) {
	$uri = $setting["ldap"]["uri"];
	//print "uri:{$uri}<br>\n";

	$conn = ldap_connect($uri);
	if( !$conn ) return false;

	$r = ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	if( !$r ) return false;

	$search_binddn     = $setting["ldap"]["search_binddn"];
	$search_bindpasswd = $setting["ldap"]["search_bindpasswd"];
	//print "search_binddn:{$search_binddn}, bsearch_indpasswd:{$search_bindpasswd}<br>\n";
	if( $search_binddn = "" ){
		$r = ldap_bind($conn);
		if( !$r ) return false;
	}else{
		$r = ldap_bind($conn, $search_binddn, $search_bindpasswd);
		if( !$r ) return false;
	}

	$search_basedn = $setting["ldap"]["search_basedn"];
	$search_filter = $setting["ldap"]["search_filter"];
	$search_nameattr   = $setting["ldap"]["search_nameattr"];
	$search_mailattr   = $setting["ldap"]["search_mailattr"];
	$u = ldap_escape( $username );
	$f = "(&{$search_filter}({$search_nameattr}={$u}))";
	//print "search_basedn:{$search_basedn}, search_filter:{$search_filter}, filter={$f}<br>\n";
	$r = ldap_search($conn, $search_basedn, $f, array($search_mailattr) );
	if( !$r ) return false;

	$result = ldap_get_entries($conn, $r);
	if( $result["count"] < 1 ) return false;

	$user_binddn = $result[0]["dn"];
	$user_mail   = $result[0][$search_mailattr][0];
	print "user_binddn:{$user_binddn}, user_mail:{$user_mail}<br>\n";

	$r = ldap_bind($conn, $user_binddn, $password);
	if( !$r ) return false;

	ldap_close( $conn );
	return $user_mail;
}

//// functions about Time-based One-Time-Password
$base32 = new \lfkeitel\phptotp\Base32();

function generate_totpsecret () {
	// 80 bits
	global $base32;
	return $base32->encode( random_bytes(10) );
}

function generate_sessionkey () {
	// 256 bits
	return bin2hex( random_bytes(32) );
}

function generate_otpauth_url ($username, $setting, $account) {
	$accountname = urlencode( $username );
	$issuer = urlencode( $setting["totp"]["issuer"] );
	$digits = intval( $setting["totp"]["digits"] );
	$period = intval( $setting["totp"]["period"] );
	$secret = urlencode( $account["totpsecret"] );
	$url = "otpauth://totp/{$issuer}:{$accountname}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits={$digits}&period={$period}";
	return $url;
}

function generate_token ($setting, $account) {
	global $base32;

	$period = $setting["totp"]["period"];
	$digits = $setting["totp"]["digits"];

	$totpsecret         = $account["totpsecret"];
	$totpsecret_decoded = $base32->decode($totpsecret);

	$totp  = new \lfkeitel\phptotp\Totp( "sha1", 0, $period );
	$token = $totp->GenerateToken($totpsecret_decoded, time(), $digits);
	return $token;
}

//// functions about QR code
$qrcode_renderer = new \BaconQrCode\Renderer\ImageRenderer(
	new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
	new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
);
$qrcode_writer = new \BaconQrCode\Writer($qrcode_renderer);

function generate_qrcode ($text) {
	global $qrcode_writer;
	$svg = $qrcode_writer->writeString($text);
	return $svg;
}

//// functions about account file input / output

function remove_account ($username) {
	$authedfile   = "status/account/{$username}.ini";
	$unauthedfile = "status/account_unathed/{$username}.ini";
	if( file_exists($authedfile) ){
		if( !unlink($authedfile) ){
			return false;
		}
	}
	if( file_exists($unauthedfile) ){
		if( !unlink($unauthedfile) ){
			return false;
		}
	}
	return true;
}

function load_account ($username, $load_account_in_authed = true, $load_account_in_unauthed = true) {
	$authedfile   = "status/account/{$username}.ini";
	$unauthedfile = "status/account_unauthed/{$username}.ini";

	if( $load_account_in_authed && file_exists($authedfile) ){
		$acct = parse_ini_file( $authedfile );
		if( $acct != false ) return $acct;
	}

	if( $load_account_in_unauthed && file_exists($unauthedfile) ){
		$acct = parse_ini_file( $unauthedfile );
		if( $acct != false ) return $acct;
	}

	return null;
}

function validate_account ($username) {
	$authedfile   = "status/account/{$username}.ini";
	$unauthedfile = "status/account_unauthed/{$username}.ini";
	if( file_exists($authedfile) )    return false;
	if( !file_exists($unauthedfile) ) return false;
	if( !rename($unauthedfile, $authedfile) ) return false;
	return true;
}

function store_account ($username, $totpsecret, $sessionkey) {
	$now = time();
	$content = "";
	$content .= "username={$username}\n";
	$content .= "totpsecret={$totpsecret}\n";
	$content .= "sessionkey={$sessionkey}\n";
	$content .= "creationtime={$now}\n";

	file_put_contents("status/account_unauthed/{$username}.ini", $content);
	return true;
}

//// functions about pass file input / output

function store_pass ($sessionkey, $username, $ipaddr, $store_pass_as_authed) {
	$unauthedfile = "status/pass_unauthed/{$sessionkey}.ini";
	$authedfile   = "status/pass_inactive/{$sessionkey}.ini";
	$now = time();
	$content = "";
	$content .= "username={$username}\n";
	$content .= "sessionkey={$sessionkey}\n";
	$content .= "ipaddr={$ipaddr}\n";
	$content .= "creationtime={$now}\n";

	if( $store_pass_as_authed ){ file_put_contents($authedfile,   $content); }
	else                       { file_put_contents($unauthedfile, $content); }
	return true;
}

function load_pass ($sessionkey, $load_pass_in_authed, $load_pass_activated = false) {
	$unauthedfile  = "status/pass_unauthed/{$sessionkey}.ini";
	$authedfile    = "status/pass_inactive/{$sessionkey}.ini";
	$activatedfile = "status/pass/{$sessionkey}.ini";

	if( !$load_pass_activated && $load_pass_in_authed  && file_exists($authedfile) ){
		$pass = parse_ini_file( $authedfile );
		if( $pass != false ) return $pass;
	}

	if( !$load_pass_activated && !$load_pass_in_authed && file_exists($unauthedfile) ){
		$pass = parse_ini_file( $unauthedfile );
		if( $pass != false ) return $pass;
	}

	if( $load_pass_activated  && $load_pass_in_authed  && file_exists($activatedfile) ){
		$pass = parse_ini_file( $activatedfile );
		if( $pass != false ) return $pass;
	}

	return null;
}

function validate_pass ($sessionkey) {
	$unauthedfile = "status/pass_unauthed/{$sessionkey}.ini";
	$authedfile   = "status/pass_inactive/{$sessionkey}.ini";
	if( file_exists($authedfile) )    return false;
	if( !file_exists($unauthedfile) ) return false;
	if( !rename($unauthedfile, $authedfile) ) return false;
	return true;
}

function pass_is_activated ($sessionkey) {
	$authedfile = "status/pass_inactive/{$sessionkey}.ini";
	$activefile = "status/pass/{$sessionkey}.ini";

	if( file_exists($authedfile) ) return false;
	if( file_exists($activefile) ) return true;
	return false;
}

function activate_passes( &$acceptedlist_is_changed ) {
	$dh = opendir( "status/pass_inactive" );
	while( false !== ($entry = readdir($dh)) ){
		$matches = null;
		if( !preg_match('/^([0-9a-f]+)\.ini$/', $entry, $matches) ) continue;
		$src = "status/pass_inactive/".$entry;
		$dst = "status/pass/".$entry;
		rename( $src, $dst );
		$acceptedlist_is_changed = true;

		if( $matches == null || $matches[1] == null ) continue;
		$sessionkey = $matches[1];
		$pass = load_pass ($sessionkey, true, true);
		if( $pass == null ) continue;

		$ipaddr = $pass["ipaddr"];
		$username = $pass["username"];
		log_info("accepte new pass, sessionkey={$sessionkey}, username={$username}, ipaddr={$ipaddr}.");
		log_tty ("accepte new pass, sessionkey={$sessionkey}, username={$username}, ipaddr={$ipaddr}.");
	}
	return true;
}

function direct_cleanup() {
	touch( "status/cleanup" );
}

function cleanup_is_directed() {
	$r = file_exists( "status/cleanup" );
	return $r;
}

function cleanup_passes( $setting, &$acceptedlist_is_changed ) {
	$dh = opendir( "status/pass" );
	$time = time();
	while( false !== ($entry = readdir($dh)) ){
		if( !preg_match('/^[0-9a-f]+\.ini$/', $entry) ) continue;
		$acceptedfile = parse_ini_file( "status/pass/".$entry );

		$timeout = $acceptedfile["creationtime"] + $setting["cron"]["lifetime_min_of_pass"] * 60;
		if( $time < $timeout ) continue;

		$src = "status/pass/".$entry;
		$dst = "status/pass_expired/".$entry;
		rename( $src, $dst );
		$acceptedlist_is_changed = true;
	}
	closedir( $dh );

	if( file_exists("status/cleanup") ) $r = unlink( "status/cleanup" );
	return true;
}

//// functions about management of accepted pass

function generate_acceptedlist() {
	$dh = opendir( "status/pass" );
	$accepted = [];
	while( false !== ($entry = readdir($dh)) ){
		if( !preg_match('/^[0-9a-f]+\.ini$/', $entry) ) continue;
		$acceptedfile = parse_ini_file( "status/pass/".$entry );

		$ipaddr = $acceptedfile["ipaddr"];
		$accepted[$ipaddr] = 1;
	}
	closedir( $dh );
	$acceptedlist = array_keys($accepted);
	ksort( $acceptedlist );
	return $acceptedlist;
}

function load_acceptedlist() {
	if( !file_exists("status/acceptedlist") ) return [];
	$acceptedlist = file("status/acceptedlist", FILE_IGNORE_NEW_LINES);
	ksort( $acceptedlist );
	return $acceptedlist;
}

function two_acceptedlists_are_diffent($acceptedlist, $last_acceptedlist) {
	ksort( $acceptedlist );
	ksort( $last_acceptedlist );
	$acceptedlist_concat      = implode("\n", $acceptedlist);
	$last_acceptedlist_concat = implode("\n", $last_acceptedlist);
	if( $acceptedlist_concat == $last_acceptedlist_concat ) return false;
	return true;
}

function store_acceptedlist( $acceptedlist ) {
	ksort( $acceptedlist );
	$acceptedlist_concat = implode("\n", $acceptedlist) . "\n";
	file_put_contents("status/acceptedlist", $acceptedlist_concat);
}


//// functions about mail
$loader = new \Twig\Loader\FilesystemLoader(__DIR__."/..");
$twig   = new \Twig\Environment($loader);

function send_mail_at_account_issuance ($setting, $mail, $username, $sessionkey) {
	global $twig;
	$content = $twig->render( "conf/mail_at_account_issuance.tmpl", [
		"username"   => $username,
		"sessionkey" => $sessionkey,
		"now" => date("Y-m-d H:i:s"),
		"base_url"                   => $setting["web"]["base_url"],
		"expiration_min_of_issuance" => $setting["web"]["expiration_min_of_issuance"],
	] );
	$header = parse_ini_file( "conf/mail_at_account_issuance.ini" );
	$header["From"]    ??= "System <admin@example.com>";
	$header["Subject"] ??= "Subject";

	$subject = $header["Subject"];
	unset( $header["Subject"] );

	mb_send_mail( $mail, $subject, $content, $header );
	return true;
}

function send_mail_at_pass_issuance ($setting, $mail, $username, $sessionkey) {
	global $twig;
	$content = $twig->render( "conf/mail_at_pass_issuance.tmpl", [
		"username"   => $username,
		"sessionkey" => $sessionkey,
		"now" => date("Y-m-d H:i:s"),
		"base_url"                   => $setting["web"]["base_url"],
		"expiration_min_of_issuance" => $setting["web"]["expiration_min_of_issuance"],
	] );
	$header = parse_ini_file( "conf/mail_at_pass_issuance.ini" );
	$header["From"]    ??= "System <admin@example.com>";
	$header["Subject"] ??= "Subject";

	$subject = $header["Subject"];
	unset( $header["Subject"] );

	mb_send_mail( $mail, $subject, $content, $header );
	return true;
}


