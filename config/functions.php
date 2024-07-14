<?php

require_once('constants.php');
/**
 * 
*/
function dd(mixed $param = null, $die = true, $debug = true) {
    $output = ($debug == true && !defined('APP_END') ? 
          'Execution time: <b>'  . round(microtime(true) - APP_START, 3) . '</b> secs' : 
          //APP_END . ' == APP_END'
          'Execution time: <b>'  . round(APP_END - APP_START, 3) . '</b> secs'
          ) . "<br />\n";
    if ($die)
      Shutdown::setEnabled(false)->setShutdownMessage(function() use ($param, $output) {
        return '<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output; //.  // var_dump
      })->shutdown();
    else
      return var_dump('<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output); // If you want to return the parameter after printing it
}
/*
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return ($http_status == 200 ? true : false);
*/

function check_ip($ip = '') {
  return filter_var($ip, FILTER_VALIDATE_IP) ? true : false;
}

function check_internet_connection($ip = '8.8.8.8') {
  $status = null;

  // Ping the host to check network connectivity
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
      exec("ping -n 1 -w 1 " . /*-W 20 */ escapeshellarg($ip), $output, $status);  // parse_url($ip, PHP_URL_HOST)
  else
      exec(APP_SUDO . "/bin/ping -c 1 -W 1 " . escapeshellarg($ip), $output, $status); // var_dump(\$status)
  if ($status !== 0)
    @fsockopen('www.google.com', 80, $errno, $errstr, 10) ?: $errors['APP_CONNECTIVITY'] = 'No internet connection.';

  return $status === 0;
}

// die(var_dump(check_internet_connection()) ? true : false);

function resolve_host_to_ip($host) {
  $ip = gethostbyname($host);
  return $ip !== $host ? $ip : false;
}

/* HTTP status of a URL and the network connectivity using ping */
function check_http_200($url = 'http://8.8.8.8') {
  if (defined('APP_CONNECTED')) { // check_ping() was 2 == fail | 0 == success
    if ($url !== 'http://8.8.8.8') {
      if (!empty($headers = get_headers($url)))
        return strpos($headers[0], '200') !== false ? false : true;
    } else
      return true; // Special case for the default URL
  }
  //return false; // Ping or HTTP request failed //$connected = @fsockopen("www.google.com", 80); //fclose($connected);
}

function packagist_return_source($vendor, $package) {
  $url = "https://packagist.org/packages/$vendor/$package";
  $initial_url = '';
  
  libxml_use_internal_errors(true); // Prevent HTML errors from displaying
  $dom = new DOMDocument(1.0, 'utf-8');
  if (check_http_200($url)) {
    $dom->loadHTML(file_get_contents($url));

    $anchors = $dom->getElementsByTagName('a');

    foreach ($anchors as $anchor) {
      if ($anchor->getAttribute('rel') == 'nofollow noopener external noindex ugc') {
        //echo $anchor->nodeValue;
        if ($anchor->nodeValue == 'Source')
            $initial_url = $anchor->getAttribute('href'); //  '/composer.json'
      }
    }
  }
  // $initial_url = "https://github.com/php-fig/log/tree/3.0.0/";

  if (preg_match('/^https:\/\/(?:www.?)github.com\//', $initial_url)) {

  // Extract username, repository, and version from the initial URL
    $parts = explode("/", rtrim($initial_url, "/"));
    dd($initial_url);
    dd($parts);
    
    $username = $parts[3];
    $repository = $parts[4];
    $version = $parts[6];

  //$blob_url = "https://github.com/$username/$repository/blob/$version/composer.json";
    return "https://raw.githubusercontent.com/$username/$repository/$version/composer.json";

  }
  return $initial_url;
}

function htmlsanitize(mixed $input = '') {

    if (is_array($input)) $input = var_export($input, true);
    
    if (is_null($input)) return;

    // Convert HTML entities to their corresponding characters
    $decoded = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
    
    // Remove any invalid UTF-8 characters
    $cleaned = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8');
    
    // Convert special characters to HTML entities
    $sanitized = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');

    return $sanitized;
}

//function htmlsanitize($argv = '') {
//  return html_entity_decode(mb_convert_encoding(htmlspecialchars($argv, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8' ), 'HTML-ENTITIES', 'utf-8');
//}


/**
This function takes in two parameters: $base and $path, which represent the base path and the path to be made relative, respectively.

It first detects the directory separator used in the base path, then splits both paths into arrays using that separator. It then removes the common base elements from the beginning of the path array, leaving only the difference.

Finally, it returns the relative path by joining the remaining elements in the path array using the separator detected earlier, with the separator prepended to the resulting string.

* Return a relative path to a file or directory using base directory. 
* When you set $base to /website and $path to /website/store/library.php
* this function will return /store/library.php
* 
* Remember: All paths have to start from "/" or "\" this is not Windows compatible.
* 
* @param   string   $base   A base path used to construct relative path. For example /website
* @param   string   $path   A full path to file or directory used to construct relative path. For example /website/store/library.php
* 
* @return  string
*/
function getRelativePath($base, $path) {
  // Detect directory separator
  $separator = substr((string)$base, 0, 1);
  $base = array_slice(explode($separator, rtrim((string)$base,$separator)),1);
  $path = array_slice(explode($separator, rtrim((string)$path,$separator)),1);

  return $separator . (string)implode($separator, array_slice($path, count($base)));
}


function readlinkToEnd($linkFilename) {
  if(!is_link($linkFilename)) return $linkFilename;
  $final = $linkFilename;
  while(true) {
    $target = readlink($final);
    $final = (substr($target, 0, 1) == '/') ? $target : dirname($final) . '/' . $target;
    if(substr($final, 0, 2)=='./') $final = substr($final, 2);
    if(!is_link($final)) return $final;
  }
}


/* function parse_ini_file_multi($file) {
  // parse_ini_string(file_get_contents($file), true, INI_SCANNER_NORMAL))) 
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED); 
  $output = [];
  foreach($data as $key => $value) {
    $keys = explode('.', $key);
    $temp = &$output;
    foreach($keys as $key) {
      $temp = &$temp[$key];
    }
    $temp = $value;
  }
  return $output;
}
function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach($data as $section => $values) {
    if (is_array($values)) {
      foreach($values as $key => $value) {
        $keys = explode('.', $key);
        $temp = &$output[$section];
        foreach($keys as $key_part) {
          $temp = &$temp[$key_part];
        }
        $temp = $value;
      }
    } else {
      $keys = explode('.', $section);
      $temp = &$output;
      foreach($keys as $key_part) {
        $temp = &$temp[$key_part];
      }
      $temp = $values;
    }
  }
  
  return $output;
}
function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
    if (is_array($values)) {
      $output[$section] = [];
      foreach ($values as $key => $value) {
        $output[$section][$key] = str_replace(['\'', '"'], '', var_export($value, true));
      }
    } else {
      $output[$section] = str_replace(['\'', '"'], '', var_export($values, true));
    }
  }

  return $output;
}

function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
      if (is_array($values)) {
          $output[$section] = [];
          foreach ($values as $key => $value) {
              // Check if the value is a regular expression
              if (preg_match('/^\/.*\/$/', $value)) {
                  $output[$section][$key] = $value;
              } else {
                  // If not a regular expression, add slashes to escape special characters
                  $output[$section][$key] = addcslashes($value, '"\\');
              }
          }
      } else {
          // Add slashes to non-array values
          $output[$section] = addcslashes($values, '"\\');
      }
  }

  return $output;
}

function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
      if (is_array($values)) {
          $output[$section] = [];
          foreach ($values as $key => $value) {
              // Do not escape regular expressions
              if (preg_match('/^\/.*\/[a-z]*$/i', $value)) {
                  $output[$section][$key] = $value;
              } else {
                  // Escape only non-regular expression values
                  $output[$section][$key] = addcslashes($value, '"\\');
              }
          }
      } else {
          // Escape only non-regular expression values
          if (preg_match('/^\/.*\/[a-z]*$/i', $values)) {
              $output[$section] = $values;
          } else {
              $output[$section] = addcslashes($values, '"\\');
          }
      }
  }

  return $output;
} */

function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
      if (is_array($values)) {
          $output[$section] = [];
          foreach ($values as $key => $value) {
              // Do not escape regular expressions
              if (preg_match('/^\/.*\/[a-z]*$/i', $value)) {
                  $output[$section][$key] = $value;
              } else {
                  // Handle boolean values explicitly
                  if (is_bool($value)) {
                      $output[$section][$key] = $value ? 'true' : 'false';
                  } else {
                      $output[$section][$key] = addcslashes($value, '"\\');
                  }
              }
          }
      } else {
          // Do not escape regular expressions
          if (preg_match('/^\/.*\/[a-z]*$/i', $values)) {
              $output[$section] = $values;
          } else {
              // Handle boolean values explicitly
              if (is_bool($values)) {
                  $output[$section] = $values ? 'true' : 'false';
              } else {
                  $output[$section] = addcslashes($values, '"\\');
              }
          }
      }
  }

  return $output;
}


