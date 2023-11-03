<?php

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))
    require_once($path); 
else die(var_dump($path . ' was not found. file=config.php'));

is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'packagist.org.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . '/packagist.org.html'))))) / 86400)) <= 0 ) {
    $url = 'https://packagist.org/';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://packagist.org/';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
}

libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );   
$xpath = new DOMXPath($dom);

$destination = $xpath->query('//head/meta');
$template = $dom->createDocumentFragment();
$template->appendXML('<base href="https://packagist.org/" />');
$destination[0]->parentNode->insertBefore($template, $destination[0]->nextSibling);
echo $dom->saveHTML();

/*

$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html'));

$divs = $dom->getElementsByTagName('head');


$element = $dom->createElement('test', 'This is the root element!');

$elm = createElement($dom, 'foo', 'bar', array('attr_name'=>'attr_value'));

$dom->appendChild($elm);

*/

//dd($divs);

//$content_node=$dom->getElementById("main");
//$node=getElementsByClass($content_node, 'p', 'latest');

 //$dom->saveHTML($dom->documentElement);
 
 
//echo file_get_contents("https://packagist.org/"); ?>