<?php
namespace Jr;
/**
 * HelperModule is a collection of functions mostly for general php useages
 * 
 * @package     JeffPack
 * @subpackage  General PHP Libraries
 * @access      public
 * @author      Jeff Russ
 * @copyright   2016 Jeff Russ
 * @license     GPL-2.0
 */

/**
 * This function for displaying booleans as strings
 * @param  boolean $boolean a boolean variable
 * @return string either 'true' or 'false'
 */
function bool2str ($boolean) { return $boolean ? 'true' : 'false'; }

/**
 * This function for displaying octal number without converting
 * @param  integer $octal_num an octal number
 * @return string  representing decimal number
 * example
 */
function octal2str ($octal_num) { return sprintf("%04o", $octal_num); }

/**
 * This function to get string after final backslash (/)
 * @param  string  $string typically a local path or uri
 * @return string  portion after final backslash (/)
 */
function afterBSlash ($string) {return array_pop(explode('/', $string)); }

/**
 * This function that captures var_dump of array as string, trims the fat and returns string
 * @param  array   $array 
 * @return string  representing array
 */
function array2string ($array)
{
	ob_start();
	var_dump($array);
	$result = ob_get_clean();
	$deletes = array(
		'/<i>(.*?)<\/i>/',
		'/ <small>(.*?)<\/small>/',
		'/(.*?)<b>(.*?)<\/b>(.*?)\n/',
	);
	$result = preg_replace($deletes, '', $result);
	return $result; 
}

/**
 * This function converts a string (path or file) to a snake_case string
 * by
 * 1. removing ".php"
 * 2. replacing  spaces, dashes dots and backslashes with underscores 
 * 3. removing all other non-alphanumeric other than 'underscores 
 * 4. converting to all apha characters to lower-case
 * 5. if the first character(s) is a number it's removed
 * 
 * @param  string  $str example: "dir/my-plugin.php"
 * @return string  "dir/my-plugin.php" would return: "dir_my_plugin"
 */
function to_snake( $str ) 
{
	$str = preg_replace("/(.+)\.php$/", "$1", $str);
	$baddies = array(' ', '-', '.', '/');
	$str = str_replace($baddies, '_', $str);
	$str = preg_replace("/[^A-Za-z0-9_]/", '', $str );
	$str = strtolower($str);
	while ( is_numeric($str[0] ) ) $str = substr($str, 1);
	return $str;
}
/**
 * This function takes an array and returns an html string
 * It does display the contents of nested objects or nested arrays,
 * only their names (keys).
 * 
 * @param  array   $array  the array
 * @param  string  $name   the name of the array for display purposes
 * @return string  representing array with markup
 */
function summarize_array($array, $name)
{
	$objects = "<br>Objects in $name<br>";
	$arrays = "<br>Arrays in $name<br>";
	$true = "<br>True in $name<br>";
	$false = "<br>False in $name<br>";
	$nulls = "<br>NULL in $name<br>";
	$nums = "<br>Numbers in $name<br>";
	$strs = "<br>Strings in $name<br>";
	$else = "<br>others in $name<br>";
	foreach ($array as $key => $value) {
		if (is_object($value)) $objects .= " '$key',";
		elseif (is_array($value)) $arrays .= " '$key',";
		elseif (true === $value) $true .= " '$key',";
		elseif (false === $value) $false .= " '$key'";
		elseif (is_null($value)) $nulls .= " '$key',";
		elseif (is_numeric($value)) $nums .= " '$key' => $value,<br>";
		elseif (is_string($value)) $strs .= " '$key' => '$value',<br>";
		else $else += " '$key' => $value,";
	}
	return "<h2>$name</h2>".$objects . '<br>' . $arrays . '<br>' . $true . '<br>' 
		. $false . '<br>' . $nulls . '<br>' . $nums . $strs . $else;
}
/**
 * This function takes an array and returns an html string
 * It DOES display the contents nested arrays but not the contents of objects
 * or double nested arrays, etc
 * s
 * @param  array   $array  the array
 * @param  string  $name   the name of the array for display purposes
 * @param  array   $allow  (Optional) array of allowed nested arrays to display (omitting displays all)
 * @return string  representing array with markup
 */
