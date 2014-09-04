<?php
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('amministratori'<>user_check($login))
  die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
page_start("Amministrazione");

/* inizia la tabella di formattazione pagina ************/
echo<<<END
<table width="100%" border="1" cellpadding="5">
<tr>
   <td width="10%" align="left" valign="top" bgcolor="$sfondoBarra" >
END;

/* barra laterale ************************************/
echo "Amministratore:<br /> <b>" . get_name($login) .  "</b>";
echo "<br /><br />";
echo hyperlink("Logout", "logout.php");
echo "</td><td>";

/* corpo centrale ************************************/

  echo<<<END
  Opzioni disponibili:
  
<form method="post" action="addUser.php">
	<fieldset>
	<input type="submit" name="submit" value="Aggiungi nuovo lavoratore al DB" />
	
    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" />
	
    <label for="cognome">Cognome:</label>
    <input type="text" id="cognome" name="cognome" />
	
    <label for="categoria">Mansione:</label>
	<select name="categoria" id="categoria" size="1" >
		<option value="operai">Operaio</option>
		<option value="tecnici">Tecnico</option>
		<option value="venditori">Venditore</option>
		<option value="amministratori">Amministratore</option>
	</select>
	</fieldset>
</form>

<form method="post" action="admin.php">
	<fieldset>
		<input type="submit" name="visualizzaUtenti" value="Controlla Utenti" />
		<select name="categoria" id="categoria" size="1">
			<option value="operai">operai</option>
			<option value="tecnici">tecnici</option>
			<option value="venditori">venditori</option>
			<option value="amministratori">amministratori</option>
		</select>
		&nbsp;|&nbsp;
		<input type="submit" name="visualizzaLavorazioni" value="Controlla Stato Produzione" />
		&nbsp;|&nbsp;
		<input type="submit" name="visualizzaClienti" value="Visualizza Clienti" />
		&nbsp;|&nbsp;
		<input type="submit" name="visualizzaProgetti" value="Visualizza Progetti" />
		&nbsp;|&nbsp;
		<input type="submit" name="visualizzaComponenti" value="Visualizza Componenti" />
		&nbsp;|&nbsp;
		<input type="submit" name="visualizzaStatistiche" value="Visualizza Statistiche" />
		
	</fieldset>
</form>

END;

