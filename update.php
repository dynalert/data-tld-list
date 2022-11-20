<?php

$iana_tld_list_dir = './cache/iana_tld/'.date('Y').'/';
$public_suffix_list_dir = './cache/public_suffix/'.date('Y').'/';

$iana_tld_list_path = $iana_tld_list_dir.'/'.date('Y-m-d').'.txt';
$public_suffix_list_path = $public_suffix_list_dir.'/'.date('Y-m-d').'.txt';

$iana_tld_list_url = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
$public_suffix_list_url = 'https://publicsuffix.org/list/public_suffix_list.dat';

// makedir
makedir('./data/');
makedir($iana_tld_list_dir);
makedir($public_suffix_list_dir);

// check if list was updated today
if(!file_exists($iana_tld_list_path)){
	logEcho('getUrl() $iana_tld_list_url');
	$iana_tld_list = getUrl($iana_tld_list_url);
	writeFile($iana_tld_list_path, $iana_tld_list);
}else{
	logEcho('file_get_contents() $iana_tld_list_path');	
	$iana_tld_list = file_get_contents($iana_tld_list_path);
}

// check if list was updated today
if(!file_exists($public_suffix_list_path)){
	logEcho('getUrl() $public_suffix_list_url');
	$public_suffix_list = getUrl($public_suffix_list_url);
	writeFile($public_suffix_list_path, $public_suffix_list);
}else{
	logEcho('file_get_contents() $public_suffix_list_url');	
	$public_suffix_list = file_get_contents($public_suffix_list_path);
}

// parse lists
logEcho('parseFile() $iana_tld_list');
$iana_tld_list_array = parseFile($iana_tld_list);

logEcho('parseFile() $public_suffix_list');
$public_suffix_list_array = parseFile($public_suffix_list);

// save lists
if(is_array($iana_tld_list_array)){
	logEcho('writeFile() $iana_tld_list');
	writeFile('./data/iana_tld_list.json', json_encode($iana_tld_list_array));	
	writeFile('./data/iana_tld_list.txt', $iana_tld_list);	
}
if(is_array($public_suffix_list_array)){
	logEcho('writeFile() $public_suffix_list');	
	writeFile('./data/public_suffix_list.json', json_encode($public_suffix_list_array));	
	writeFile('./data/public_suffix_list.txt', $public_suffix_list);	
}

function parseFile($data){
	if(empty($data)){
		return null;
	}
	
	$out = array();
	$data = explode("\n", $data);
	foreach($data as $line){
		$line = trim($line);				
		if(
			substr($line, 0, 1) == '/' ||
			substr($line, 0, 1) == '#' ||
			empty($line)
		){
			continue;
		}
		
		$out[] = strtolower($line);
	}
	return $out;
}

function writeFile($path, $data){
	if(!file_exists($path) && !empty($data)){
		file_put_contents($path, $data);
		return true;
	}
	return false;
}

function makeDir($path){
	if(!file_exists($path)){
		mkdir($path, 0777, true);
		return true;
	}
	return false;
}

function getUrl($url){
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	
	$headers = array();
	$headers[] = 'Authority: data.iana.org';
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8';
	$headers[] = 'Accept-Language: en-GB,en;q=0.7';
	$headers[] = 'Cache-Control: max-age=0';
	$headers[] = 'Sec-Fetch-Dest: document';
	$headers[] = 'Sec-Fetch-Mode: navigate';
	$headers[] = 'Sec-Fetch-Site: same-site';
	$headers[] = 'Sec-Fetch-User: ?1';
	$headers[] = 'Sec-Gpc: 1';
	$headers[] = 'Upgrade-Insecure-Requests: 1';
	$headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    //echo 'Error:' . curl_error($ch);
	    return false;
	}
	curl_close($ch);
	
	return $result;
}

function logEcho($line){
	var_dump($line);
}