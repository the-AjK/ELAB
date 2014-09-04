<?php
/******************************************************************************
|
|  functions.php
|
|  Descrizione: funzioni generali per pagine HTML e DB mySQL e progetto ELAB
|
\****************************************************************************/

//Configuro la visualizzazione degli errori di PHP
error_reporting(E_ALL & ~E_NOTICE);

//Utilizzo cookies (NON IMPLEMENTATA COMPLETAMENTE -> lasciare a ZERO)
define(USE_COOKIES,0);
//chiave segreta per i cookies
$chiaveSegreta="ELAB2013";

//Definizioni
define(DIM_MAX,250000);  					//dimensione massima di upload
define(DIR_SCHEMI,"schemiProgetti");  		//directory dove vengono salvati gli schemi elettronici
define(ESTENSIONE_IMMAGINE_SCHEMI,"png");  	//estensione delle immagini degli schemi

define(DBuser,"utenteDB");					//username basidati.unipd.it
define(DBpass,"inputUrPasswordHere");		//password accesso al DB

define(DBname,"agarbui-PR");				//nome database basidati.unipd.it

//sfondo barra laterale sinistra
$sfondoBarra="CornflowerBlue";

/****************************************************************************\
| Funzione: loginPage_start
| Descrizione: inizia la pagina di login
\****************************************************************************/
function loginPage_start() {
  echo<<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="it" lang="it">

<head><title>Laboratorio Elettronico - Login</title></head>
<body>
<br />
END;
}

/****************************************************************************\
| Funzione: page_start
| Descrizione: inizia una pagina impostando il titolo
\****************************************************************************/
function page_start($title) {
$backgroundURL=base_url()."/images/sfondo.jpg";
  echo<<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="it" lang="it">

<head>
  <title>$title</title>
  <style> 
    body {background-image: url(images/sfondo.png);
	      background-repeat: no-repeat;
		  background-position: 8px 8px;
		  background-size: 99% 66px;
		 }
 </style>
</head>

<body >
<hr />
<div style="position:relative; right:8px;">
<img style="float:right" alt="Electric LAB logo" src="images/logoSmall.png" />
</div>
<h2>&nbsp;&nbsp;<span style="color:black;">$title</span> </h2>
<hr />
END;
}

/****************************************************************************\
| Funzione: page_end
| Descrizione: termina la pagina
\****************************************************************************/
function page_end() {
  echo "
</body>
</html>";
};

/****************************************************************************\
| Funzione: subtitle
| Descrizione: inserisce un sottotitolo
\****************************************************************************/
function subtitle($str) {
  echo "<h3>$str</h3>\n";
};

/****************************************************************************\
| Funzione: hyperlink
| Descrizione: ritorna un link associato ad una URL
\****************************************************************************/
function hyperlink($str, $url) {
  return "<a href=\"$url\">$str</a>";
};

/****************************************************************************\
| Funzione: table_start
| Descrizione: Funzione per iniziare una tabella html. In input l'array che
|               contiene gli header delle colonne 
\****************************************************************************/
function table_start($row) {
  echo "<table border=\"1\">\n";
  echo "<tr>\n";
  foreach ($row as $field) 
    echo "<th>$field</th>\n";
  echo "</tr>\n";
};
  
/****************************************************************************\
| Funzione: table_row
| Descrizione: stampa una riga di tabella HTML utilizzando l'array passato
\****************************************************************************/
function table_row($row) {
  echo "<tr>";
  foreach ($row as $field) 
    if ($field)
      echo "<td>$field</td>\n";
    else
      echo "<td>---</td>\n";
  echo "</tr>";
  };

/****************************************************************************\
| Funzione: table_end
| Descrizione: chiude una tabella
\****************************************************************************/
function table_end() {
  echo "</table>\n";
};

/****************************************************************************\
| Funzione: dbConnect
| Descrizione: si connette al database
\****************************************************************************/
function dbConnect($dbname) {

  $server="localhost";
  $username=DBuser;		//username progetto
  $password=DBpass;		//password
  
  $conn=mysql_connect($server,$username,$password)
    or die("Impossibile connettersi!");

  mysql_select_db($dbname,$conn);

  return $conn;
}

/****************************************************************************\
| Funzione: operaioLibero
| Descrizione: restituisce 1 se l'operaio è libero di iniziare un nuovo lavoro
\****************************************************************************/
function operaioLibero($Id){
  
  $dbname=DBname;
  $conn = dbConnect($dbname);

  //controllo se l'utente Id sta facendo più di zero assemblaggi
  $query= sprintf("SELECT UtenteInAssemblaggio(\"%s\",0) as inAssemblaggio", 
		  $Id);
  
  $result=mysql_query($query,$conn)
    or die("Query controllo utente in assemblaggio fallita" . mysql_error($conn));
  $output=mysql_fetch_array($result);
  return $output['inAssemblaggio'];
}