function display_2d_array($array, $name, $allow=true)
{
	$contents = "";
	foreach ($array as $key => $value) {
		if (is_object($value))
			$contents .= "'$key' => (Object),<br>";
		elseif (is_array($value)) {
			if ($allow === true || in_array($key, $allow)) {
				$arrays .= "<h1>".$name."['".$key."']</h1>";
				foreach ($value as $inner_key => $inner_value) {
					if (is_object($inner_value)):
						$arrays .= "<b>'$inner_key' => (Object),<br>";
					elseif (is_array($inner_value)):
						$arrays .= "<b>'$inner_key' => (Array),<br>";
					else:
						$arrays .= "<b>'$inner_key'</b> => $inner_value,<br>";
					endif;
				}
			}
		} 
		else $contents .= "<b>$key</b> => $value,<br>";
	}
	return "<h1>$name</h1>".$contents.$arrays ;
}
/**
 * This function determines if argument is an Closure object
 * @param  mixed   $var of any type
 * @return boolean true if $var is a Closure object
 */
function is_closure($var) {
	return is_object($var) && ($var instanceof Closure);
}

/**
 * This function take no arguments and 
 * returns full path of WordPress home path.
 * Use when get_home_path() might not be available.
 * MUST be called from somewhere inside /wp-content
 * 
 * @return string  home path of WordPress installation
 */
function wp_home_path() { # just like get_home_path() from WP
	return substr( __FILE__, 0, strpos(__FILE__, "wp-content") );
}
/**
 * Returns true of argument 1 path (string) contains argument 2 (string).
 * Duplicate backslashes are removed before comparison is made.
 * 
 * @param  string  $path file path or url
 * @param  string  $hash path substring
 * @return bool    true or false
 */
function path_has ($path, $has) {
	$path = preg_replace('#/+#','/',$path); # turn // to /
	return (strpos($path, $has) !== false) ? true : false;
}

## UnDocumented ###############################################################

function array_string($arr,$opts='php') {
	if (is_array($opts)) $opts['depth']++;
	else {
		if (!is_string($opts)) {
			$in = $opts;
			$opts = is_integer($in) ? "json" : 'php';
			if ($in) $opts .= " pretty print";
		}
		$args = preg_split('/[^a-z0-9]/i', $opts);
		if (in_array('json', $args))
			 $opts = array('open'=>'{','close'=>'}','sep'=>': ', 'integers'=>false);
		else $opts = array('open'=>'[','close'=>']','sep'=>' => ','integers'=>true);
		if (!in_array('pretty', $args)) $opts = $opts + array('indent'=>'','eol'=>'');
		else $opts = $opts + array('indent'=>'  ','eol'=>"\n" );
		$opts['depth'] = 1; #starts at 1
		$opts['print'] = in_array('print', $args) || in_array('echo', $args) ? true:false;
	}
	end($arr); $last = key($arr);
	$result = "$opts[open]$opts[eol]";
	
	foreach($arr as $k=>$v){
		$result .= str_repeat($opts['indent'],$opts['depth']);
		if (!$opts['integers']) $result .= "\"$k\"$opts[sep]";
		else $result .= is_integer($k) ? " $k$opts[sep]" : "\"$k\"$opts[sep]";
		if    (is_array($v))   $result .= array_string($v,$opts);
		elseif(is_bool($v))    $result .= $v ? "true":"false";
		elseif(is_numeric($v)) $result .= $v;
		else                   $result .= "\"".addslashes($v)."\"";
		$result .= $last===$k ? $opts['eol'] : ", $opts[eol]";
	}
	$opts['depth']--;
	$result .= str_repeat($opts['indent'],$opts['depth']).$opts['close'];
	if ($opts['depth']===0) {
		$result .= $opts['eol'];
		if ($opts['print']) echo $result;
	}
	return $result;
}

# test is one argument is a refrence to the other
function is_ref_to(&$a, &$b) {
	$t = $a; # temporary backup of value
	$bool = ($a = $a===0 ? 1 : 0) === $b;
	$a = $t; # restore from backup value;
	return $bool;
}

function arr_getset($array) {
	$av = debug_backtrace(false, 2); $av = $av[1]['args']; $ac = count($av);
	
	if ($ac===0) return array('get', $array);
	elseif ($ac===1) { $a = $av[0];
		if (is_array($a)) return array('set', $a);
		elseif (is_scalar($a)) {
			if (array_key_exists($a, $array)) return array('get', $array[$a]);
			else return array('fail', "$a is not valid");
		}
	}
	elseif ($ac%2===0) {
		$return = array('set', 1=>array());
		for ($i=0; $i<$ac; $i+=2) $return[1][$av[$i]] = $av[$i+1];
		return $return;
	}
	else return array('fail', "Invalid Number of Arguments");
}

