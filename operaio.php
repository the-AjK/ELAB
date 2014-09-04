<?php
require("functions.php");

/* verifica se l'utente e' autenticato */
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");
  
if('operai'<>user_check($login))
  die("Non sei un operaio e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
 
page_start("Gestione Produzione");

/* inizia la tabella di formattazione pagina ************/
echo<<<END
<table width="100%" border="1" cellpadding="5">
<tr>
   <td width="10%" align="left" valign="top" bgcolor="$sfondoBarra" >
END;

/* barra laterale ************************************/
echo "Operaio:<br /> <b>" . get_name($login) .  "</b>";
echo "<br /><br />";
echo hyperlink("Logout", "logout.php");
echo "</td><td>";

/* corpo centrale ************************************/

$IdOp=get_userID($login); //leggo l'ID operaio

//controllo il POST
if ($_POST['terminaAssemblaggio']) {
  $IdDaTerminare=$_POST['scheda'];
  terminaAssemblaggio($IdDaTerminare);
  echo "<p> </p> Bene, hai terminato l'assemblaggio correttamente. <br />";
}elseif($_POST['iniziaAssemblaggio']){
  $commessa=$_POST['commessa'];
  iniziaAssemblaggio($commessa,$IdOp);
}


//controllo lo stato dell'utente per visualizzare le informazioni corrette

if(operaioLibero($IdOp)){ //se l'utente sta assemblando una scheda


//controllo che scheda Sta assemblando e la data di inizioAssemblaggio
$dbname=DBname;
$conn = dbConnect($dbname);
  
$query= sprintf("SELECT Id, IdCommessa, DataInizioAssemblaggio
				 FROM produzione
				 WHERE IdOperaio=\"%s\" AND DataFineAssemblaggio IS NULL", 
		  $IdOp);
		  
$result=mysql_query($query, $conn)
  or die("Query controllo commessa fallita!" . mysql_error());
$output = mysql_fetch_array($result);
$scheda = $output['Id'];
$idcomm = $output['IdCommessa'];
$dataInizio = $output['DataInizioAssemblaggio'];

echo "<p> </p>Buongiorno ".get_name($login).",<br />";
echo<<<END
   Hai iniziato l'assemblaggio della scheda ID: $scheda in data $dataInizio<br />
   Relativa alla commessa ID: $idcomm
   <p> </p>
   Questi sono i dettagli della scheda utili in fase di assemblaggio:<br />
  
END;

  $pId=get_ProjectIDFromCommessa($idcomm); //leggo l'id del progetto
  echo "<p> </p><b>Progetto: ".get_ProjectNameProjectNameFromCommessa($idcomm)."</b><br />";
echo<<<END
  <p> </p>
    			 
	<SCRIPT LANGUAGE="JavaScript">
	if (window.print) {
	document.write('<form> '
	+ '<input type=button name=print value="Stampa schema e lista componenti" '
	+ 'onClick="javascript:window.print()"> </form>');
	}
	</script>
END;
  
  echo "<p> </p><b>Schema Elettrico:</b> <br />";
  
  //visualizzo lo schema se è presente
  $localdir=DIR_SCHEMI;
  $formatoImmagine=ESTENSIONE_IMMAGINE_SCHEMI;
  
  $schemaImage=$localdir."/".$pId.".".$formatoImmagine;
  if(file_exists($schemaImage)){
    echo "<img alt=\"schema elettrico progetto\" src=\"$schemaImage\" /> ";
  }else{
    echo " - Schema Elettrico non presente - ";
  }
  
echo "<p> </p><b>Lista Componenti:</b><br />";

//ora visualizzo tutti i componenti necessari per l'assemblaggio
show_listaComponentiProgetto($pId,0);	//visualizzo la tabella dei componenti del progetto

echo<<<END
  <p> </p>Una volta terminato l'assemblaggio puoi salvare il tuo stato cliccando il pulsante sottostante:
  <p> </p>
  <form method="POST" action="operaio.php">
		<input type="hidden" name="scheda" value=$scheda />
		<input type="submit" name="terminaAssemblaggio" value="Ho finto l'assemblaggio della scheda ID: $scheda" style=" height:50px; width:300px" />
</form>
<p> </p>
END;

}else{   //nel caso in cui l'operaio non sta facendo nulla

echo "<p> </p> Puoi scegliere l'assemblaggio che intendi iniziare tra quelli disponibili:";

//controllo le commesse dove posso iniziare una produzione
$dbname=DBname;
$conn = dbConnect($dbname);
  
$query="SELECT *,PezziInAssemblaggio(Id) as PezziInAssemblaggio,PezziProdotti(Id) as PezziAssemblati
        FROM commesse
		WHERE commessaTerminata(Id)=0";
$result=mysql_query($query, $conn)
  or die("Query controllo commessa fallita!" . mysql_error());

// inizia la form 
echo<<<END
<form action="operaio.php" method="post">
END;

$titolo =  array("Seleziona","Commessa","Progetto","Quantita'","in Assemblaggio","Data Ordine");
table_start($titolo);

while ($row = mysql_fetch_array($result)) {
  $out[1]=$row['Id'];
  $radio[0]="<input type=\"radio\" name=\"commessa\" value=\"$out[1]\" checked />";
  $url=base_url()."/projectInfo.php?".http_build_query(array('IdProgetto' => $row['IdProgetto'],
															 'returnTo' => 'operaio.php'));
  $out[2]=hyperlink(get_ProjectName($row['IdProgetto']), $url);
  $out[3]=$row['PezziAssemblati']."/".$row['QuantitaDaProdurre'];
  $out[4]=$row['PezziInAssemblaggio'];
  $out[5]=$row['DataCommessa'];
  table_row(array_merge($radio,$out));
}

table_end();

echo<<<END
<br />
<input type="submit" name="iniziaAssemblaggio" value="Inizia Assemblaggio" style=" height:50px; width:200px"/>
</form><p> </p>
END;


}

/* fine tabella di formattazione *****************************/
echo "</td></tr>";
echo "</table>";

page_end();
?>