/****************************************************************************\
| Funzione: show_userTable
| Descrizione: visualizza una tabella HTML con i dati degli utenti di una certa categoria
\****************************************************************************/
function show_userTable($categoria){
  
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Id,Nome,Cognome,DataAssunzione,Username FROM utenti WHERE Categoria=\"%s\"", 
		  $categoria);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
	
  $titolo=array(" ","Nome","Cognome","Data Assunzione","Username");
  table_start($titolo);
    while ($row=mysql_fetch_array($result)){
   
	//if($categoria=='operai'){
      $url=base_url()."/userInfo.php" . "?Id=" . urlencode($row['Id']);
      $out[0]=hyperlink("Licenzia", $url); 
	/*}else{
      $out[0]=$row['Id'];
	}*/
    $out[1]=$row['Nome'];
    $out[2]=$row['Cognome'];
    $out[3]=$row['DataAssunzione'];
	$out[4]=$row['Username'];
    table_row($out);
	}
	table_end();
}

/****************************************************************************\
| Funzione: show_Clienti
| Descrizione: visualizza una tabella HTML con i dati dei clienti
\****************************************************************************/
function show_Clienti(){
  
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query="SELECT * FROM clienti;";
  $result=mysql_query($query,$conn)
    or die("Query lettura clienti per Admin fallita" . mysql_error($conn));
	
  $titolo=array("Id","Societa'","Rappresentante","Data Primo Ordine");
  table_start($titolo);
  while ($row=mysql_fetch_array($result)){
	$out[0]=$row['Id'];
	$url=base_url()."/clientInfo.php?".http_build_query(array('IdCliente' => $row['Id'],
															   'returnTo' => 'admin.php'));
	$out[1]=hyperlink($row['Societa'], $url);
	$out[2]=$row['Nome']." ".$row['Cognome'];
   	$out[3]=$row['DataPrimoOrdine'];
    table_row($out);
  }
  table_end();
}

/****************************************************************************\
| Funzione: show_Progetti
| Descrizione: visualizza una tabella HTML con tutti i progetti del DB
\****************************************************************************/
function show_Progetti(){
  
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query="SELECT * FROM progetti;";
  $result=mysql_query($query,$conn)
    or die("Query lettura progetti per Admin fallita" . mysql_error($conn));
	
  $titolo=array("Id","Upgrade di","Descrizione","Creato da","Ultima Modifica");
  table_start($titolo);
  while ($row=mysql_fetch_array($result)){
	$out[0]=$row['Id'];
   	$out[1]=$row['IdUpgrade'];
    //visualizzo il nome del progetto con relativo link al dettaglio	
	$url=base_url()."/projectInfo.php?".http_build_query(array('IdProgetto' => $row['Id'],
															   'returnTo' => 'admin.php'));
	$out[2]=hyperlink($row['Descrizione'], $url);
    $out[3]=get_UserNamefromID($row['IdTecnico'])." il ".$row['DataCreazione'];
	if($row['IdTecnicoMOD']){
	  $out[4]=get_UserNamefromID($row['IdTecnicoMOD'])." il ".$row['DataUltimaModifica'];
	}else{
	  $out[4]="- Nessuna Modifica -";
	}
    table_row($out);
  }
  table_end();
}

/****************************************************************************\
| Funzione: get_ProjectName
| Descrizione: restituisce il nome del progetto con id specificato
\****************************************************************************/
function get_ProjectName($IDpr) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Descrizione FROM progetti WHERE Id=\"%s\"", 
		  $IDpr);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Descrizione'];
}

/****************************************************************************\
| Funzione: get_lastModUserfromProjectID
| Descrizione: restituisce il nome dell'autore dell'ultima modifica al progetto con id specificato
\****************************************************************************/
function get_lastModUserfromProjectID($IDpr) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Nome,Cognome 
                   FROM progetti p JOIN utenti u ON p.IdTecnicoMOD=u.Id
				   WHERE p.Id=\"%s\"", 
		  $IDpr);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  if($output){
    return $output['Nome']." ".$output['Cognome'];
  }else{
    return FALSE;
  }
}

