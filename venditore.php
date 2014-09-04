<?php
require("functions.php");

/* verifica se l'utente e' autenticato */
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('venditori'<>user_check($login))
  die("Non sei un venditore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
 
page_start("Gestione Vendite");

/* inizia la tabella di formattazione pagina ************/
echo<<<END
<table width="100%" border="1" cellpadding="5">
<tr>
   <td width="10%" align="left" valign="top" bgcolor="$sfondoBarra" >
END;

/* barra laterale ************************************/
echo "Venditore:<br /> <b>" . get_name($login) . "</b>";
echo "<br /><br />";
echo hyperlink("Logout", "logout.php");
echo "</td><td>";

/* corpo centrale ************************************/

$IdVend=get_userID($login);	//controllo l'id del venditore

//controllo il POST
if ($_POST['submit']) {
	echo "<p> </p><b>Novita':</b><br />";
	$npezzi=$_POST['npezzi'];
    if(!ctype_digit($npezzi))
	  die("Il numero pezzi deve essere in formato numerico!<p></p><a href=venditore.php>Ritorna alla pagina precedente.</a>");
    if($npezzi==0)
      die("Il numero pezzi della commessa non può essere zero!<p></p><a href=venditore.php>Ritorna alla pagina precedente.</a>");

  $tipoCliente=$_POST['cliente'];
  if(!$tipoCliente){   //nuovo cliente
	$nome=$_POST['nome'];
	$cognome=$_POST['cognome'];
	$societa=$_POST['societa'];
	if(empty($societa))
		die("L'inserimento della Societa' e' obbligatorio per un nuovo cliente.<p></p><a href=venditore.php>Ritorna alla pagina precedente.</a>");
	$IdSocieta=inserisciCliente($nome,$cognome,$societa);
    echo " ->  Hai inserito un nuovo Cliente nel sistema: <b>".$societa."</b> <br />";
  }else{
	$IdSocieta=$_POST['cliente'];
  }
  $IdProgetto=$_POST['progetto'];
  inserisciCommessa($IdProgetto,$IdVend,$IdSocieta,$npezzi);
  echo " ->  Hai inserito correttamente una nuova commessa. <br />";
}

echo "<p> </p><b>Commesse in corso:</b>";
show_currentOrders(0); //visualizzo commesse per i venditori (0)
  
echo<<<END

 <p> </p><b>Modulo creazione nuova commessa:</b>  
<form method="post" action="venditore.php">
	<fieldset>
	<p> </p>
	<label for="cliente">Cliente:</label>
	<select name="cliente" id="cliente" size="1" >
		<option value=0>- Nuovo Cliente -</option>
END;

//prelievo della lista dei clienti
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT * FROM clienti;";
$result=mysql_query($query, $conn)
  or die("Query lettura clienti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
	$idC=$row['Id'];
	$nome=$row['Nome'];
	$cognome=$row['Cognome'];
	$nomeS=$row['Societa'];
	if(empty($nome)&&empty($cognome)){
	  echo "<option value=\"$idC\">$nomeS</option>";
	}else{
	  echo "<option value=\"$idC\">$nomeS (Rappresentante: $nome $cognome)</option>";
	}
}
		
echo<<<END
	</select>
	
	<fieldset>
    <b>Se Nuovo Cliente, specificare:</b><br />
	<label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" />
	
    <label for="cognome">Cognome:</label>
    <input type="text" id="cognome" name="cognome" />
	
	<label for="societa">Societa'(*):</label>
    <input type="text" id="societa" name="societa" />
	(*)campi obbligatori
	</fieldset>
	<p> </p>
	
	<label for="progetto">Progetto:</label>
	<select name="progetto" id="progetto" size="1" >
	
END;

//prelievo della lista dei progetti
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT Id,Descrizione FROM progetti WHERE DataCreazione IS NOT NULL;";
$result=mysql_query($query, $conn)
  or die("Query lettura progetti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
	$idP=$row['Id'];
	$desc=$row['Descrizione'];
	echo "<option value=\"$idP\">$idP - $desc</option>";
}

ECHO<<<END
	</select>
	<p> </p>
	<label for="npezzi">Numero Pezzi (>0):</label>
    <input type="numeric" id="npezzi" name="npezzi" value=1 maxlength=5 size=5 />

	<p> </p><input type="submit" name="submit" value="Aggiungi nuova Commessa" />
    
	</fieldset>
</form>

END;

echo "<p> </p><b>Commesse terminate:</b>";
show_completedOrders(0); //visualizzazione limitata per venditori (0)




/* fine tabella di formattazione *****************************/
echo "</td></tr>";
echo "</table>";

page_end();
?>