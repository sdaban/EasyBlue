<?php

/* limit date to pick all appointments between today and the previous year */
$datelimite = date ("Y-m-d h:i:s a", mktime(0,0,0,date("m"),date("d")-365,date("Y")));

/*connexion to the database */
$conn = mysql_connect('localhost', 'root', 'M@!tre') or die(mysql_error());
mysql_select_db('easyblue2', $conn) or die(mysql_error($conn));

/*request to have the months*/
$query = "SELECT `name` AS `MOIS`
FROM `ea_mois`";

/*request to have the names of services*/
$queryservicename = "SELECT `name`
FROM `ea_services` ";

/*request to have the id of services*/
$queryserviceid = "SELECT `id` 
FROM `ea_services` ";

/*request to have the number of appointments by month*/
$querytotalbymonth = "SELECT `ea_mois`.`id`, `ea_mois`.`name`, 
COUNT( `ea_appointments`.`start_datetime` ) 
FROM { oj `easyblue2`.`ea_mois` AS `ea_mois` 
LEFT OUTER JOIN ( SELECT `easyblue2`.`ea_appointments`.* FROM `easyblue2`.`ea_appointments` WHERE  `ea_appointments`.`start_datetime` >= '".$datelimite."') AS `ea_appointments` 
ON `ea_mois`.`id` = MONTH(`ea_appointments`.`start_datetime`) } 
GROUP BY `ea_mois`.`id` 
ORDER BY `ea_mois`.`id` ASC";

/*request to have the name of categories*/
$querynamecategories = "SELECT `name` FROM `easyblue2`.`ea_service_categories` AS `ea_service_categories`";




$result = mysql_query($query, $conn) or die(mysql_error($conn));
$row = mysql_fetch_row($result);

?>

    <!-- Construction table of appointments -->
    
<div>
<table id="rendez-vous" style="width: 95%;padding-left: 3px; margin: auto;border:1px solid #1E6A40;">
 	<div id="titre" style="font-size: 18px;margin-left: 25%;;padding-bottom: 15px;"><?php echo $this->lang->line('table_of_appointments'); ?></div>
  		<thead>
	       <?php
         echo "<th style='padding-left: 5px;border:1px solid #1E6A40;width: 300px;'>".$this->lang->line('services')." </th>";
         while($row) {
          echo "<th style='text-align: center;border:1px solid #1E6A40;width: 6%'>".$this->lang->line($row[0])."</th>" ;
          $row = mysql_fetch_row($result);
         }
          echo "<th style='text-align: center;border:1px solid #1E6A40;width : 4%'> ".$this->lang->line('total')." </th>" ; ?>
		</thead>
  <tbody>
    <?php
      $tablenamecategories = mysql_query($querynamecategories, $conn) or die(mysql_error($conn));
      $tabletotalbymonth = mysql_query($querytotalbymonth, $conn) or die(mysql_error($conn));
    	$tableservicename = mysql_query($queryservicename, $conn) or die(mysql_error($conn));
    	$tableserviceid = mysql_query($queryserviceid, $conn) or die(mysql_error($conn));
		$rownamecategories = mysql_fetch_row($tablenamecategories);
    	$rowtotalbymonth = mysql_fetch_row($tabletotalbymonth);
    	$rowservicename = mysql_fetch_row($tableservicename);
    	$rowserviceid = mysql_fetch_row($tableserviceid);
    	
    	/* Display name of categories */
    	while($rowservicename) {
    		$total = 0;
 			$nomcategorie = "";
    		if($nomcategorie != $rownamecategories[0]) {
    			echo "<tr style='border-top:1px solid #1E6A40;border-bottom:1px solid #1E6A40;background-color:#B2FFD0'>";
    			echo "<td  colspan=14 style='text-align: left;padding-left: 5px;border-right:1px solid #1E6A40; font-weight: bold;'> $rownamecategories[0] </td>";
    		
    			echo "</tr>";
    			$nomcategorie = $rownamecategories[0];
    			$rownamecategories = mysql_fetch_row($tablenamecategories);
    		}
    		echo "<tr >";
    		echo "<td style='text-align: left;padding-left: 30px;border-right:1px solid #1E6A40;'> $rowservicename[0] </td>";
    		
    		/*request which take the number of appointments by month for each services */
    		
    		$querypourservice = "SELECT `ea_mois`.`id`, `ea_mois`.`name`, 
			COUNT( `ea_appointments`.`start_datetime` ), `ea_services`.`name` 
			FROM { oj ( SELECT `easyblue2`.`ea_appointments`.* FROM `easyblue2`.`ea_appointments` WHERE  `ea_appointments`.`start_datetime` >= '".$datelimite."') AS `ea_appointments`
			RIGHT OUTER JOIN `easyblue2`.`ea_mois` AS `ea_mois` 
			ON MONTH(`ea_appointments`.`start_datetime`) = `ea_mois`.`id` AND `ea_appointments`.`id_services` = '$rowserviceid[0]' 
			LEFT OUTER JOIN `easyblue`.`ea_services` AS `ea_services` 
			ON `ea_appointments`.`id_services` = `ea_services`.`id` AND `ea_services`.`id`='$rowserviceid[0]'  } 
			GROUP BY `ea_mois`.`name`, `ea_services`.`name` 
			ORDER BY `ea_mois`.`id` ASC";

    		$resultatrequete = mysql_query($querypourservice, $conn) or die(mysql_error($conn));
    		$row = mysql_fetch_row($resultatrequete);
    		
    		/* Display the number of appointments by month for each services */
    		while($row) {
      		$total = $total + $row[2];
      		echo "<td style='text-align: center;border-left:1px solid #1E6A40;border-right:1px solid #1E6A40;'> $row[2]</td>" ;
      		$row = mysql_fetch_row($resultatrequete);
    		}
    		
    		/* Display the total of appointments by services */
    		echo "<td style='text-align: center;border-left:1px solid #1E6A40;font-weight: bold;'> $total </td>";
    		$rowservicename = mysql_fetch_row($tableservicename);
			$rowserviceid = mysql_fetch_row($tableserviceid);
			echo "</tr>";
			}
					
		$total2 = 0;
		echo "<tr>";
		echo "<td style='padding-left: 5px;border:1px solid #1E6A40;font-weight: bold;'> ".$this->lang->line('total')." </td>";
	   
	   /* Display the number of appointments by month */
	   while($rowtotalbymonth) {
      	echo "<td style='text-align: center;border:1px solid #1E6A40;font-weight: bold;'> $rowtotalbymonth[2]</td>" ;
         $total2 = $total2 + $rowtotalbymonth[2];
         $rowtotalbymonth = mysql_fetch_row($tabletotalbymonth);
         }
         
         /* Display the total number of appointments in the year*/
      echo "<td style='text-align: center;border:1px solid #1E6A40;font-weight: bold;'> $total2 </td>" ; ?>	
  </tbody>
  </table>
  </div>
  
  		<!-- link which gives you the possibility to upload a csv file which containts the list of all the appointments took between today and the previous year -->
 <div style="margin-left:43%;padding-top: 5px; font-size: 16px;">
 	<a href="<?php echo site_url('backend/exportation'); ?>" class="menu-item">                      
   	<?php echo $this->lang->line('export_list_of_appointments'); ?>
   </a>
 </div>