/****************************************************************************\
| Funzione: get_UserNamefromProjectID
| Descrizione: restituisce il nome dell'autore del progetto con id specificato
\****************************************************************************/
function get_UserNamefromProjectID($IDpr) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Nome,Cognome 
                   FROM progetti p JOIN utenti u ON p.IdTecnico=u.Id
				   WHERE p.Id=\"%s\"", 
		  $IDpr);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Nome']." ".$output['Cognome'];
}

/****************************************************************************\
| Funzione: get_ProjectNameFromCommessa
| Descrizione: restituisce il nome del progetto relativo ad una certa commessa
\****************************************************************************/
function get_ProjectNameProjectNameFromCommessa($IDcomm) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Descrizione FROM progetti p join commesse c ON c.IdProgetto=p.Id WHERE c.Id=\"%s\"", 
		  $IDcomm);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Descrizione'];
}

/****************************************************************************\
| Funzione: projectComponentAlreadyEXIST
| Descrizione: restituisce 1 se il progetto indicato contiene già il componente
\****************************************************************************/
function projectComponentAlreadyEXIST($IdPr,$IdComp) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT count(*) as esiste FROM listacomponentiprogetto WHERE IdP=\"%s\" AND IdC=\"%s\"", 
		  $IdPr,$IdComp);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['esiste'];
}

/****************************************************************************\
| Funzione: ComponentAlreadyEXIST
| Descrizione: restituisce 1 se il componente con l'ID indicato è presente nel DB
\****************************************************************************/
function ComponentAlreadyEXIST($IdComp) {

  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("SELECT count(*) as esiste FROM componenti WHERE Id=\"%s\"", 
		  $IdComp);
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  return $output['esiste'];
}

/****************************************************************************\
| Funzione: ISinProgettazione
| Descrizione: restituisce 1 se il progetto indicato è "in progettazione"
\****************************************************************************/
function ISinProgettazione($IdPro) {
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("SELECT count(*) as esiste FROM progetti WHERE Id=\"%s\" AND DataCreazione IS NULL", 
		  $IdPro);
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  return $output['esiste'];
}

/****************************************************************************\
| Funzione: clonaComponentiProgetto
| Descrizione: esegue una copia dei componenti di un progetto per iniziare la creazione
|              del progetto upgrade
\****************************************************************************/
function clonaComponentiProgetto($idProg,$newID) {
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("INSERT INTO listacomponentiprogetto
				   SELECT \"%s\",IdC,NumeroPezzi
				   FROM listacomponentiprogetto 
				   WHERE IdP=\"%s\"", 
		  $newID,$idProg);
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));  
}

/****************************************************************************\
| Funzione: get_ProjectIDFromCommessa
| Descrizione: restituisce l'ID del progetto relativo ad una certa commessa
\****************************************************************************/
function get_ProjectIDFromCommessa($IDcomm) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT p.Id FROM progetti p join commesse c ON c.IdProgetto=p.Id WHERE c.Id=\"%s\"", 
		  $IDcomm);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Id'];
}

/****************************************************************************\
| Funzione: get_clientName
| Descrizione: restituisce il nome del cliente con id specificato
\****************************************************************************/
function get_clientName($ID) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Societa FROM clienti WHERE Id=\"%s\"", 
		  $ID);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Societa'];
}

/****************************************************************************\
| Funzione: get_UserNamefromID
| Descrizione: restituisce nome e cognome dell'utente con id specificato
\****************************************************************************/
function get_UserNamefromID($ID) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Nome,Cognome FROM utenti WHERE Id=\"%s\"", 
		  $ID);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  return $output['Nome']." ".$output['Cognome'];
}

/****************************************************************************\
| Funzione: progettiIncompleti
| Descrizione: controlla se il tecnico ha progetti incompleti
\****************************************************************************/
function progettiIncompleti($IdTec) {

  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("SELECT count(*) as esiste FROM progetti WHERE IdTecnico=\"%s\" AND DataCreazione IS NULL", 
		  $IdTec);
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  return $output['esiste'];
}

/****************************************************************************\
| Funzione: show_currentWorks
| Descrizione: visualizza le lavorazioni correnti
\****************************************************************************/
function show_currentWorks(){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query="SELECT c.Id as Idc,p.Id,c.IdProgetto,p.IdOperaio,p.DataInizioAssemblaggio,c.IdCliente
          FROM produzione p JOIN commesse c ON c.Id=p.IdCommessa
		  WHERE DataFineAssemblaggio IS NULL";
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
	
  $titolo=array("Id Commessa","Progetto","Operaio","Data inizio assemblaggio");
  table_start($titolo);
    while ($row=mysql_fetch_array($result)){
   
	$out[0]=$row['Idc'];	
	
	//visualizzo il nome del progetto con relativo link al dettaglio	
	$url=base_url()."/projectInfo.php?".http_build_query(array('IdProgetto' => $row['IdProgetto'],
															   'returnTo' => 'admin.php'));
	$out[1]=hyperlink(get_ProjectName($row['IdProgetto']), $url);
	
	//Mostro il nome dell'operaio 
	$out[2]=get_UserNamefromID($row['IdOperaio']); 	
    $out[3]=$row['DataInizioAssemblaggio'];
	
    table_row($out);
	}
	table_end();

}

