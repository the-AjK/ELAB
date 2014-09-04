<?php
//script per visualizzare i dettagli dell'utente
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('amministratori'<>user_check($login))
  die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
/* recupera i dati immessi */
$Id=$_GET['Id'];

page_start("Informazioni Commessa ID: ".$Id);

//prelievo della lista dei progetti
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT *,PezziProdotti(Id) as PezziAssemblati,DataFineUltimoLavoroCommessa(Id) as DataFineCommessa 
        FROM commesse WHERE Id=$Id;";
$result=mysql_query($query, $conn)
  or die("Query lettura commesse fallita! " . mysql_error());
$row = mysql_fetch_array($result);

echo "<p> </p>Commessa creata dal venditore ".get_UserNamefromID($row['IdVenditore'])." in data ".$row['DataCommessa'];
echo "<p></p><b>Riepilogo:</b>";
echo "<br />Commessa destinata al cliente: ".get_clientName($row['IdCliente']);
echo "<br />Progetto: ".get_ProjectNameProjectNameFromCommessa($Id);
echo "<br />Numero pezzi: ".$row['QuantitaDaProdurre'];

echo "<p></p><b>Riepilogo produzioni (".$row['PezziAssemblati']."):</b>";
show_StoriaProduzione($Id);

if($row['QuantitaDaProdurre']>$row['PezziAssemblati']){
  echo "<p> </p><b>NOTE:</b> La commessa e' ancora in fase di lavorazione";
}else{
  echo "<p> </p><b>NOTE:</b> La commessa e' stata terminata in data: ".$row['DataFineCommessa'];
}

echo "<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>";
  
page_end();
?>