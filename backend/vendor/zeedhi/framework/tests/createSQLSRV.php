<?php
error_reporting(1);

$begin = microtime();
echo "Started\n";

$serverName = "192.168.120.130";
$connectionInfo = array(
    "Database"=>"ZEEDHI_DEMO",
    "UID"=>"sa",
    "PWD"=>"Zeedh1@ds13",
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

echo "Connecting\n";
if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

$sql =
"
CREATE OR ALTER PROCEDURE procedureTest
    @param1 nvarchar(30),
    @param2 nvarchar(30) OUT
AS BEGIN
    SET @param2 = 'output value'
END
";

echo "Query: ".$sql;

$stmt = sqlsrv_prepare($conn, $sql);

echo "Creating statement\n";
if(!$stmt){
    die(print_r(sqlsrv_errors(), true));
}

echo "Executing\n";
if(sqlsrv_execute($stmt) === false){
    die(print_r(sqlsrv_errors(), true));
}

do {
    var_dump(sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC));
} while(sqlsrv_next_result($stmt));

$end = microtime();

echo "Finished! Time elapsed(".($end - $begin).").\n";