/****************************************************************************\
| Funzione: show_listaComponentiProgetto
| Descrizione: visualizza la lista dei componenti di un determinato progetto
|			   se showDeleteButtons=1 visualizza anche i pulsanti (x il tecnico)
\****************************************************************************/
function show_listaComponentiProgetto($IdPr,$showDeleteButtons){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("SELECT c.*, comp.NumeroPezzi
                   FROM componenti c, progetti p JOIN listacomponentiprogetto comp ON p.Id=comp.IdP
                   WHERE c.Id=comp.IdC AND p.Id=\"%s\"",	
		  $IdPr);
  $result=mysql_query($query, $conn)
    or die("Query lettura componenti progetto fallita! " . mysql_error());
  $titolo=array("Id componente","Sigla Produttore","Descrizione","Numero Pezzi");
  table_start($titolo);

    while ($row=mysql_fetch_array($result)){
   	  $out[0]=$row['Id'];
	  $out[1]=$row['SiglaProduttore'];
	  $out[2]=$row['Descrizione'];
	  if($showDeleteButtons){
	    $npez=$row['NumeroPezzi'];
	    $radio[0]="<input type=\"text\" id=\"npezzi\" name=\"$out[0]\" value=\"$npez\" />";
	    table_row(array_merge($out,$radio));
	  }else{
	    $out[3]=$row['NumeroPezzi'];
        table_row($out);
	  }
	}
  table_end();

}

/****************************************************************************\
| Funzione: show_listaComponenti
| Descrizione: visualizza la lista dei componenti in archivio (x l'admin)
\****************************************************************************/
function show_listaComponenti(){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= "SELECT * FROM componenti;";
  $result=mysql_query($query, $conn)
    or die("Query lettura componenti fallita! " . mysql_error());
  $titolo=array("Id componente","Sigla Produttore","Descrizione");
  table_start($titolo);

    while ($row=mysql_fetch_array($result)){
   	  $out[0]=$row['Id'];
	  $out[1]=$row['SiglaProduttore'];
	  $out[2]=$row['Descrizione'];
	  table_row($out);
	}
  table_end();

}

/****************************************************************************\
| Funzione: show_currentOrders
| Descrizione: visualizza le commesse in corso
\****************************************************************************/
function show_currentOrders($admin){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query="SELECT *,PezziProdotti(c.Id) as PezziAssemblati
          FROM commesse c
		  WHERE PezziProdotti(c.Id)<>QuantitaDaProdurre";
		  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
	
  if($admin){
	$titolo=array("Id Commessa","Progetto","Quantita' Prodotta","Cliente","Ordinata il");
  }else{
	$titolo=array("Venditore","Progetto","Quantita' Prodotta","Cliente","Ordinata il");
  }
  table_start($titolo);
  
    while ($row=mysql_fetch_array($result)){
   
	if($admin){
	//Mostro l'id della commessa con relativo link alla pagina del dettaglio
	$url=base_url()."/orderInfo.php" . "?Id=" . urlencode($row['Id']);
	$out[0]=hyperlink($row['Id'], $url);
	}else{
	  $out[0]=get_UserNamefromID($row['IdVenditore']);
	}
	
	if($admin){
	//visualizzo il nome del progetto con relativo link al dettaglio	
	$url=base_url()."/projectInfo.php?".http_build_query(array('IdProgetto' => $row['IdProgetto'],
															 'returnTo' => 'admin.php'));
	$out[1]=hyperlink(get_ProjectName($row['IdProgetto']), $url);
	}else{
	  $out[1]=get_ProjectName($row['IdProgetto']);
	}
	
	$out[3]=$row['PezziAssemblati']."/".$row['QuantitaDaProdurre'];
	
	//visualizzo il nome del cliente con relativo link al dettaglio	
	if($admin){	
		$returnTo="admin.php";
	}else{
		$returnTo="venditore.php";
	}
	$url=base_url()."/clientInfo.php?".http_build_query(array('IdCliente' => $row['IdCliente'],
															   'returnTo' => $returnTo));
	$out[4]=hyperlink(get_clientName($row['IdCliente']), $url);
	
	$out[5]=$row['DataCommessa'];
	
    table_row($out);
	}
	table_end();

}