//controllo se c'è un post
if ($_POST['visualizzaUtenti']) {   //*********visualizza utenti

  $categoria=$_POST['categoria'];
  echo "<p> </p>Controlla utenti: <b>".$categoria."</b>";
  show_userTable($categoria);
  
}elseif ($_POST['visualizzaLavorazioni']){ //*******visualizza lavorazioni

  echo "<p> </p><b>Lavorazioni singole in corso:</b>";
  show_currentWorks();
  echo "<p> </p><b>Commesse in corso:</b>";
  show_currentOrders(1); //visualizzo commesse per gli amministratori (1)
  echo "<p> </p><b>Commesse terminate:</b>";
  show_completedOrders(1); //visualizzazione personalizzata per amministratori (1)
  
}elseif ($_POST['visualizzaClienti']){  
  echo "<p> </p><b>Lista Clienti ELAB:</b>";
  show_Clienti();
}elseif ($_POST['visualizzaProgetti']){  
  echo "<p> </p><b>Lista Progetti Elettronici:</b>";
  show_Progetti();
}elseif ($_POST['visualizzaComponenti']){
  echo "<p> </p><b>Componenti a magazzino:</b>";
  show_listaComponenti();
}else{

	//Qui inserisco le statistiche come visualizzazione di default per la pagina di ADMIN
	echo "<p> </p><b>Statistiche azienda:</b><p></p>";
	
	//QUERY3
	echo "<b>I migliori 3 operai del mese:</b>";	
	$conn = dbConnect(DBname);
	$query="SELECT Nome, Cognome,(	SELECT count(*)
									FROM produzione pp
									WHERE pp.IdOperaio=u.Id
								) as tot
			FROM utenti u JOIN produzione p ON u.Id=p.IdOperaio JOIN commesse c ON c.Id=p.IdCommessa
			WHERE DataCommessa>DATE_SUB(CURDATE(), INTERVAL 31 DAY)
			GROUP BY tot DESC,DataAssunzione DESC LIMIT 3";
	$result=mysql_query($query,$conn)
		or die("Query fallita" . mysql_error($conn));
	$titolo=array("Nome","Cognome","Schede prodotte");
	table_start($titolo);
    while ($row=mysql_fetch_array($result)){
		$out[1]=$row['Nome'];
		$out[2]=$row['Cognome'];
		$out[3]=$row['tot'];
		table_row($out);
	}
	table_end();
	
	//QUERY4
	echo "<p></p><b>Peggiori prestazioni del mese:</b>";	
	$conn = dbConnect(DBname);
	$query="SELECT DISTINCT Nome,Cognome, TempoProduzioneUnita(p.Id) as tempo
			FROM produzione p JOIN utenti u ON p.IdOperaio=u.Id
			WHERE (SELECT TIMESTAMPDIFF(HOUR,px.DataInizioAssemblaggio,px.DataFineAssemblaggio)
					FROM produzione px
					WHERE px.Id=p.Id
				   )> 72 
				  AND (p.DataInizioAssemblaggio>DATE_SUB(CURDATE(), INTERVAL 31 DAY))
			GROUP BY tempo DESC LIMIT 3";
	$result=mysql_query($query,$conn)
		or die("Query fallita" . mysql_error($conn));
	$titolo=array("Nome","Cognome","Ore per assemblare una singola scheda");
	table_start($titolo);
    while ($row=mysql_fetch_array($result)){
		$out[1]=$row['Nome'];
		$out[2]=$row['Cognome'];
		$out[3]=$row['tempo'];
		table_row($out);
	}
	table_end();
	
	//QUERY5
	echo "<p></p><b>Tecnici piu' attivi della settimana:</b>";	
	$conn = dbConnect(DBname);
	$query="SELECT Nome,Cognome, (
				SELECT count(*)
				FROM progetti px
				WHERE px.IdTecnico=u.Id AND (SELECT count(*)
											FROM progetti p JOIN listacomponentiprogetto l ON p.Id=l.IdP
											WHERE px.Id=p.Id
											)>19
  			) as nprogetti
			FROM progetti pp JOIN utenti u ON pp.IdTecnico=u.Id
			WHERE pp.DataCreazione>DATE_SUB(CURDATE(), INTERVAL 7 DAY)
			GROUP BY nprogetti DESC";
	$result=mysql_query($query,$conn)
		or die("Query fallita" . mysql_error($conn));
	$titolo=array("Nome","Cognome","Progetti Creati (con almeno 20 componenti)");
	table_start($titolo);
    while ($row=mysql_fetch_array($result)){
		$out[1]=$row['Nome'];
		$out[2]=$row['Cognome'];
		$out[3]=$row['nprogetti'];
		table_row($out);
	}
	table_end();
	
	//QUERY6
	echo "<p></p><b>Venditori piu' attivi della settimana:</b>";	
	$conn = dbConnect(DBname);
	$query="SELECT Nome,Cognome, (	SELECT count(*)
									FROM commesse cc
									WHERE cc.IdVenditore=u.Id 
										AND DataCommessa>DATE_SUB(CURDATE(), INTERVAL 7 DAY)
								 ) as numeroCommesse 
			FROM commesse c JOIN utenti u ON u.Id=c.IdVenditore
			WHERE DataCommessa>DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
				AND EXISTS (
								SELECT *
								FROM clienti
								WHERE Id=c.IdCliente AND Livello=\"new\"
							)
				AND  (
						SELECT count(*)
						FROM progetti p JOIN listacomponentiprogetto l ON p.Id=l.IdP
						WHERE c.IdProgetto=p.Id
					  )>20
			GROUP BY numeroCommesse DESC HAVING count(*)>1";
	$result=mysql_query($query,$conn)
		or die("Query fallita" . mysql_error($conn));
	$titolo=array("Nome","Cognome","Commesse Create con nuovi clienti per progetti sostanziosi");
	table_start($titolo);
    while ($row=mysql_fetch_array($result)){
		$out[1]=$row['Nome'];
		$out[2]=$row['Cognome'];
		$out[3]=$row['numeroCommesse'];
		table_row($out);
	}
	table_end();	
	
	
}
echo "<p> </p>";

/* fine tabella di formattazione *****************************/
echo "</td></tr>";
echo "</table>";

page_end();
?>