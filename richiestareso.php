<?php
ini_set('error_log', 'C:\xampp\htdocs\Raffaele\LOG\error_richiestareso.log');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'C:\xampp\composer\vendor\autoload.php';
include 'C:\xampp\htdocs\Raffaele\connections.php';
include ('C:\xampp\htdocs\Raffaele\function.php');

$funzioni=new Funzioni();

$string2="TRUNCATE TABLE richiestareso";
$query2 = odbc_exec($conn6,$string2);


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

$csva = file_get_contents($_FILES['userfile']['tmp_name']);
$Data = str_getcsv($csva, "\n");
//array_shift($Data); //salto l'itestazione

// Credenziali
$username = $_POST['user'];
$password = $_POST['pass'];
$companyDB = $_POST['Database'];
$lingua ='13';
$Filler = $_POST['Filler'];
$cliente = $_POST['cliente'];

// URL del SAP Business One Service Layer TEST
//$baseUrl = 'https://10.54.10.116:50000/b1s/v1/';

// URL del SAP Business One Service Layer PRODUZIONE
$baseUrl = 'https://HANA2-SL:50000/b1s/v1/';


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


$string="SELECT t1.\"ItemCode\",t1.\"Quantity\" , t0.\"ObjType\",t0.\"DocEntry\",t1.\"LineNum\",Concat(t0.U_PONE_SIGLADOC,concat('|',concat(t0.\"DocNum\",concat('|',CAST(t0.\"DocDate\" AS date))))) as \"documento\",
CAST(t0.\"DocDate\" AS date) as \"DocDate\",t1.\"Price\",cast(t1.\"DiscPrcnt\" as decimal(10,2)) as \"DiscPrcnt\",cast(t1.\"LineTotal\" as decimal(10,2))as \"LineTotal\"
FROM ".$companyDB.".\"ODLN\" t0,".$companyDB.".\"DLN1\" t1 
WHERE t0.\"DocEntry\"=t1.\"DocEntry\" AND t0.\"CardCode\"='".$cliente."' and t0.\"DocEntry\" not in
(SELECT tt.U_PONE_BASEENTRY FROM ".$companyDB.".\"RRR1\" tt,".$companyDB.".\"ORRR\" tt2 where tt.\"ItemCode\"=t1.\"ItemCode\" AND t1.\"Quantity\"=tt.\"Quantity\" AND tt.U_PONE_BASEENTRY =t0.\"DocEntry\"
and tt2.\"DocEntry\"=tt.\"DocEntry\" and tt2.CANCELED<>'Y')
UNION ALL
SELECT t1.\"ItemCode\",t1.\"Quantity\" , t0.\"ObjType\",t0.\"DocEntry\",t1.\"LineNum\",Concat(t0.U_PONE_SIGLADOC,concat('|',concat(t0.\"DocNum\",concat('|',CAST(t0.\"DocDate\" AS date))))) as \"documento\",
CAST(t0.\"DocDate\" AS date) as \"DocDate\",t1.\"Price\",cast(t1.\"DiscPrcnt\" as decimal(10,2)) as \"DiscPrcnt\",cast(t1.\"LineTotal\" as decimal(10,2))as \"LineTotal\"
FROM ".$companyDB.".\"OINV\" t0,".$companyDB.".\"INV1\" t1 
WHERE t0.\"DocEntry\"=t1.\"DocEntry\" AND t0.\"CardCode\"='".$cliente."' AND t1.\"BaseType\"=17 and t0.\"DocEntry\" not in
(SELECT tt.U_PONE_BASEENTRY FROM ".$companyDB.".\"RRR1\" tt,".$companyDB.".\"ORRR\" tt2 where tt.\"ItemCode\"=t1.\"ItemCode\" AND t1.\"Quantity\"=tt.\"Quantity\" AND tt.U_PONE_BASEENTRY =t0.\"DocEntry\"
and tt2.\"DocEntry\"=tt.\"DocEntry\" and tt2.CANCELED<>'Y') order by \"DocDate\"";
$query = odbc_prepare($conn2,$string);
$success = odbc_execute($query);
while ($r = odbc_fetch_array($query))
{
    $string2="INSERT INTO richiestareso (ItemCode,Quantity, ObjType, DocEntry,LineNum,DocNum,DocDate,Price,DiscPrcnt,LineTotal) 
    VALUES('$r[ItemCode]','$r[Quantity]','$r[ObjType]','$r[DocEntry]','$r[LineNum]','$r[documento]','$r[DocDate]','$r[Price]','$r[DiscPrcnt]','$r[LineTotal]')";
    $query2 = odbc_exec($conn6,$string2);
}

$Dati = [
    "CardCode"=>$cliente,
    "DocDate" => date('Y-m-d'),
    "U_PONE_MAGA_DFLT" => $Filler, 
    "U_PONE_CAU"=>"V3",
    "U_PONE_WMS" => "Y", // Valore di default
    "U_PONE_BLOCCAEVASIONE" => "N", // Valore di default
    "U_PONE_COMMENTO_WEB" => "importato da tools massivo",
    "DocumentLines" => []
];
foreach($Data as $Row)
{
    $ricerca=strpos($Row, ";");
    $articolo= substr($Row,0,$ricerca);
    $qta= substr($Row,$ricerca+1);
    $string2=<<<SQL
    WITH CTE AS ( SELECT *, SUM(Quantity) OVER (PARTITION BY ItemCode ORDER BY DocDate, DocEntry,LineNum) AS somma_progressiva FROM richiestareso ), 
    CTE2 AS ( SELECT *, somma_progressiva - Quantity AS somma_precedente FROM CTE ) SELECT * FROM CTE2 WHERE ItemCode ='$articolo' AND somma_precedente < $qta
    SQL;
    $query2 = odbc_prepare($conn6,$string2);
    $success = odbc_execute($query2);
    $num_rows = odbc_num_rows($query2);
    if ($num_rows==0) {
        echo "Articolo: " . $articolo ." non rendibile<br>";
    }else 
        $qta_rimanente = $qta;
        while ($r = odbc_fetch_array($query2))
        {
            $qta_da_prendere = min($r["Quantity"], $qta_rimanente);
            if(strtotime($r["DocDate"])<strtotime('-1 year')){
                $iva = 'FF';
            }else {
                $iva = 'V2';
            }
            $Dati["DocumentLines"][] = [
                "ItemCode" => $r["ItemCode"],
                "Quantity" => $qta_da_prendere,
                "U_PONE_BASETYPE" => $r["ObjType"],
                "U_PONE_BASEENTRY" => $r["DocEntry"],
                "U_PONE_BASELINE" => $r["LineNum"],
                "U_PONE_RIFP" => $r["DocNum"],
                "Price" => $r["Price"],
                "DiscPrcnt" => $r["DiscPrcnt"],
                //"LineTotal" => $r["LineTotal"],
                "U_INFOBIT_RateoRisc" => 'Y',
                "U_INFOBIT_RatDataIni" => $r["DocDate"],
                "U_INFOBIT_RatDataFin" => $r["DocDate"],
                "WarehouseCode" => $Filler,
                "VatGroup" => $iva,
                    ];
            $qta_rimanente -= $qta_da_prendere;
            if ($qta_rimanente <= 0) {
                break;
            }
        }
    
}



$result = $funzioni->createrichiestareso($baseUrl, $sessionToken, $Dati);
$docentry=$result["DocEntry"];

$linea=1;
$string="SELECT DISTINCT t0.\"DocEntry\", t0.U_PONE_BASEENTRY,t1.\"DocNum\",t1.\"DocDate\" FROM ".$companyDB.".\"RRR1\" t0,".$companyDB.".\"ODLN\" t1 WHERE t1.\"DocEntry\"=t0.U_PONE_BASEENTRY and t0.\"DocEntry\" ='".$docentry."'
and t1.\"CardCode\" ='".$cliente."'";
//$query = odbc_prepare($conn3,$string);TEST
$query = odbc_prepare($conn2,$string);
$success = odbc_execute($query);
while ($r = odbc_fetch_array($query))
{
    $string2='INSERT INTO '.$companyDB.'.RRR21
    ("DocEntry", "ObjectType", "LogInstanc", "RefType", "LineNum", "RefDocEntr", "RefDocNum", "ExtDocNum", "RefObjType", "AccessKey", "IssueDate", "IssuerCNPJ", "IssuerCode", "Model", "Series", "Number", "RefAccKey", "RefAmount", "SubSeries", "Remark", "LinkRefTyp")
    VALUES('.$r["DocEntry"].', \'234000031\', 0, \'S\', '.$linea.', '.$r["U_PONE_BASEENTRY"].','.$r["DocNum"] .', \'\', \'15\', NULL,\' '.$r["DocDate"] .'\', NULL, NULL, \'0\', NULL, NULL, \'\', 0.000000, NULL, NULL, \'00\')';
    //$query2 = odbc_prepare($conn3,$string2);TEST
    $query2 = odbc_prepare($conn2,$string2);
    $success = odbc_execute($query2);
    $linea++;
}

$string="SELECT DISTINCT t0.\"DocEntry\", t0.U_PONE_BASEENTRY,t1.\"DocNum\",t1.\"DocDate\" FROM ".$companyDB.".\"RRR1\" t0,".$companyDB.".\"OINV\" t1 WHERE t1.\"DocEntry\"=t0.U_PONE_BASEENTRY and t0.\"DocEntry\" ='".$docentry."'
and t1.\"CardCode\" ='".$cliente."'";
//$query = odbc_prepare($conn3,$string);TEST
$query = odbc_prepare($conn2,$string);
$success = odbc_execute($query);
while ($r = odbc_fetch_array($query))
{
    $string2='INSERT INTO '.$companyDB.'.RRR21
    ("DocEntry", "ObjectType", "LogInstanc", "RefType", "LineNum", "RefDocEntr", "RefDocNum", "ExtDocNum", "RefObjType", "AccessKey", "IssueDate", "IssuerCNPJ", "IssuerCode", "Model", "Series", "Number", "RefAccKey", "RefAmount", "SubSeries", "Remark", "LinkRefTyp")
    VALUES('.$r["DocEntry"].', \'234000031\', 0, \'S\', '.$linea.', '.$r["U_PONE_BASEENTRY"].','.$r["DocNum"] .', \'\', \'13\', NULL,\' '.$r["DocDate"] .'\', NULL, NULL, \'0\', NULL, NULL, \'\', 0.000000, NULL, NULL, \'00\')';
    //$query2 = odbc_prepare($conn3,$string2);TEST
    $query2 = odbc_prepare($conn2,$string2);
    $success = odbc_execute($query2);
    $linea++;
}

odbc_close($conn2);
odbc_close($conn6);


echo "<br><br><input type=\"button\" value=\"Torna Indietro\" onClick=\"javascript:history.back();\" name=\"button\">";

?>