/****************************************************************************\
| Funzione: show_completedOrders
| Descrizione: visualizza le commesse completate
\****************************************************************************/
function show_completedOrders($admin){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query="SELECT *,DataFineUltimoLavoroCommessa(Id) as DataFineCommessa 
          FROM commesse
		  WHERE PezziProdotti(Id)=QuantitaDaProdurre
		  ";
		  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
	
  if($admin){
  $titolo=array("Id Commessa","Progetto","Quantita'","Cliente","Ordinata il","Terminata il");
  }else{
    $titolo=array("Venditore","Progetto","Quantita'","Cliente","Ordinata il","Terminata il");
  }
  table_start($titolo);
  
    while ($row=mysql_fetch_array($result)){
	   
	if($admin){
	//Mostro l'id della commessa con relativo link alla pagina del dettaglio
	$url=base_url()."/orderInfo.php" . "?Id=" . urlencode($row['Id']);
	$out[0]=hyperlink($row['Id'], $url);	
	}else{
	  $out[0]=get_UserNamefromID($row['IdVenditore']);
	}
	
	//visualizzo il nome del progetto con relativo link al dettaglio
    if($admin){	
	$url=base_url()."/projectInfo.php?".http_build_query(array('IdProgetto' => $row['IdProgetto'],
															   'returnTo' => 'admin.php'));
	$out[1]=hyperlink(get_ProjectName($row['IdProgetto']), $url);
	}else{
	  $out[1]=$row['IdProgetto'];
	}
	$out[2]=$row['QuantitaDaProdurre'];
	
	//visualizzo il nome del cliente con relativo link al dettaglio	
	if($admin){	
		$returnTo="admin.php";
	}else{
		$returnTo="venditore.php";
	}
	$url=base_url()."/clientInfo.php?".http_build_query(array('IdCliente' => $row['IdCliente'],
															   'returnTo' => $returnTo));
	$out[3]=hyperlink(get_clientName($row['IdCliente']), $url);
	
	$out[4]=$row['DataCommessa'];
	
	$out[5]=$row['DataFineCommessa'];
    table_row($out);
	}
	table_end();

}

/****************************************************************************\
| Funzione: show_StoriaProduzione
| Descrizione: visualizza le fasi della produzione relativa ad una commessa
|              in una tabella HTML
\****************************************************************************/
function show_StoriaProduzione($Idcomm){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query="SELECT p.*
          FROM produzione p JOIN commesse c ON c.Id=p.IdCommessa
		  WHERE DataFineAssemblaggio IS NOT NULL AND IdCommessa=$Idcomm";
		  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
	
  $titolo=array("Id Assemblaggio","Operaio","Data inizio assemblaggio","Data fine assemblaggio");
  table_start($titolo);
  
    while ($row=mysql_fetch_array($result)){
   
	$out[0]=$row['Id'];
	
	$out[1]=get_UserNamefromID($row['IdOperaio']); 	
	
    $out[2]=$row['DataInizioAssemblaggio'];
	$out[3]=$row['DataFineAssemblaggio'];
	
    table_row($out);
	}
	table_end();

}