# Similar to both func_get_args() and merge_subarrays() only not needing the 
# first argument as it is the caller's arguments (from a function or method).
# The return is the same as merge_subarrays() and only:
# 'top' is called 'argv' and 'sub' is called 'vars'

# Unlike merge_subarrays(), argv_and_vars() will pull all indexed elements in 
# subarrays (arguments which were arrays) and push them to the end of 'argv'.
# This way, argv is integer indexed arguments and 'vars' are "named arguments"
function argv_and_vars($safe=true, $fatal=false) {
	$trace = debug_backtrace(false, 2); 
	$ret = array( 'argv'=>$trace[1]['args'], 'vars'=>array() );
	foreach ($ret['argv'] as $key=>$val) {
		if (is_array($val)) {
			unset( $ret['argv'][$key]);
			foreach ($val as $k=>$v) {
				if (!is_string($k)) $ret['argv'][] = $v;
				elseif (!isset($ret['vars'][$k])) $ret['vars'][$k] = $v;
				elseif ($fatal===false) return false;
				else { $label = is_string($fatal) ? "$fatal: " : '';
					trigger_error("${label} argv_and_vars() found duplicate keys", E_USER_ERROR);
				}
			}
		}
	}
	return $ret;
}

function log2apache() {


			/* Send the message to the Apache error log exploiting the fact that
				 mod_php maps the stderr stream to the Apache log. */
			// file_put_contents('php://stderr', $this->message, FILE_APPEND));
		}

for ($i=0; $i<=6; $i++) $php_gte["5.$i.0"] = version_compare(PHP_VERSION,"5.$i.0")>=0;
for ($i=0; $i<=4; $i++) $php_gte["7.$i.0"] = version_compare(PHP_VERSION,"7.$i.0")>=0;
$php_gte['anon func']   = $php_gte['5.3.0'];
$php_gte['short array'] = $php_gte['5.4.0'];

function php_min($ver) {
	if (isset($php_gte[$ver])) return $php_gte[$ver];
	else return version_compare(PHP_VERSION, $ver)>= 0;
}


/* define keys instead of values:
	keys('key1','key2','key2');
	// returns:    ['key1'=>0,'key2'=>2,'key2'=>2]

If arg1 is not string or numeric, it will be value:
	keys(null, 'key1','key2','key2');
	// returns:    ['key1'=>null,'key2'=>null,'key2'=>null]  */
function keys (){
	$argv = func_get_args();
	if (!is_string($argv[0]) && !is_numeric($argv[0])) {
		$val = array_shift($argv);
		return array_fill_keys($argv, $val);
	} else 
		return array_flip($argv);
}

function keys2str($arr, $str='') {
	foreach ($arr as $k=>$v) $str.="'$k', ";
	return substr($str, 0, -2);
}

/* returns an array of keys in arg1 not listed in remaining args or, 
if arg2 is an array, not matching the keys or arg2 */
function whitelist_keys($arr, $key1/*, $key2, $key3 ...*/) {
	return array_diff_key($arr, is_array($key1) 
		? $key1 : array_flip(array_slice(func_get_args(),1)));
}

function is_powof2($i) { return ($i & ($i - 1)) === 0; }

function next_powof2($i) {
	$i--;
	$i |= $i >> 1;
	$i |= $i >> 2;
	$i |= $i >> 4;
	$i |= $i >> 8;
	$i |= $i >> 16;
	return ++$i;
}

function prev_powof2($i) {
	$i = $i | ($i >> 1);
	$i = $i | ($i >> 2);
	$i = $i | ($i >> 4);
	$i = $i | ($i >> 8);
	$i = $i | ($i >> 16);
	return $i - ($i >> 1);
}

function floor_idx($arr, $i) {
	# CAUTION: you should be sure you have only integer keys!
	if ($i<1) { if ($i===0) return null; $i=abs($i); }
	for ($i=$i-1; $i>=0; --$i) {
		if (isset($arr[$i])) return $i;
	}
	return null;
}
function ceil_idx($arr, $i) {
	# note that integers are higher than character in php
	# so this effectively works as a 'ceilKey()'
	if ($i<1) $i=abs($i);
	$higest = max(array_keys($arr));
	for ($i=$i+1; $i<=$higest; ++$i) {
		if (isset($arr[$i])) return $i;
	}
	return null;
}
function max_idx($arr){ return max(array_keys($arr)); }
function min_idx($arr){ return min(array_keys($arr)); }


