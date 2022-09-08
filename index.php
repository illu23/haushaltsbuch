<?php 
    // Haushaltsbuch 
    // v0.01 

// database
$servername = "db";
$username = "db";
$password = "changeme";
$dbname = "haushalt";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

class BudgetBook {
	public $html;
	function view($conn) {
			$this->html = "Ein und Ausgaben der letzten 6 Monate<br>";
			$this->html.="<table class='table table-striped'>\n\r";
			$this->html.="<tr><th>Datum</th><th>Einnahmen</th><th>Ausgaben</th><th>Defizit</th></tr>";
			$dateTime = new DateTime('first day of this month');
			$debt_char=array("+","-");
            for ($i_month = 1; $i_month <= 6; $i_month++) {
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
            $this->html.="</tr></table>\n\r<br><br>";
            $this->table_print("now",$conn,50);
			return true;
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
            $this->html.="<td><a href='?h=kill&p=".$row2['id']."'><i class='bi bi-x-circle'></i></a></td>";
            $this->html.="</tr>";
          }
        }
        $this->html.="</table>";
	    return true;
	}
	
	function add(){
	        $this->html = "Eintrag hinzufügen<br>";
	        $rows_add = array('datum','was','art','wo','betrag','info');
	        $this->html.= "<table border='1'> <tr>";
	        for ($i=0;$i<=count($rows_add)-1;$i++){
	            $this->html.="<td>".$rows_add[$i]."</td>";
            }
            $this->html.="<td>+/-</td>";
	        $this->html.= "<form action='?' method='get'>";
            $this->html.= "</tr><tr>";
            for ($i=0;$i<=count($rows_add)-1;$i++){
                if ($rows_add[$i]=='datum'){
	                $this->html.="<td><input type='text' name='datum' id='datepicker'></td>";
	            } else {
                    $this->html.="<td> <input type='text' id='".$rows_add[$i]."' name='".$rows_add[$i]."'></td>";
	            }
            }
            $this->html.="<input type='hidden' name='h' value='put'>";
            $this->html.="<td><select name='debit' id='debit'><option value='-'>-</option><option value='+'>+</option></select></td>";
	        $this->html.= "</tr><tr><td colspan='".count($rows_add)."'><input type='submit' value='Submit'></td></tr></form></table>";
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
                    $hh->view($conn);
                    echo $hh->html;
                    break;
                case 'add':
                    $hh->add();
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
