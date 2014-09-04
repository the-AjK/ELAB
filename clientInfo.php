<?php
//script per visualizzare i dettagli dell'utente
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('amministratori'<>user_check($login) && 'venditori'<>user_check($login))
  die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
/* recupera i dati immessi */
$IdCliente=$_GET['IdCliente'];
$returnTo=$_GET['returnTo'];

$societa=get_clientName($IdCliente); 

page_start("Informazioni Societa': ".$societa);

//prelievo le informazioni del progettista
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT * FROM clienti WHERE Societa=\"$societa\";";
$result=mysql_query($query, $conn)
  or die("Query lettura informazioni clienti fallita! " . mysql_error());
$output = mysql_fetch_array($result);

echo "<p> </p>Rappresentante: ".$output['Nome'].$output['Cognome'];
echo "<br />Cliente ELAB dal: ".$output['DataPrimoOrdine'];
echo "<br />Livello: <b>".$output['Livello']."</b>";
switch ($output['Livello']){
	case 'new':
		echo " (totale ordini effettuati compreso da 1 a 25)";
	break;
	case 'bronzo':
		echo " (totale ordini effettuati compreso da 26 a 50)";
	break;
	case 'argento':
		echo " (totale ordini effettuati compreso da 51 a 100)";
	break;
	case 'oro':
		echo " (piu' di 100 ordini effettuati ;) )";
	break;
}
echo "<p></p><a href=\"$returnTo\">Ritorna alla pagina precedente.</a>";
  
page_end();
?>