function var2str($var) {
	if (is_scalar($var)) {
		if ($var===true) return 'true';
		elseif ($var===false) return 'false';
		else return (string)$var;
	}
	elseif (is_array($var)) {
		$curlies = array( '}', '{' );
		$squares = array( '[', ']' );
		$string = '';
		$lines = preg_split("/((\r?\n)|(\r\n?))/",
			json_encode($var, JSON_PRETTY_PRINT));
		foreach($lines as $i=>$line){
			if (strpos($line,':')!==false) $string.="$line\n";
			elseif (trim($line)==='},' && trim($lines[$i+1])==="{")
				$string .= str_replace('}',']',$line) . "[\n";
			else $string.= str_replace($curlies,$squares,$line) . "\n";
		}
		return str_replace(': {',': [',$string);
	}
	elseif (is_object($var)) {
		if (method_exists($var, '__toString' )) return $var;
		else {var_dump($var); return;}
	} 
}
function echo_br($var) { echo nl2br($var); }

function arr2str($var, $echo=false) {
	$string = '';
	$lines = preg_split("/((\r?\n)|(\r\n?))/",
		json_encode($var, JSON_PRETTY_PRINT));
	foreach($lines as $i=>$line){
		if (strpos($line,':')!==false) $string.="$line\n";
		elseif (trim($line)==='},' && trim($lines[$i+1])==="{")
			$string .= str_replace('}',']',$line) . "[\n";
		else $string.= str_replace(['}', '{'],['[', ']'],$line) . "\n";
	}
	$string = str_replace(': {',': [',$string);
	if ($echo) echo nl2br($string);
	return $string;
}

function offsetError ($key=null) {
	extract(self::offsetError);
	if ($key!==null) $message = $message.": '$key'";
	if ($reporting==='e'): trigger_error($message, E_USER_ERROR);
	elseif ($reporting==='w'): trigger_error($message, E_USER_WARNING);
	elseif ($reporting==='n'): trigger_error($message, E_USER_NOTICE);
	endif;
	return $value;
}

function err($msg, $opts=[]) {
	$return = false;
	if (is_string($opts)) {
		if ($opts[0]==='e') $level=E_USER_ERROR;
		elseif ($opts[0]==='w') $level=E_USER_WARNING;
		elseif ($opts[0]==='n') $level=E_USER_NOTICE;
	} elseif(is_array($opts)) {
		foreach ($opts as $k => $v) {
			if (!is_string($v)) $return = $v;
			elseif ($v[0]==='e') $level=E_USER_ERROR;
			elseif ($v[0]==='w') $level=E_USER_WARNING;
			elseif ($v[0]==='n') $level=E_USER_NOTICE;
		}
	} else $return = $opts;
	if (!isset($level)) $level = E_USER_NOTICE;
	trigger_error($msg, $level);
	return $return;
}

function warn($msg)  { trigger_error($msg, E_USER_WARNING); return ''; }
function notice($msg){ trigger_error($msg, E_USER_NOTICE);  return ''; }
function error($msg) { trigger_error($msg, E_USER_ERROR);   return ''; }

## sub-arrays ###############################################################

function subarray_search ($arr, $subkey, $subval) {
	foreach ($arr as $key=>$arr) {
		foreach ($arr as $k=>$v) {
			if ($k===$subkey) { if ($v===$subval) $found[] = $key; break; }
		}
	}
	return $found;
}

function subarray_find ($arr, $subkey, $subval, $get_last=false) {
	if ($get_last) $arr = array_reverse($arr, true);
	foreach ($arr as $key=>$arr) {
		foreach ($arr as $k=>$v) {
			if ($k===$subkey && $v===$subval) return $key;
		}
	}
}


/*`````````````````````````````````````````````````````````````````````````````
	array_rotate() is like array_flip() only it's for Two-dimensional arrays.
	There can be more than Two dimensions but the value of $subkey can't be
	another array and must be a valid key. */

function array_rotate($arr, $subkey, $oldkey=null, $get_last=false) {
	$new_arr = [];
	foreach ($arr as $k=>$sub_arr) {
		$categ = $sub_arr[$subkey];
		if (!$get_last && isset($new_arr[$categ])) continue;
		unset($sub_arr[$subkey]);
		if ($oldkey===null) $sub_arr[] = $k;
		else $sub_arr[$oldkey] = $k;
		$new_arr[$categ] = $sub_arr;
	}
	return $new_arr;
}


