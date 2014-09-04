<?php
//script per visualizzare i dettagli dell'utente
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

//if('amministratori'<>user_check($login) && 'operai'<>user_check($login))
  //die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
/* recupera i dati immessi */
$IdProgetto=$_GET['IdProgetto'];
$returnTo=$_GET['returnTo'];

$descrizioneProgetto=get_ProjectName($IdProgetto); //leggo la descrizione del progetto

page_start("Informazioni Progetto: ".$descrizioneProgetto);

//prelievo le informazioni del progettista
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT * FROM progetti WHERE Id=\"$IdProgetto\";";
$result=mysql_query($query, $conn)
  or die("Query lettura informazioni progettista fallita! " . mysql_error());
$output = mysql_fetch_array($result);
$nomeTecnico=get_UserNamefromID($output['IdTecnico']);
$dataCreazioneProgetto=$output['DataCreazione'];
$nomeTecnicoMOD=get_UserNamefromID($output['IdTecnicoMOD']);
$dataUltimaModifica=$output['DataUltimaModifica'];

echo "Creato da: <b>".$nomeTecnico."</b><br />
	  in data: ".$dataCreazioneProgetto."<p></p>";

if($dataUltimaModifica)
echo "Ultima modifica: ".$dataUltimaModifica." - ".$nomeTecnicoMOD."<p></p>";
	  
//visualizzo lo schema se è presente
$localdir=DIR_SCHEMI;
$formatoImmagine=ESTENSIONE_IMMAGINE_SCHEMI;
  
$schemaImage=$localdir."/".$IdProgetto.".".$formatoImmagine;
if(file_exists($schemaImage)){
  echo "<b>Schema Elettrico:</b><br />";
  echo "<img alt=\"schema elettrico progetto\" src=\"$schemaImage\" /> ";
}else{
  echo " - Schema Elettrico non presente - ";
}

echo "<p> </p><b>Lista Componenti:</b><br />";

show_listaComponentiProgetto($IdProgetto,0);		 //visualizzo la tabella dei componenti del progetto



echo "<p></p><a href=\"$returnTo\">Ritorna alla pagina precedente.</a>";
  
page_end();
?>