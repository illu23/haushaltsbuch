<?php 
    // Haushaltsbuch 
    // v0.01 

// database
$servername = "db";
$username = "root";
$password = "eskuhell";
$dbname = "haushalt";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

class BudgetBook {
	public $html;

    function button($type) {
        switch($type){
            case 'edit': $button= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M6.414 16L16.556 5.858l-1.414-1.414L5 14.586V16h1.414zm.829 2H3v-4.243L14.435 2.322a1 1 0 0 1 1.414 0l2.829 2.829a1 1 0 0 1 0 1.414L7.243 18zM3 20h18v2H3v-2z" fill="rgba(0,0,0,1)"/></svg>';
                break;
            case 'kill': $button= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M17 6h5v2h-2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8H2V6h5V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v3zm1 2H6v12h12V8zm-4.586 6l1.768 1.768-1.414 1.414L12 15.414l-1.768 1.768-1.414-1.414L10.586 14l-1.768-1.768 1.414-1.414L12 12.586l1.768-1.768 1.414 1.414L13.414 14zM9 4v2h6V4H9z"/></svg>';
                break;
            case 'debt_minus': $button= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-5-9h10v2H7v-2z"/></svg>';
                break;
            case 'debt_plus': $button= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M11 11V7h2v4h4v2h-4v4h-2v-4H7v-2h4zm1 11C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/></svg>';
                break;
        }
        return $button;
    }


	function overview($conn) {
	        $this->last_months(6,$conn);
            $this->table_print("now",$conn,50);
			return true;
	}
	