/*`````````````````````````````````````````````````````````````````````````````
	array_rotate_category() is like array_rotate() only it avoids 
	clobbering duplicate values of $subkey by creating "category" array 
	containters for each possible value of $subkey. This additional 
	array container will be created even if there is only one sub-key 
	with a particular value (and would not have caused a collision). */

function array_rotate_category($arr, $subkey, $oldkey=null) {
	$new_arr = [];
	foreach ($arr as $k=>$sub_arr) {
		$categ = $sub_arr[$subkey];
		unset($sub_arr[$subkey]);
		if ($oldkey===null) $sub_arr[] = $k;
		else $sub_arr[$oldkey] = $k;
		$new_arr[$categ][] = $sub_arr;
	}
	return $new_arr;
}


/*`````````````````````````````````````````````````````````````````````````````
	array_rotate_jagged() is an adaptive version of array_rotate_category()
	meaning it will create a category only if a collision would occur. 
	This means that the final array returned is "jagged" meaning each 
	element might have a diffent dimensional depth (could be a single array
	or an array of arrays). */

function array_rotate_jagged($arr, $subkey, $oldkey=null) {
	$new_arr = [];
	foreach ($arr as $k=>$sub_arr) {
		$categ = $sub_arr[$subkey];
		unset($sub_arr[$subkey]);
		if ($oldkey===null) $sub_arr[] = $k;
		else $sub_arr[$oldkey] = $k;
		
		if (!isset($new_arr[$categ])) $new_arr[$categ] = $sub_arr;
		else {
			$new_arr[$categ] = array($new_arr[$categ]);
			$new_arr[$categ][] = $sub_arr;
		}
	}
	return $new_arr;
}

/*`````````````````````````````````````````````````````````````````````````````
	create_rotations() is the en masse version of array_rotate(). */

function create_rotations($arr, $prim_key_name=null, $get_last=false) {
	if ($prim_key_name===null) $sorts = [];
	else $sorts = [ $prim_key_name => $arr ];

	foreach ($arr as $pkey=>$record) {
		$record_w_pkey = $record;
		$record_w_pkey[$prim_key_name] = $pkey;
		
		foreach ($record as $field=>$value) {
				${"sorted_by_$field"} = $record_w_pkey;
				unset(${"sorted_by_$field"}[$field]);
				if (!$get_last && isset($sorts[$field][$value])) continue;
				$sorts[$field][$value] = ${"sorted_by_$field"};
		}
	}
	return $sorts;
}

/*`````````````````````````````````````````````````````````````````````````````
	create_sorts() is the en masse version of array_rotate_category().
	
	NOTE: The primary key (if provided) is still "array-ified" when the 
		lookup table sort is created for it EVEN though collisions are 
		not possible. This is so you have a universal way of dealing with 
		lookups (you will always the same depths of arrays.) In other words
		even thought there should be only one "result" if you search by 
		primary key, you will get an array with one value. */

function create_sorts($arr, $prim_key_name=null) {
	if ($prim_key_name===null) $sorts = [];
	else {
		$sorts = [ $prim_key_name => [] ];
		foreach ($arr as $k=>$v) $sorts[$prim_key_name][] = [ $k=>$v ];
	}
	foreach ($arr as $pkey=>$record) {
		$record_w_pkey = $record;
		$record_w_pkey[$prim_key_name] = $pkey;
		
		foreach ($record as $field=>$value) {
				${"sorted_by_$field"} = $record_w_pkey;
				unset(${"sorted_by_$field"}[$field]);
				$sorts[$field][$value][] = ${"sorted_by_$field"};
		}
	}
	return $sorts;
}

/*`````````````````````````````````````````````````````````````````````````````
	create_sorts_jagged() is the en masse version of array_rotate_jagged(). */

function create_sorts_jagged($arr, $prim_key_name=null) {
	if ($prim_key_name===null) $sorts = [];
	else $sorts = [ $prim_key_name => $arr ];

	foreach ($arr as $pkey=>$record) {
		$record_w_pkey = $record;
		$record_w_pkey[$prim_key_name] = $pkey;
		
		foreach ($record as $field=>$value) {
				${"sorted_by_$field"} = $record_w_pkey;
				unset(${"sorted_by_$field"}[$field]);

				if (isset($sorts[$field][$value])) {
					$sorts[$field][$value] = [ $sorts[$field][$value] ];
					$sorts[$field][$value][] = ${"sorted_by_$field"};
				} else {
					$sorts[$field][$value] = ${"sorted_by_$field"};
				}
				
		}
	}
	return $sorts;
}