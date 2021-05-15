<?php
namespace Zeedhi\Framework\ErrorHandler;


interface PHPErrorsInterface {
    const E_ERROR = 1;
    const E_WARNING = 2;
    const E_PARSE = 4;
    const E_NOTICE = 8;
    const E_CORE_ERROR = 16;
    const E_CORE_WARNING = 32;
    const E_COMPILE_ERROR = 64;
    const E_COMPILE_WARNING = 128;
    const E_USER_ERROR = 256;
    const E_USER_WARNING = 512;
    const E_USER_NOTICE = 1024;
    const E_ALL = 6143;
    const E_STRICT = 2048;
    const E_RECOVERABLE_ERROR = 4096;
} 