	function last_months($months,$conn){
			$this->html = "Ein und Ausgaben der letzten ".$months." Monate<br>";
			$this->html.="<table class='table table-striped'>\n\r";
			$this->html.="<tr><th>Datum</th><th>Einnahmen</th><th>Ausgaben</th><th>Defizit</th></tr>";
			$dateTime = new DateTime('first day of this month');
			$debt_char=array("+","-");
            for ($i_month = 1; $i_month <= $months; $i_month++) {
                $date_last6[$i_month-1] = $dateTime->format('Y-m');
                $this->html.="\n\r<tr><td><a href='?h=dm&p=".$date_last6[$i_month-1]."'>".$date_last6[$i_month-1]."</a></td><td>";
                for ($i_debt = 0;$i_debt <= 1; $i_debt++){
                    $sql = "select sum(betrag) as sum from history where datum like '".$date_last6[$i_month-1]."%' and debit='".$debt_char[$i_debt]."';";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $this->html.=$row['sum'];
                            $debit_status[$i_debt]=$row['sum'];
                        }
                    }
                    if ($i_debt<=0) $this->html.="</td><td>";
                }
                // $this->html.=$sql;
                $debit_total = $debit_status[0] - $debit_status[1];
                $this->html.="<td>".$debit_total."</td></tr>";
                $dateTime->modify('-1 month');
            }
            $this->html.="</tr></table>\n\r<br>";
	}
	
	function detail($date,$conn){
        $this->table_print($date,$conn,0);
	    return true;
	}
	
	function table_print($date,$conn,$limit){
	    $this->html.="Die letzten Transaktionen (limit ".$limit."):<br>";
		$this->html.="<table class='table table-striped'><tr>";
		$sql = "SHOW COLUMNS FROM history;";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          $i=0;
          while($row = $result->fetch_assoc()) {
            $this->html.="<th>".$row["Field"]."</th>";
            $table_colums[$i] = $row["Field"];
            $i++;
          }
        }
        $this->html.="<th>edit</th><th>kill</th>";
        $this->html.="</tr><tr>";
        $sql = "SELECT * FROM history";
        if ($date != "now"){
            $sql.=" where datum like '".$date."%'";
        }
        $sql.= " order by datum";
        if ($limit != 0) $sql.=" desc limit ".$limit;
        // $this->html.=$sql;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          while($row2 = $result->fetch_assoc()) {
              $this->html.="<tr>";
            for ($i=0;$i<=count($row2)-1;$i++){
                $this->html.="<td>".$row2[$table_colums[$i]]."</td>";    
            }
            $this->html.="<td><a href='?h=edit&p=".$row2['id']."'>";
            $this->html.=$this->button("edit");
            $this->html.="</a></td>";
            $this->html.="<td><a href='?h=kill&p=".$row2['id']."' onclick=\"return confirm('Löschen von ID:".$row2['id']."');\">";
            $this->html.=$this->button("kill");
            $this->html.="</a></td>";
            $this->html.="</tr>";
          }
        }
        $this->html.="</table>";
	    return true;
	}
	
	function entry($type,$id,$conn){
	    $t="new";
	    if ($type == "edit"){
	        $sql="select * from history where id = '".$id."'";
	        $result = $conn->query($sql);
            if ($result->num_rows > 0) {
              $i=0;
              $row = $result->fetch_assoc();
            }
            $new_date_array=explode("-", $row['datum']);
	        $new_date_correct=$new_date_array[2].".".$new_date_array[1].".".$new_date_array[0];
	        $t="edit";
	    }
	    $this->html ="Eintrag hinzufügen / bearbeiten";
	    $this->html.="<form action='?' method='get'>";
	    $this->html.="<div class='row'><div class='col-2 col-sm-1'>Datum</div>";
        $this->html.="<div class='col-2 col-sm-1'><input type='text' name='datum' value='".$new_date_correct."' id='datepicker'></div>";
        $this->html.="<div class='row'><div class='col-3 col-sm-1'>betrag</div>";
        $this->html.="<div class='col-3 col-sm-2'><input type='text' id='betrag' name='betrag' value='".$row['betrag']."'></div>";   
        $this->html.="<div class='col-3 col-sm-2'><select name='debit' id='debit'><option value='-'>-</option><option value='+'>+</option></select></div>";
        $rows_add = array('was','wo','info');
         for ($i=0;$i<=count($rows_add)-1;$i++){
	            $this->html.="<div class='w-100'></div>";
                $this->html.="<div class='col-2 col-sm-1'>".$rows_add[$i]."</div>";
                $this->html.="<div class='col-2 col-sm-1'><input type='text' id='".$rows_add[$i]."' value='".$row[$rows_add[$i]]."' name='".$rows_add[$i]."'></div>";
        }
        $this->html.="<input type='hidden' name='h' value='put'>";
        $this->html.="<input type='hidden' name='t' value='".$t."'>";
        $this->html.="<input type='hidden' name='id' value='".$row['id']."'>";
        $this->html.="<div class='w-100'></div>";
	    $this->html.="<div class='col-1 col-sm-1'><input type='submit' value='Submit'></div>";           
        $this->html.="</div>";
        $this->html.="</form>";
        return true;
	}
	
	function put($values,$conn){
	        $sql = "INSERT INTO history (datum, was, art, wo, betrag, info, debit) VALUES (";
	        $new_date_array=explode(".", $values['datum']);
	        $new_date_correct=$new_date_array[2]."-".$new_date_array[1]."-".$new_date_array[0];
	        $sql.= "'".$new_date_correct."',";
	        $sql.= "'".$values['was']."',";
	        $sql.= "'".$values['art']."',";
	        $sql.= "'".$values['wo']."',";
	        $sql.= "'".$values['betrag']."',";
	        $sql.= "'".$values['info']."',";
	        $sql.= "'".$values['debit']."');";
            $result = $conn->query($sql);
	        $this->html = "Daten eingetragen:<br>".$sql."<br>";
	        return true;
	}
	
	function kill($id,$conn){
	        $sql = "DELETE FROM `history` WHERE ((`id` = '".$id."'));";
            $result = $conn->query($sql);
	        $this->html = "Daten gelöscht:<br>".$sql."<br>";
	        return true;
	}
}
$hh = new BudgetBook();
?>
<!doctype html>
<html>
    <head>
        <title>Haushaltsbuch</title>
      <link rel="stylesheet" href="jquery-ui.css">
      <link rel="stylesheet" href="style.css">
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="jquery-3.6.0.js"></script>
      <script src="jquery-ui.js"></script>
      <script src="datepicker-de.js"></script>
      <script>
      $(function() {
       $('#datepicker').datepicker( $.datepicker.regional[ "de" ] );
      } );
      </script>
    </head>
    <body>
        <center><h1>Haushaltsbuch</h1></center>
        <div class="container text-center">
          <div class="row row-cols-auto">
            <div class="col"><a href="?h=view" class="btn btn-outline-success" role="button" aria-pressed="true">Übersicht</a></div>
            <div class="col"><a href="?h=add" class="btn btn-outline-success" role="button" aria-pressed="true">Eintrag hinzufügen</a></div>
            <div class="col"><a href="?h=stats" class="btn btn-outline-success" role="button" aria-pressed="true">Statistiken</a></div>
          </div>
        </div>
    <hr>
        
        <?php
            switch($_GET['h']) {
                case 'view':
                    $hh->overview($conn);
                    echo $hh->html;
                    break;
                case 'add':
                    $hh->entry("add",0,$conn);
                    echo $hh->html;
                    break;
                case 'put':
                    $hh->put($_GET,$conn);
                    echo $hh->html;
                    break;
                case 'kill':
                    $hh->kill($_GET['p'],$conn);
                    echo $hh->html;
                    break;
                case 'edit':
                    $hh->entry("edit",$_GET['p'],$conn);
                    echo $hh->html;
                    break;
                
                case 'dm' : 
                    $hh->detail($_GET['p'],$conn);
                    echo $hh->html;
                    break;
                default:
                    $hh->view($conn);
                    echo $hh->html;
            }
        ?>
    </body>
</html>
<?php $conn->close(); ?>