function array_merge_recursive_distinct(array &$array1, array &$array2) {
  $merged = $array1;

  foreach ($array2 as $key => &$value) {
    if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
      $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
    } else {
      $merged[$key] = $value;
    }
  }

  return $merged;
}

function array_intersect_key_recursive(array $array1, array $array2) {
  $result = [];
  foreach ($array1 as $key => $value) {
    if (array_key_exists($key, $array2)) {
      if (is_array($value) && is_array($array2[$key])) {
        $result[$key] = array_intersect_key_recursive($value, $array2[$key]);
      } else {
        $result[$key] = $value;
      }
    }
  }
  return $result;
}
/*
if (!empty($env_arr = parse_ini_file($file, true, INI_SCANNER_TYPED ))) {
foreach ($env_arr as $key => $value) {

  if (is_array($value)) {
      foreach ($value as $k => $v) {
        $env_arr[$key][$k] = str_replace(['\'', '"'], '', var_export($v, true));
      }
  } else {
      // Check if the value is boolean true, and replace it with the string 'true'
      if ($value === true) {
        $env_arr[$key] = 'true';
      } else {
        $env_arr[$key] = str_replace(['\'', '"'], '', var_export($value, true));
      }
  }
}
*/

/**
 * This function is to replace PHP's extremely buggy realpath().
 * @param string The original path, can be relative etc.
 * @return string The resolved path, it might not exist.
 */
