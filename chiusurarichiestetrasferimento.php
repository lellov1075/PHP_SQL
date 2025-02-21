<?php
ini_set('error_log', 'C:\xampp\htdocs\Raffaele\LOG\error_chiusurarichiestatrasferimento.log');
include 'C:\xampp\htdocs\Raffaele\connections.php';
include ('C:\xampp\htdocs\Raffaele\function.php');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 10400);
ini_set('default_socket_timeout', 10400);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$funzioni=new Funzioni();
$Database = $_POST['Database'];
$Filler = $_POST['Filler'];
$towhs = $_POST['towhs'];



if (!isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    echo 'Non hai inviato nessun file...';
    exit;
}

//controllo che il file sia .csv
$ext_ok = array('csv');
$temp = explode('.', $_FILES['userfile']['name']);
$ext = end($temp);
if (!in_array($ext, $ext_ok)) {
    echo 'Il file ha un estensione non ammessa!';
    exit;
}



// Credenziali
$username = $_POST['user'];
$password = $_POST['pass'];
$companyDB = $_POST['Database'];
$lingua ='13';



// URL del SAP Business One Service Layer PRODUZIONE
$baseUrl = 'https://hana-db:50000/b1s/v1/';

// Ottieni la sessione ID e memorizzala nella sessione PHP
if (!isset($_SESSION['sessionId'])) {
    $_SESSION['sessionId'] = $funzioni->authenticateSAP($baseUrl, $username, $password, $companyDB,$lingua);
}

$sessionToken = $_SESSION['sessionId'];

if (!$sessionToken) {
    die("<b>"."Autenticazione fallita"."</b><br>"."Controllare USERNAME e PASSWORD inseriti per il login di SAP<br>"."<input type=\"button\" value=\"Torna Indietro\" onClick=\"javascript:history.back();\" name=\"button\">");
} else {
    echo "Autenticazione riuscita. Session ID: " . $sessionToken . "<br><br>";
}


//importo il file in un array
$csva = file_get_contents($_FILES['userfile']['tmp_name']);
$Data = str_getcsv($csva, "\n");
array_shift($Data); //salto l'itestazione



    
foreach($Data as $Row)
{
    $ricerca=strpos($Row, ";");
    $articolo= substr($Row,0,$ricerca);
    $qta= substr($Row,$ricerca+1);
    $string='SELECT t0."DocEntry" ,t1."DocNum",t0."LineNum",t0."LineStatus",t0."OpenQty" ,pick."AbsEntry"
    FROM '.$Database.'."WTQ1" t0
    INNER JOIN '.$Database.'."OWTQ" t1 ON t0."DocEntry" =t1."DocEntry"
    LEFT JOIN (
	   SELECT "OrderEntry","AbsEntry" FROM '.$Database.'."PKL1" p WHERE "PickStatus"=\'R\' AND "BaseObject"=\'1250000001\'
	   )pick ON pick."OrderEntry"=t0."DocEntry"
    WHERE "ItemCode" =\''.$articolo.'\'
    and "OpenQty"=\''.$qta.'\'
    AND  t1."DocStatus" =\'O\' AND t0."LineStatus" =\'O\'
    AND T1."Filler" =\''.$Filler.'\' AND T1."ToWhsCode" =\''.$towhs.'\'';
    $query = odbc_prepare($conn2,$string);
    $success = odbc_execute($query);
    while($r = odbc_fetch_array($query))
    {
        if ($r["AbsEntry"]==null){
            $ID=$r["DocEntry"];
            $result = $funzioni->CloseRequest($baseUrl, $sessionToken, $ID);
            echo "Riferimento: ".$articolo."=> Richiesta di Trasferimento n: ".$r["DocNum"] ." iteramente chiusa<br>";
        }else{echo "Riferimento: ".$articolo."=>ATTENZIONE: Richiesta di Trasferimento n: ".$r["DocNum"]." impossile chiudere, esiste lista prelievo aperta: ".$r["AbsEntry"]."<br>";}
    } 
}
    

odbc_close($conn2);
echo "<br><input type=\"button\" value=\"Torna Indietro\" onClick=\"javascript:history.back();\" name=\"button\">";

?>
