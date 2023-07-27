<?php

function dd(mixed $param = null) { //?string $param != work when missing argv
  echo '<pre><code>';
  var_dump($param); // var_export($param)
  print '</code></pre>'; // get_defined_constants(true)['user']'
  return die();
}


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
* @param   String   $base   A base path used to construct relative path. For example /website
* @param   String   $path   A full path to file or directory used to construct relative path. For example /website/store/library.php
* 
* @return  String
*/
function getRelativePath($base, $path) {
  // Detect directory separator
  $separator = substr($base, 0, 1);
  $base = array_slice(explode($separator, rtrim($base,$separator)),1);
  $path = array_slice(explode($separator, rtrim($path,$separator)),1);

  return $separator.implode($separator, array_slice($path, count($base)));
}


function readlinkToEnd($linkFilename) {
  if(!is_link($linkFilename)) return $linkFilename;
  $final = $linkFilename;
  while(true) {
    $target = readlink($final);
    if(substr($target, 0, 1)=='/') $final = $target;
    else $final = dirname($final).'/'.$target;
    if(substr($final, 0, 2)=='./') $final = substr($final, 2);
    if(!is_link($final)) return $final;
  }
}

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
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path=implode(DIRECTORY_SEPARATOR, $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path=!$unipath ? '/'.$path : $path;
    return $path;
}


function getElementsByClass(&$parentNode, $tagName, $className) {
    $nodes=array();

    $childNodeList = $parentNode->getElementsByTagName($tagName);
    for ($i = 0; $i < $childNodeList->length; $i++) {
        $temp = $childNodeList->item($i);
        if (stripos($temp->getAttribute('class'), $className) !== false) {
            $nodes[]=$temp;
        }
    }

    return $nodes;
}