/****************************************************************************\
| Funzione: inserisciCliente
| Descrizione: aggiunge un nuovo Cliente al DB
\****************************************************************************/
function inserisciCliente($Nome,$Cognome,$Societa){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("INSERT INTO clienti (Nome,Cognome,Societa)
				   VALUES (\"%s\", \"%s\", \"%s\")", 
		  $Nome,$Cognome,$Societa);
		  
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiungere un nuovo cliente al DB " . mysql_error($conn));
	
   //controllo l'ID del nuovo utente
  $query= "SELECT max(Id) as Id FROM clienti;";
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
 
  $ID=$output['Id'];
	
  return $ID;
  
}

/****************************************************************************\
| Funzione: inserisciCommessa
| Descrizione: aggiunge una nuova commessa nel DB
\****************************************************************************/
function inserisciCommessa($IdProgetto,$IdVenditore,$IdCliente,$quantita){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("INSERT INTO commesse (IdProgetto,IdVenditore,IdCliente,QuantitaDaProdurre)
				   VALUES (\"%s\", \"%s\", \"%s\", \"%s\")", 
		  $IdProgetto,$IdVenditore,$IdCliente,$quantita);
		  
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiungere una nuova commessa al DB " . mysql_error($conn));
  
}

/****************************************************************************\
| Funzione: inserisciProgetto
| Descrizione: aggiunge un nuovo progetto al DB, restituisce l'ID
\****************************************************************************/
function inserisciProgetto($IdUpgrade,$IdTecnico,$Descrizione){

  set_FKchecks(0); //rimuovo il controllo sulle chiavi esterne per inserire il progetto
  
  $dbname=DBname;
  $conn = dbConnect($dbname);
  if($IdUpgrade<>0){
   $query= sprintf("INSERT INTO progetti (IdUpgrade,IdTecnico,Descrizione,DataCreazione)
			  	   VALUES (\"%s\", \"%s\", \"%s\", NULL)", 
		    $IdUpgrade,$IdTecnico,$Descrizione);
  }else{
	$query= sprintf("INSERT INTO progetti (IdUpgrade,IdTecnico,Descrizione,DataCreazione)
			  	   VALUES (NULL, \"%s\", \"%s\", NULL)", 
		    $IdTecnico,$Descrizione);
  }
		  
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiungere un nuovo progetto al DB " . mysql_error($conn));
	
  //controllo l'ID del nuovo progetto
  $query= "SELECT max(Id) as Id FROM progetti;";
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  $ID=$output['Id'];
  
  set_FKchecks(1);
  
  return $ID;
}

/****************************************************************************\
| Funzione: inserisciComponente
| Descrizione: aggiunge un nuovo componente al DB
\****************************************************************************/
function inserisciComponente($Id,$Sigla,$Descrizione){

  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("INSERT INTO componenti (Id,SiglaProduttore,Descrizione)
			  	   VALUES (\"%s\", \"%s\", \"%s\")", 
		    $Id,$Sigla,$Descrizione);
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiungere un nuovo componente al DB " . mysql_error($conn));
}

/****************************************************************************\
| Funzione: inserisciComponenteProgetto
| Descrizione: aggiunge npezzi di componenti ad un determinato progetto
\****************************************************************************/
function inserisciComponenteProgetto($Idcomp,$Idprog,$npezzi){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("INSERT INTO listacomponentiprogetto (IdP,IdC,NumeroPezzi)
			  	   VALUES (\"%s\", \"%s\", \"%s\")", 
		    $Idprog,$Idcomp,$npezzi);
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiungere i componenti al progetto" . mysql_error($conn));

}

/****************************************************************************\
| Funzione: aggiornaNpezzi
| Descrizione: aggiorna il numero pezzi di tutti i componenti di un determinato progetto
|              da richiamare solo 
\****************************************************************************/
function aggiornaNpezzi($IdP){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= "SELECT IdC FROM listacomponentiprogetto WHERE IdP=$IdP";
  $result=mysql_query($query,$conn)
    or die("Impossibile contare i componenti del progetto. " . mysql_error($conn));

  while ($compid = mysql_fetch_array($result)) {
    $IdC=$compid['IdC'];
    $npez=$_POST[$IdC];
	if($npez==0){ //se il numero di pezzi è a zero cancello tutta la riga
	  $query="DELETE FROM listacomponentiprogetto WHERE IdP=$IdP AND IdC=\"$IdC\" ;";
	}else{
	  $query="UPDATE listacomponentiprogetto SET NumeroPezzi=$npez WHERE IdP=$IdP AND IdC=\"$IdC\" ;";
	}
	$out1=mysql_query($query,$conn) or die("Impossibile aggiornare il numero pezzi. " . mysql_error($conn));
  }

}

/****************************************************************************\
| Funzione: confermaNuovoProgetto
| Descrizione: conferma il progetto appena creato impostando la data e descrizione
\****************************************************************************/
function confermaNuovoProgetto($Idp,$Descrizione){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("UPDATE progetti
				   SET Descrizione=\"%s\", DataCreazione=NOW()
				   WHERE Id=\"%s\"", 
		  $Descrizione,$Idp);
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiornare il nuovo progetto. " . mysql_error($conn));
}

/****************************************************************************\
| Funzione: ultimaModificaProgetto
| Descrizione: salva l'ID del tecnico che ha realizzato l'ultima modifica ed inserisce la data attuale
\****************************************************************************/
function ultimaModificaProgetto($Idp,$IDtecnicoMOD){
  set_FKchecks(0);
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query= sprintf("UPDATE progetti
				   SET IdTecnicoMOD=\"%s\", DataUltimaModifica=NOW()
				   WHERE Id=\"%s\"", 
		  $IDtecnicoMOD,$Idp);
  $result=mysql_query($query,$conn)
    or die("Impossibile aggiornare il progetto con l'IdTecnicoMOD. " . mysql_error($conn));
  set_FKchecks(1);
}

/****************************************************************************\
| Funzione: scartaNuovoProgetto
| Descrizione: elimina un progetto in fase di progettazione, restituisce 1 se ha
|              eliminato qualcosa
\****************************************************************************/
function scartaNuovoProgetto($Idtec,$idP){
  $dbname=DBname;
  $conn = dbConnect($dbname);
    //controllo quanti progetti sono in fase di progettazione per il tecnico $Idtec
  $query= "SELECT count(*) as nprogetti FROM progetti 
           WHERE Id=$idP AND IdTecnico=$Idtec AND DataCreazione IS NULL;";
  $result=mysql_query($query,$conn)
    or die("Query controllo progetti in progettazione fallita. " . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  $nprogetti=$output['nprogetti'];
  //elimino se ci sono progetti da eliminare
  if($nprogetti){
    $query= "DELETE FROM progetti WHERE Id=$idP AND IdTecnico=$Idtec AND DataCreazione IS NULL;";
    $result=mysql_query($query,$conn)
     or die("Impossibile scartare il nuovo progetto. " . mysql_error($conn));
	 
	//cancello il file schema elettrico se presente 
	$filename = "schemiProgetti/".$idP.".png";
	if(file_exists($filename))unlink($filename);
	 
  }
  return $nprogetti;
}

/****************************************************************************\
| Funzione: iniziaAssemblaggio
| Descrizione: inizia Assemblaggio di una unità relativa alla commessa indicata
\****************************************************************************/
function iniziaAssemblaggio($Idcomm,$IdOp){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("INSERT INTO produzione (IdCommessa,IdOperaio)
				   VALUES (\"%s\", \"%s\")", 
		  $Idcomm,$IdOp);
		  
  $result=mysql_query($query,$conn)
    or die("Impossibile iniziare la produzione " . mysql_error($conn));
  
}

/****************************************************************************\
| Funzione: terminaAssemblaggio
| Descrizione: termina l'assemblaggio di una unità in produzione scrivendo la
|			   data attuale
\****************************************************************************/
function terminaAssemblaggio($IdProduzione){
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("UPDATE produzione 
				   SET DataFineAssemblaggio=NOW()
				   WHERE Id=\"%s\"", 
		  $IdProduzione);
		  
  $result=mysql_query($query,$conn)
    or die("Impossibile iniziare la produzione" . mysql_error($conn));
  
}

/****************************************************************************\
| Funzione: new_user
| Descrizione: aggiunge un nuovo utente e genera automaticamente l'account
|			   restituisce l'id
\****************************************************************************/
function new_user($nome, $cognome, $categoria) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);
  
  //inserisce il nuovo utente
  $query= sprintf("INSERT INTO utenti (Categoria,Nome,Cognome) 
				   VALUES (\"%s\", \"%s\", \"%s\")", 
		  $categoria, $nome, $cognome);
     
  mysql_query($query,$conn)
    or die("Inserimento nuovo utente fallito!" . mysql_error($conn));
	
  //controllo l'ID del nuovo utente
  $query= "SELECT max(Id) as Id FROM utenti;";
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));
  $output=mysql_fetch_assoc($result);
  $ID=$output['Id'];
   
  //genero i dati account relativi al nuovo utente
  $username=substr(strtolower($nome),0,5).substr(strtolower($cognome),0,1). $ID;  //genero username
  $pwd="ELAB"; //password di default per il nuovo utente
	
  $query= sprintf("UPDATE utenti SET Username=\"%s\", Password=\"%s\" WHERE Id=\"%s\"", 
		  $username,SHA1($pwd),$ID);
  
  mysql_query($query,$conn)
    or die("Inserimento dati account utente fallito" . mysql_error($conn));
	
  return $ID;

}

/****************************************************************************\
| Funzione: removeUser
| Descrizione: rimuove un utente dal database
\****************************************************************************/
function removeUser($Id) {

  set_FKchecks(0); //rimuovo il controllo sulle chiavi esterne per eliminare l'utente
  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);
  
  //rimuove l'utente
  $query= "DELETE FROM utenti WHERE Id=$Id;";
  mysql_query($query,$conn)
    or die("Rimozione utente dal DB fallita!" . mysql_error($conn));
	
  set_FKchecks(1);	//riattivo il controllo sulle chiavi esterne
	
}

/****************************************************************************\
| Funzione: set_FKchecks
| Descrizione: imposta il controllo delle chiavi esterne 0-1
\****************************************************************************/
function set_FKchecks($value){
  $dbname=DBname;
  $conn = dbConnect($dbname);
  $query="SET FOREIGN_KEY_CHECKS=$value;";
  mysql_query($query,$conn);
}

/****************************************************************************\
| Funzione: update_password
| Descrizione: aggiorna la password dell'utente specificato
\****************************************************************************/
function update_password($login, $password) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);

  /* preparazione dello statement */
  $query= sprintf("UPDATE utenti SET Password=\"%s\" WHERE Username=\"%s\"", 
		  SHA1($password),$login);
  
  /* Stampa la query a video ... utile per debug */
  /* echo "<B>Query</B>: $query <BR />"; */
  
  mysql_query($query,$conn)
    or die("Password update fallita" . mysql_error($conn));
}

/****************************************************************************\
| Funzione: get_pwd
| Descrizione: restituisce la password relativa al login
\****************************************************************************/
function get_pwd($login) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);

  /* preparazione dello statement */
  $query= sprintf("SELECT * FROM utenti WHERE Username=\"%s\"", 
		  $login);
  
  /* Stampa la query a video ... utile per debug */
  /* echo "<b>Query</b>: $query <br />"; */
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);

  if ($output)
    return $output['Password'];
  else 
    return FALSE;
}

