<?php
use Fwlib\Net\Curl;
use Fwlib\Util\UtilContainer;

require_once __DIR__ . '/../config.default.php';

$curl = new Curl;
$env = UtilContainer::getInstance()->getEnv();


/** @noinspection SpellCheckingInspection */
$testSite = 'www.httpbin.org';


// HTTP
$response = $curl->get("http://$testSite/");
$env->ecl('HTTP get: ' . $curl->getLastCode());
$env->ecl('Title: ' . $curl->match('/<title>(.*?)<\/title>/', $response));

$env->ecl();

// HTTPS
$response = $curl->post("http://$testSite/post", ['q' => 'fwlib']);
$env->ecl('HTTPS post: ' . $curl->getLastCode());
$env->ecl('Post data: ' . var_export(json_decode($response, true)['form'], true));
