<?php
namespace Jr;
foreach (glob("error-and-status/*.php") as $f) include_once $f; 

class Tree
	implements \Serializable, \IteratorAggregate, \ArrayAccess, \Countable
{
	///// STATIC SPACE ////////////////////////////////////////////////////////

	protected static function undefined($key) {
		trigger_error(get_class()." Undefined Offset: $key", E_USER_NOTICE);
	}
	public static $defaults = array(
		'undefined'=> array('Tree', 'undefined'),
		'delimiter'=> '/',
	);
	public static function defaults()
	{
		$r = self::arr_getset(self::$defaults, "::defaults()");
		if ($r[0]==='fail') return trigger_error($r[1], E_USER_ERROR); 
		elseif ($r[0]==='get') return $r[1];

		foreach ($r[1] as $prop => $val) {
			if ( $prop==='delimiter' && (!is_string($val) || $val==='') )
				return trigger_error("Delimiter must be a string!", E_USER_ERROR);
			elseif ($val==="default") {
				if     ($prop==='undefined') $val = array('Tree', 'undefined');
				elseif ($prop==='delimiter') $val = '/';
				else return trigger_error("$prop has no default!", E_USER_ERROR);
			}
			self::$defaults[$prop] = $val; 
		}
	}

	///// OBJECT SPACE ////////////////////////////////////////////////////////

	protected $data = array();
	protected $set; # to override and extend self::$defaults
	protected $rel = ''; # relative path as string

	public function set($a1)
	{
		$r = self::arr_getset($this->set, "->set()");
		if ($r[0]==='fail') return trigger_error($r[1], E_USER_ERROR); 
		elseif ($r[0]==='get') return $r[1];

		foreach ($r[1] as $prop => $val) {
			if ( $prop==='delimiter' && (!is_string($val) || $val==='') )
				return trigger_error("Delimiter must be a string!", E_USER_ERROR);
			if ($val==="default") $val = self::$defaults[$prop];
			$this->set[$prop] = $val; 
		}
	}

	function __construct()
	{
		$argc = func_num_args(); $argv = func_get_args();
		if ($argc===1 && is_array($argv[0])) $this->data = $argv[0]; 
		elseif ($argc>1) $this->data = $argv;
		$this->set = self::$defaults;
	}
	function serialize()  { return serialize($this->data); }
	function __toString() { return json_encode($this->data, JSON_PRETTY_PRINT);} #NO PRETTY PRINT IN PHP5.3!!
	function toArray()    { return $this->data;}
	function toJson()     { return json_encode($this->data);}
	function unserialize($serialized) { $this->data = unserialize($serialized);}
	function getIterator() { return new ArrayIterator( $this->data ); }
	function count() { return count($this->data); }
	function length(){ return count($this->data); }
	function offsetUnset($key)  { unset($this->data[$key]); }
	function offsetExists($key) { return isset($this->data["$key"]); }

	function rel($path=null) {
		if ($path===null) return $this->rel;
		if (empty($path)) return $this->rel = '';

		if (!is_array($path)) {
			$path = "$path";
			if (array_key_exists($path, $this->data)) return $this->rel = $path;

			$split = strpos($path, ($delim=$this->set['delimiter']) ) !== false;
			if ($split) $path=preg_split("[$delim]", $path, null, PREG_SPLIT_NO_EMPTY);
			else { $path = array($path); }
		}
	}

	// function __set($prop, $set) {}
	// function __get($prop) {}

	function offsetSet($key, $value, &$ar=null) # RETURN THIS OR RETURN VALUE?
	{
		if ($ar===null) $ar =& $this->data;
		echo "offsetSet called with ".gettype($key)." \$key\n";

		if ($key===null) { $ar[] = $value; return;}

		if (is_array($key)) $split = false;
		else {
			$key = To::string($key); # this will throw exception
			if (array_key_exists($k,$ar)) {$ar[$k]=$value; return;}
			$split = strpos($k, ($delim=$this->set['delimiter']) ) !== false;
			if ($split) $key=preg_split("[$delim]", $k, null, PREG_SPLIT_NO_EMPTY);
			else { $ar[$k] = $value; return $this; }
		}
		if (is_array($key) || $key instanceof Traversable) {

			end($key); $last=key($key); // reset($key); $first=key($key);

			foreach ($key as $pos=>$e) {

				if ($split) { $k = $e; $null = $k==="[ ]"; }
				else { $k = To::string($e); $null = $k==="[ ]" || $k===''; }

				if (!$null) {
					if (isset($ar[$k])) { $ar =& $ar[$k]; }

					# force through missing key by making it a new subarray:
					elseif ($pos!==$last) { $ar[$k]=array(); $ar =& $ar[$k]; }

					# add $value at deepest array at $k:
					else { $ar[$k] = $value; return $this; }
				}
				# null or '' key: push $value on deepest (last) array:
				elseif ($pos===$last) { $ar[]=$value; return $this; }

				# null or '' key: push new subarray, determine key of new $arr:
				# key/end might fail silently if not array or instance!!!
				else { $ar[]=array();end($ar);$i=key($ar); $ar =& $ar[$i]; }
			}
			$ar = $value;
			return $this;
		}
		else {
			$undef = $this->set['undefined'];
			if (!is_callable($undef)) return $undef;
			if (is_array($key)) $key = '['.implode("][",$key).']';
			return $ret;
		}
	}

	function & offsetGet($key, &$ar=null)
	{
		if ($ar===null) $ar =& $this->data;
		echo "offsetGet called with ".gettype($key)." $key\n";

		# this pushes subarray at end of array and returns it
		if ($key===null) { $ar[]=array(); end($ar); return $ar[ key($ar) ]; }

		if (is_array($key)) $split = false;
		else {
			$key = To::string($key); # this will throw exception
			if (array_key_exists($k,$ar)) return $ar[$k];
			$split = strpos($k, ($delim=$this->set['delimiter']) ) !== false;
			if ($split) $key=preg_split("[$delim]", $k, null, PREG_SPLIT_NO_EMPTY);
			else { $ar[$k]=array(); return $ar[$k]; }
		}
		if (is_array($key) || $key instanceof Traversable) {

			$ar =& $this->data;
			end($key); $last=key($key); // reset($key); $first=key($key);

			foreach ($key as $pos=>$e) {

				if ($split) { $k = $e; $null = $k==="[ ]"; }
				else { $k = To::string($e); $null = $k==="[ ]" || $k===''; }

				if (!$null) {
					if (isset($ar[$k])) { $ar =& $ar[$k]; }

					# force through missing key by making it a new subarray:
					elseif ($pos!==$last) { $ar[$k]=array(); $ar =& $ar[$k]; }

					# add $value at deepest array at $k:
					else { $ar[$k]=array(); return $ar[$k]; }
				}
				# null or '' key: push subarray on deepest (last) array:
				elseif ($pos===$last) { 
					$ar[]=array(); end($ar); return $ar[ key($ar) ];
				}
				# null or '' key: push new subarray, determine key of new $arr:
				# key/end might fail silently if not array or instance!!!
				else { $ar[]=array(); end($ar); $ar =& $ar[ key($ar) ]; }
			}
			return $ar;
		}
		else {
			$undef = $this->set['undefined'];
			if (!is_callable($undef)) return $undef;
			if (is_array($key)) $key = '['.implode("][",$key).']';
			$ret = $undef($key); # this avoids error of returning a non-variable by reference
			return $ret;
		}
	}


	// function __call($method, $argv)
	// {
	// 	if (substr($method,0,3)==='get') {
	// 		switch ($method) {
	// 			case'getLock':
	// 				return $this->lock;
	// 				break;
	// 			case'getArray': case'getArrayCopy':
	// 				return $this->data;
	// 				break;
	// 			case'getJson':
	// 				return json_encode($this->data);
	// 				break;
	// 			default: return false;
	// 		}
	// 	}
	// 	$trace = debug_backtrace()[0];
	// 	"Undefined method ".get_called_class()."'$method'";
	// 	self::_notice("Undefined property on object: '$method'");
	// }

	# Non-Public Object Methods -----------------------------------------------

	protected static function _notice($message, $trace=false) {
		$end = $trace ? "\nFile: ".$trace['file']." Line: ".$trace['line'] :'';
		trigger_error("$message ".get_called_class().$end, E_USER_NOTICE);
	}

	protected static function arr_getset($array, $func_name) {
		$trace = debug_backtrace(true, 2); $av = $trace[1]['args']; $ac = count($av);
		$fail = "Class ".get_called_class().$func_name;
		if ($ac===0) return array('get', $array);
		elseif ($ac===1) { $a = $av[0];
			if (is_array($a)) return array('set', $a);
			elseif (is_scalar($a)) {
				if (array_key_exists($a, $array)) return array('get', $array[$a]);
				else return array('fail', "$fail: '$a' is not valid");
			}
		}
		elseif ($ac%2===0) {
			$return = array('set', 1=>array());
			for ($i=0; $i<$ac; $i+=2) $return[1][$av[$i]] = $av[$i+1];
			return $return;
		}
		else return array('fail', "$fail: Invalid Number of Arguments");
	}
}

// Tree::defaults('delimiter', ', ');

// $t = new Tree(['this','that','other','a' => array()]);

// echo "assigning...\n";
// $arr = [ '8', 'this-array', 'onemore' ]; # $t['8']['this-array']['onemore']
// $ret = $t[ $arr ] = 'this.array.val';
// echo "done assigning\n";
// echo $t;
// var_dump($ret);
// // echo $t[ '0, subsub, subsubsub' ];

