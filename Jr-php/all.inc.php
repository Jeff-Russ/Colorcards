<?php

include_once 'functions.php';
include_once 'TPersistArgs.php';

if (! class_exists('scssc', false)) {
    if (version_compare(PHP_VERSION, '5.4') < 0) {
        throw new \Exception('scssphp requires PHP 5.4 or above');
    }
    include_once 'dependencies/scssphp/src/Base/Range.php';
    include_once 'dependencies/scssphp/src/Block.php';
    include_once 'dependencies/scssphp/src/Colors.php';
    include_once 'dependencies/scssphp/src/Compiler.php';
    include_once 'dependencies/scssphp/src/Compiler/Environment.php';
    include_once 'dependencies/scssphp/src/Exception/CompilerException.php';
    include_once 'dependencies/scssphp/src/Exception/ParserException.php';
    include_once 'dependencies/scssphp/src/Exception/ServerException.php';
    include_once 'dependencies/scssphp/src/Formatter.php';
    include_once 'dependencies/scssphp/src/Formatter/Compact.php';
    include_once 'dependencies/scssphp/src/Formatter/Compressed.php';
    include_once 'dependencies/scssphp/src/Formatter/Crunched.php';
    include_once 'dependencies/scssphp/src/Formatter/Debug.php';
    include_once 'dependencies/scssphp/src/Formatter/Expanded.php';
    include_once 'dependencies/scssphp/src/Formatter/Nested.php';
    include_once 'dependencies/scssphp/src/Formatter/OutputBlock.php';
    include_once 'dependencies/scssphp/src/Node.php';
    include_once 'dependencies/scssphp/src/Node/Number.php';
    include_once 'dependencies/scssphp/src/Parser.php';
    include_once 'dependencies/scssphp/src/Type.php';
    include_once 'dependencies/scssphp/src/Util.php';
    include_once 'dependencies/scssphp/src/Version.php';
    include_once 'dependencies/scssphp/src/Server.php';
}

include_once 'error-and-status/Status.php';
include_once 'error-and-status/Exc.php';
include_once 'error-and-status/To.php';
include_once 'error-and-status/test.php';

include_once 'Tree.php';
