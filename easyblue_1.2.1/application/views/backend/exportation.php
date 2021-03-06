<?php

/* limit date to pick all appointments between today and the previous year */
$datelimite = date ("Y-m-d h:i:s a", mktime(0,0,0,date("m"),date("d")-365,date("Y")));

/*connexion to database*/
$conn = mysql_connect('localhost', 'root', 'M@!tre') or die(mysql_error());
mysql_select_db('easyblue2', $conn) or die(mysql_error($conn));

/*request which takes all the appointments with their month, their categories and their service*/

$query = sprintf("SELECT `ea_appointments`.`start_datetime` AS `Date`,
CASE
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '1' THEN '".$this->lang->line('january')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '2' THEN '".$this->lang->line('february')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '3' THEN '".$this->lang->line('march')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '4' THEN '".$this->lang->line('april')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '5' THEN '".$this->lang->line('may')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '6' THEN '".$this->lang->line('june')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '7' THEN '".$this->lang->line('july')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '8' THEN '".$this->lang->line('august')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '9' THEN '".$this->lang->line('september')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '10' THEN '".$this->lang->line('october')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '11' THEN '".$this->lang->line('november')."'
  WHEN MONTH( `ea_appointments`.`start_datetime` ) = '12' THEN '".$this->lang->line('december')."'
END AS `Mois`,
`ea_service_categories`.`name` AS `".$this->lang->line('categorie')."`,
`ea_services`.`name` AS `".$this->lang->line('service')."`, 
CASE 
	WHEN `ea_appointments`.`id` IS NOT NULL THEN '1'
END AS `".$this->lang->line('number')."`
FROM `easyblue2`.`ea_appointments` AS `ea_appointments`,
`easyblue2`.`ea_services` AS `ea_services`,
`easyblue2`.`ea_service_categories` AS `ea_service_categories`
WHERE `ea_appointments`.`id_services` = `ea_services`.`id`
AND `ea_services`.`id_service_categories` = `ea_service_categories`.`id`
AND `ea_appointments`.`start_datetime` >= '".$datelimite."'
ORDER BY `ea_appointments`.`start_datetime` ASC");


$result = mysql_query($query, $conn) or die(mysql_error($conn));

/*
 * send response headers to the browser
 * following headers instruct the browser to treat the data as a csv file called export.csv
 */

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment;filename="export.csv"');

/*
 * output header row (if atleast one row exists)
 */

$row = mysql_fetch_assoc($result);
if ($row) {
    echocsv(array_keys($row));
}

/*
 * output data rows (if atleast one row exists)
 */

while ($row) {
    echocsv($row);
    $row = mysql_fetch_assoc($result);
}

/*
 * echo the input array as csv data maintaining consistency with most CSV implementations
 * - uses double-quotes as enclosure when necessary
 * - uses double double-quotes to escape double-quotes
 * - uses CRLF as a line separator
 */

function echocsv($fields)
{
    $separator = '';
    foreach ($fields as $field) {
        if (preg_match('/\\r|\\n|,|"/', $field)) {
            $field = '"' . str_replace('"', '""', $field) . '"';
        }
        echo $separator . $field;
        $separator = ',';
    }
    echo "\r\n";
}
?>