/****************************************************************************\
| Funzione: get_accountInfo
| Descrizione: restituisce la username e password relativa all'ID
\****************************************************************************/
function get_accountInfo($Id) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT Username,Password FROM utenti WHERE Id=\"%s\"", 
		  $Id);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);

  if ($output)
    return $output;
  else 
    return FALSE;
}

/****************************************************************************\
| Funzione: get_name
| Descrizione: restituisce il nome dell'utente relativo al login fornito
\****************************************************************************/
function get_name($login) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);

  /* preparazione dello statement */
  $query= sprintf("SELECT Nome FROM utenti WHERE UserName=\"%s\"", 
		  $login);
  
  /* Stampa la query a video ... utile per debug */
  /* echo "<b>Query</b>: $query <br />"; */
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);

  if ($output)
    return $output['Nome'];
  else 
    return FALSE;
}

/****************************************************************************\
| Funzione: get_userID
| Descrizione: restituisce l'Id dell'utente relativo al login fornito
\****************************************************************************/
function get_userID($login) {

  /* si connette e seleziona il database da usare */
  $dbname=DBname;
  $conn = dbConnect($dbname);

  /* preparazione dello statement */
  $query= sprintf("SELECT Id FROM utenti WHERE UserName=\"%s\"", 
		  $login);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);

  if ($output)
    return $output['Id'];
  else 
    return FALSE;
}

