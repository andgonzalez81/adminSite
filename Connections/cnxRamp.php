<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_cnxRamp = "localhost";
$database_cnxRamp = "lfg";
$username_cnxRamp = "root";
$password_cnxRamp = "root";

//$hostname_cnxRamp = "localhost";
//$database_cnxRamp = "rampmood_lfg";
//$username_cnxRamp = "rampmood_wsuser";
//$password_cnxRamp = "sP-~,0hQ8dP=";

$cnxRamp = mysql_pconnect($hostname_cnxRamp, $username_cnxRamp, $password_cnxRamp) or trigger_error(mysql_error(),E_USER_ERROR); 
?>