function truepath($path){
    // whether $path is unix or not
    $unipath=strlen($path)==0 || $path[0]!='/';
    // attempts to detect if path is relative in which case, add cwd
    if(strpos($path,':')===false && $unipath)
        $path=getcwd().DIRECTORY_SEPARATOR.$path;
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
    switch ('..') {
      case $part:
        array_pop($absolutes);
        break;
      default:
        $absolutes[] = $part;
        break;
    }
    }
    $path=implode(DIRECTORY_SEPARATOR, $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path = !$unipath ? "/$path" : $path;
    return $path;
}


// Snippet from PHP Share: http://www.phpshare.org

function formatSizeUnits($bytes)
{
  if ($bytes >= 1073741824)
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  elseif ($bytes >= 1048576)
    $bytes = number_format($bytes / 1048576, 2) . ' MB';
  elseif ($bytes >= 1024)
    $bytes = number_format($bytes / 1024, 2) . ' KB';
  elseif ($bytes > 1)
    $bytes = "$bytes bytes";
  elseif ($bytes == 1)
    $bytes .= ' byte';
  else
    $bytes = '0 bytes';
  return $bytes;
}

function convertToBytes($value) {
    $unit = strtolower(substr($value, -1, 1));
    return (int) $value * pow(1024, array_search($unit, [1 => 'k', 'm', 'g']));
}

/**
* Converts bytes into human readable file size.
*
* @param string $bytes
* @return string human readable file size (2,87 ??)
* @author Mogilev Arseny
* @bug Does not handle 0 bytes
*/
function FileSizeConvert($bytes)
{
  $bytes = floatval($bytes);
  $arBytes = array(
    0 => array(
      "UNIT" => "TB",
      "VALUE" => pow(1024, 4)
    ),
    1 => array(
      "UNIT" => "GB",
      "VALUE" => pow(1024, 3)
    ),
    2 => array(
      "UNIT" => "MB",
      "VALUE" => pow(1024, 2)
    ),
    3 => array(
      "UNIT" => "KB",
      "VALUE" => 1024
    ),
    4 => array(
      "UNIT" => "B",
      "VALUE" => 1
    ),
  );

  foreach($arBytes as $arItem)
  {
    if($bytes >= $arItem["VALUE"])
    {
      $result = $bytes / $arItem["VALUE"];
      $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
      break;
    }
  }
  return $result;
}

function getElementsByClass(&$parentNode, $tagName, $className) {
    $nodes= [];

    $childNodeList = $parentNode->getElementsByTagName($tagName);
    for ($i = 0; $i < $childNodeList->length; $i++) {
        $temp = $childNodeList->item($i);
        if (stripos($temp->getAttribute('class'), $className) !== false) {
            $nodes[]=$temp;
        }
    }

    return $nodes;
}
