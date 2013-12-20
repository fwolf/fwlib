<?php
/**
 * Example for Fwlib\Net\Curl
 *
 * @package     Fwlib\Net
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-10-02
 */

use Fwlib\Net\Curl;
use Fwlib\Util\UtilContainer;

require_once __DIR__ . '/../../../config.default.php';

$ch = new Curl;
$env = UtilContainer::getInstance()->get('Env');


// HTTP, Baidu
$html = $ch->get('http://www.baidu.com/');
$env->ecl('Baidu HTTP get: ' . $ch->getLastCode());
$env->ecl('Title: ' . $ch->match('/<title>(.*?)<\/title>/', $html));


// HTTPS, Alipay
$html = $ch->post('http://www.alipay.com/');
$charset = $ch->match('<meta charset="(.*?)">', $html);
$title = $ch->match('/<title>(.*?)<\/title>/', $html);
$title = mb_convert_encoding($title, 'utf-8', $charset);
$env->ecl('Alipay HTTPS get: ' . $ch->getLastCode());
$env->ecl('Title: ' . $title);


unset($ch);