/****************************************************************************\
| Funzione: get_userDatass
| Descrizione: restituisce la data assunzione dell'utente ID
\****************************************************************************/
function get_userDatass($ID) {

  $dbname=DBname;
  $conn = dbConnect($dbname);

  $query= sprintf("SELECT DataAssunzione FROM utenti WHERE Id=\"%s\"", 
		  $ID);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);

  return $output['DataAssunzione'];
 
}

/****************************************************************************\
| Funzione: user_check
| Descrizione: restituisce la Categoria dell'utente con username specificato
\****************************************************************************/
function user_check($username) {

  $dbname=DBname;
  $conn = dbConnect($dbname);
  
  $query= sprintf("SELECT Categoria FROM utenti WHERE Username=\"%s\"", 
		  $username);
  
  $result=mysql_query($query,$conn)
    or die("Query fallita" . mysql_error($conn));

  $output=mysql_fetch_assoc($result);
  
  if ($output){
    return $output['Categoria'];
  }else{
	return FALSE;
  }
  
}
/****************************************************************************\
| Funzione: authenticate
| Descrizione: inizia la sessione e verifica che l'utente sia autenticato
\****************************************************************************/
function authenticate() {
  if(USE_COOKIES){
    //leggo il cookie
    list($login,$hashcode)=split(",",$COOKIE['logged']);
	echo $COOKIE['logged'];
	if(SHA1($login.$chiaveSegreta)!=$hashcode){
	  return FALSE;
	}else{
	  return $login;
	}
  }else{
    session_start();
    //leggo, controllo il login della sessione
    $login=$_SESSION['logged'];
    if (! $login) {
      return FALSE;
    } else {
      return $login;
    }
  }
}

/****************************************************************************\
| Funzione: base_url
| Descrizione: ritorna l'url base dello script corrente
\****************************************************************************/
function base_url() {
  return "http://{$_SERVER['HTTP_HOST']}"
         .dirname($_SERVER['PHP_SELF']);
}

?>