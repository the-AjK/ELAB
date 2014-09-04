<?php
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('tecnici'<>user_check($login))
  die("Non sei un tecnico e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
page_start("Gestione Progetti");

/* inizia la tabella di formattazione pagina ************/
echo<<<END
<table width="100%" border="1" cellpadding="5">
<tr>
   <td width="10%" align="left" valign="top" bgcolor="$sfondoBarra" >
END;

/* barra laterale ************************************/
echo "Tecnico:<br /> <b>" . get_name($login) .  "</b>";
echo "<br /><br />";

/* corpo centrale ************************************/
$IDtecnico=get_userID($login);	//leggo l'id del tecnico che sta lavorando
$nomeNuovoProgetto="Nuovo Progetto";			//il nome di default

//dati upload immagine schema elettrico
$localdir=DIR_SCHEMI;
$formatoImmagine=ESTENSIONE_IMMAGINE_SCHEMI;
$maxFileSize=DIM_MAX/1000;

if ($_POST['creaProgetto'] || $_POST['aggiungiComponente'] || $_POST['uploadImage']) {
//**********************************************************
//********************CREA PROGETTO*************************
//**********************************************************
//echo hyperlink("Logout", "logout.php"); //disabilito il logout in fase di creazione
echo "</td><td>";
if(!$_POST['creaProgetto'] )$nomeNuovoProgetto=$_POST['nomePr'];  //recupero il nome del nuovo progetto per riempire nuovamente la form
if ($_POST['creaProgetto']){
  $newID=inserisciProgetto(0,$IDtecnico,"- in progettazione -");
}elseif ($_POST['aggiungiComponente']){                //se ho appena aggiunto un componente
  $idComp=$_POST['nuovoComponente'];	               //leggo l'ID del componente da aggiungere alla lista
  $newID=$_POST['newID'];								//leggo l'id del nuovo progetto che sto creando
  $numeroPezzi=$_POST['npezzi'];		                //leggo il numero di pezzi da aggiungere
  
  //visualizzo l'errore se l'ID del componente ha più di 4caratteri oppure zero
  if(!$idComp && ((strlen($_POST['IDcomp'])>4) || (strlen($_POST['IDcomp'])==0))){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      L'ID del componente deve avere un numero di caratteri alfanumerici compreso tra 1 e 4!</fieldset>";
		  
  //errore se il numero pezzi non è numerico oppure se è zero	  
  }elseif(!ctype_digit($numeroPezzi) || $numeroPezzi==0){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      Il campo \"Numero Pezzi\" deve avere un valore numeri non nullo!</fieldset>";
		  
  //controllo che il componente da aggiungere al progetto non sia già presente nel progetto
  }elseif($idComp && projectComponentAlreadyEXIST($newID,$idComp)){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      Il componente e' gia' presente nel progetto!</fieldset>";  
		  
  //controllo che il nuovo componente che si vuole aggiungere non abbia lo stesso ID di un componente esistente
  }elseif(!$idComp && ComponentAlreadyEXIST($_POST['IDcomp'])){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      L'ID del nuovo componente da inserire e' gia' presente nel database!</fieldset>";  

  //altrimenti posso continuare..
  }else{
    echo "<p></p><fieldset><b>Novita': </b>";
    if(!$idComp){ //il componente non è presente quindi aggiungo il nuovo componente
	  $idComp=$_POST['IDcomp'];
	  $newSigla=$_POST['Siglacomp'];
	  $newDesc=$_POST['Desccomp'];	  
      inserisciComponente($idComp,$newSigla,$newDesc);	 //inserisco il nuovo componente  
	  echo "Creazione nuovo componente avvenuta con successo!<br /><b>Novita': </b>";
    }
    //ora aggiungo il componente al progetto
    inserisciComponenteProgetto($idComp,$newID,$numeroPezzi);
    echo "Inserimento componente al progetto avvenuto con successo!</fieldset>";
  }
}elseif($_POST['uploadImage']){

  $newID=$_POST['newID'];	//leggo l'id del progetto e creo il nuovo nome del file da salvare
  $nomeNuovoProgetto=$_POST['nomePr']; 	//leggo la nuova descrizione del progetto
  
  /* informazioni sul file */
  $error= $_FILES['myfile']['error'];    /* codice di errore */
  $size = $_FILES['myfile']['size'];     /* dimensione       */
  $type = $_FILES['myfile']['type'];     /* mime-type        */
  $name = $_FILES['myfile']['name'];     /* nome sul client  */
  $tmp  = $_FILES['myfile']['tmp_name']; /* nome sul server  */
 
  //controllo se ci sono errori nel file in upload
  if($error != UPLOAD_ERR_OK){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito! 
	      Errore di upload. Codice: $error</fieldset>"; 
	
  //controllo se il tipo è corretto	
  }elseif($type != "image/$formatoImmagine"){ 
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito!
	      File di tipo $type. Errato!</fieldset>";
	
  //controllo la dimensione del file
  }elseif ($size > DIM_MAX){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito!
	      File troppo grande ($size bytes)!</fieldset>";
		  
  }else{  //scrivo il file a destinazione
    $schemaImage=$localdir."/".$newID.".".$formatoImmagine;
    move_uploaded_file($tmp, $schemaImage);
	echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico COMPLETATO!</fieldset>";
  }

}else{
  
}

echo<<<END
<p></p><b>Creazione Nuovo Progetto:</b><p></p>
  <form method="post" action="tecnico.php">
	<p>
	   <input type="hidden" id="idPr" name="idPr" value=$newID />
       <label for="nomePr">Nome Progetto:</label>
       <input type="text" id="nomePr" name="nomePr" value="$nomeNuovoProgetto" size=50 />
	   <input type="submit" name="salvaProgetto" value="Salva il progetto ed Esci" />
	   |
	   <input type="submit" name="cancellaProgetto" value="Esci senza salvare il progetto" />
    </p>

	Lista Componenti:
END;
show_listaComponentiProgetto($newID,1);	//visualizzo la tabella dei componenti del nuovo progetto con delete(1)
echo<<<END
	<p></p><fieldset>
	<label for="nuovoComponente">Componente da aggiungere al progetto:</label>
	<select name="nuovoComponente" id="nuovoComponente" size="1" >
		<option value=0>- Nuovo Componente -</option>
END;
//prelievo della lista dei componenti
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT * FROM componenti;";
$result=mysql_query($query, $conn)
  or die("Query lettura componenti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
    $idC=$row['Id'];
	$sigla=$row['SiglaProduttore'];
	$desc=$row['Descrizione'];
	echo "<option value=\"$idC\">$sigla - $desc</option>";
}
echo<<<END
	</select><p></p>
  <p>
	<label for="npezzi">Numero Pezzi:</label>
    <input type="text" id="npezzi" name="npezzi" value=1 maxlength=4 size=4/>
	|
	<input type="submit" name="aggiungiComponente" value="Aggiungi Componente al progetto" />
  </p>
  <fieldset>
  <b>Se stai inserendo un nuovo componente questi campi sono obbligatori:</b>
  <p>
	<label for="IDcomp">Id componente [max 4 caratteri]:</label>
    <input type="text" id="IDcomp" name="IDcomp" maxlength=4 size=4/>
  </p>
  <p>
	<label for="Siglacomp">Sigla Produttore:</label>
    <input type="text" id="Siglacomp" name="Siglacomp" />
  </p>
  <p>
	<label for="Desccomp">Descrizione:</label>
    <input type="text" id="Desccomp" name="Desccomp" size=50/>
  </p>
  <input type="hidden" id="newID" name="newID" value=$newID />
  <p></p><input type="submit" name="aggiungiComponente" value="Aggiungi Nuovo Componente al progetto" />
  
  </form>
  </fieldset>
  <p></p>  
  <fieldset>
	<b>Aggiungi Schema:</b><br />
	(formato immagine .$formatoImmagine dimensione massima $maxFileSize kbytes)
	<p> </p>
END;
  $schemaImage=$localdir."/".$newID.".".$formatoImmagine;
  if(file_exists($schemaImage)){
    echo "<img alt=\"schema elettrico progetto\" src=\"$schemaImage\" /> ";
  }else{
    echo " - Nessuna Immagine Caricata - ";
  }
echo<<<END

<form enctype="multipart/form-data" action="tecnico.php" method="post">

  <input type="hidden" name="MAX_FILE_SIZE" value=DIM_MAX />
  <input type="hidden" name="newID" value=$newID />
  <input type="hidden" name="nomePr" value="$nomeNuovoProgetto" />
  <p></p><input type="file" name="myfile" />
  <p></p><input type="submit" name="uploadImage" value="Carica Nuova Immagine"/>
  </form>

  </fieldset>
  <p></p>
</fieldset>
<p></p>
	  
END;
}elseif ($_POST['modificaProgetto'] || $_POST['aggiungiComponenteMOD'] || $_POST['uploadImageMOD']) {         
//**********************************************************
//********************MODIFICA PROGETTO*********************
//**********************************************************
//echo hyperlink("Logout", "logout.php"); //disabilito il logout in fase di modifica
echo "</td><td>";

if ($_POST['modificaProgetto']){            //la prima volta che entro in modificaProgetto
  $idProg=$_POST['IdProgetto'];             //ricevo l'id del progetto da modificare
  $descrizione=get_ProjectName($idProg);    //leggo la descrizione del progetto 
  $nuovaDesc=$descrizione;
  if($_POST['creaCopia'])                  //nel caso creo una nuova copia
    $nuovaDesc=$nuovaDesc." - new";		   //genero la nuova descrizione di default
  $autoreOriginale=get_UserNamefromProjectID($idProg); //leggo l'autore del progetto originale
  $autoreUltimaModifica=get_lastModUserfromProjectID($idProg);//l'id del tecnico che ha modificato per ultimo il progetto
  
  //inserisco un nuovo progetto solo se sto modificando un progetto concluso e voglio creare una copia
  if(!ISinProgettazione($idProg) && $_POST['creaCopia']){
    $newID=inserisciProgetto($idProg,$IDtecnico,"- in progettazione -"); //creo un progetto "upgrade"
	//ora copio tutti i componenti del progetto iniziale nel nuovo progetto upgrade
    clonaComponentiProgetto($idProg,$newID);
  }else{
    $newID=$idProg;	//altrimenti continuo a modificare il progetto che era "in progettazione"
  }   
}elseif ($_POST['aggiungiComponenteMOD']){          //se ho appena aggiunto un componente

  $newID=$_POST['newID'];							//leggo l'id del nuovo progetto che sto creando
  $nuovaDesc=$_POST['nuovoNome'];					//leggo la nuova descrizione del progetto
  $autoreOriginale=$_POST['autoreOriginale'];       //leggo l'autore del progetto originale
  $descrizione=$_POST['descOriginale'];				//leggo il nome del progetto originale
  
  ultimaModificaProgetto($newID,$IDtecnico);	    //salvo l'autore dell'ultima modifica (il tecnico corrente)
  $autoreUltimaModifica=get_lastModUserfromProjectID($newID);
  
  $idComp=$_POST['nuovoComponente'];	            //leggo l'ID del componente da aggiungere alla lista
  $numeroPezzi=$_POST['npezzi'];		            //leggo il numero di pezzi da aggiungere
  
  //visualizzo l'errore se l'ID del componente ha più di 4caratteri oppure zero
  if(!$idComp && ((strlen($_POST['IDcomp'])>4) || (strlen($_POST['IDcomp'])==0))){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      L'ID del componente deve avere un numero di caratteri alfanumerici compreso tra 1 e 4!</fieldset>";
		  
  //errore se il numero pezzi non è numerico oppure se è zero	  
  }elseif(!ctype_digit($numeroPezzi) || $numeroPezzi==0){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      Il campo \"Numero Pezzi\" deve avere un valore numeri non nullo!</fieldset>";
		  
  //controllo che il componente da aggiungere al progetto non sia già presente nel progetto
  }elseif($idComp && projectComponentAlreadyEXIST($newID,$idComp)){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      Il componente e' gia' presente nel progetto!</fieldset>";  
		  
  //controllo che il nuovo componente che si vuole aggiungere non abbia lo stesso ID di un componente esistente
  }elseif(!$idComp && ComponentAlreadyEXIST($_POST['IDcomp'])){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento componente fallito! 
	      L'ID del nuovo componente da inserire e' gia' presente nel database!</fieldset>";  

  //altrimenti posso continuare..
  }else{
    echo "<p></p><fieldset><b>Novita': </b>";
    if(!$idComp){ //il componente non è presente quindi aggiungo il nuovo componente
	  $idComp=$_POST['IDcomp'];
	  $newSigla=$_POST['Siglacomp'];
	  $newDesc=$_POST['Desccomp'];	  
      inserisciComponente($idComp,$newSigla,$newDesc);	 //inserisco il nuovo componente  
	  echo "Creazione nuovo componente avvenuta con successo!<br /><b>Novita': </b>";
    }
    //ora aggiungo il componente al progetto
    inserisciComponenteProgetto($idComp,$newID,$numeroPezzi);
    echo "Inserimento componente al progetto avvenuto con successo!</fieldset>";
  }
}elseif($_POST['uploadImageMOD']){

  $newID=$_POST['newID'];	//leggo l'id del progetto e creo il nuovo nome del file da salvare
  $nuovaDesc=$_POST['nuovaDesc'];					//leggo la nuova descrizione del progetto
  $autoreOriginale=$_POST['autoreOriginale'];       //leggo l'autore del progetto originale
  $descrizione=$_POST['descOriginale'];				//leggo il nome del progetto originale
 
  ultimaModificaProgetto($newID,$IDtecnico);	    //salvo l'autore dell'ultima modifica (il tecnico corrente)
  $autoreUltimaModifica=get_lastModUserfromProjectID($newID);
   
  /* informazioni sul file */
  $error= $_FILES['myfile']['error'];    /* codice di errore */
  $size = $_FILES['myfile']['size'];     /* dimensione       */
  $type = $_FILES['myfile']['type'];     /* mime-type        */
  $name = $_FILES['myfile']['name'];     /* nome sul client  */
  $tmp  = $_FILES['myfile']['tmp_name']; /* nome sul server  */
 
  //controllo se ci sono errori nel file in upload
  if($error != UPLOAD_ERR_OK){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito! 
	      Errore di upload. Codice: $error</fieldset>"; 
	
  //controllo se il tipo è corretto	
  }elseif($type != "image/$formatoImmagine"){ 
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito!
	      File di tipo $type. Errato!</fieldset>";
	
  //controllo la dimensione del file
  }elseif ($size > DIM_MAX){
    echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico fallito!
	      File troppo grande ($size bytes)!</fieldset>";
		  
  }else{  //scrivo il file a destinazione
    $schemaImage=$localdir."/".$newID.".".$formatoImmagine;
    move_uploaded_file($tmp, $schemaImage);
	echo "<p></p><fieldset><b>Novita':</b> Inserimento schema elettrico COMPLETATO!</fieldset>";
  }
  
}else{

}

//**fine controllo dati $_POST
  
echo<<<END
	<p></p><b>Modifica del progetto: $descrizione</b><br />
	Autore originale: $autoreOriginale<br />
END;
//se c'è stata una modifica da un nuovo tecnico lo scrivo
if(strlen($autoreUltimaModifica)!=0)
	echo "Ultima modifica: $autoreUltimaModifica";

echo "<p></p>";
if($_POST['creaCopia'])
  echo "<p></p><b>N.B.:</b> Il nuovo progetto verra' salvato in copia come \"upgrade\" del progetto precedente.";
echo<<<END
	<form method="post" action="tecnico.php">
	<p>
       <label for="nuovoNome">Nome Progetto:</label>
       <input type="text" id="nuovoNome" name="nuovoNome" value="$nuovaDesc" size=50 />
	   <input type="hidden" id="nuovaDesc" name="nuovaDesc" value="$nuovaDesc" />
	   <input type="hidden" id="autoreOriginale" name="autoreOriginale" value="$autoreOriginale" />
	   <input type="hidden" id="descOriginale" name="descOriginale" value="$descrizione" />
	   <input type="submit" name="salvaProgettoMOD" value="Salva il progetto ed Esci" />
	   |
	   <input type="submit" name="cancellaProgettoMOD" value="Esci senza aggiornare il numero pezzi" />
     </p>
	 <b>ATTENZIONE: Le modifiche alla lista componenti ed allo schema elettrico sono permanenti.</b>
END;
show_listaComponentiProgetto($newID,1);	//visualizzo la tabella dei componenti del nuovo progetto con delete(1)
echo<<<END
<p></p><fieldset>
	<label for="nuovoComponente">Componente da aggiungere al progetto:</label>
	<select name="nuovoComponente" id="nuovoComponente" size="1" >
		<option value=0>- Nuovo Componente -</option>
END;
//prelievo della lista dei componenti
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT * FROM componenti;";
$result=mysql_query($query, $conn)
  or die("Query lettura componenti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
    $idC=$row['Id'];
	$sigla=$row['SiglaProduttore'];
	$desc=$row['Descrizione'];
	echo "<option value=\"$idC\">$sigla - $desc</option>";
}
echo<<<END
	</select><p></p>
  <p>
	<label for="npezzi">Numero Pezzi:</label>
    <input type="text" id="npezzi" name="npezzi" value=1 maxlength=4 size=4 />
	|
	<input type="submit" name="aggiungiComponenteMOD" value="Aggiungi Componente al progetto" />
  </p>
  <fieldset>
  <b>Se stai inserendo un nuovo componente questi campi sono obbligatori:</b>
  <p>
	<label for="IDcomp">Id componente [max 4 caratteri]:</label>
    <input type="text" id="IDcomp" name="IDcomp" maxlength=4 size=4 />
  </p>
  <p>
	<label for="Siglacomp">Sigla Produttore:</label>
    <input type="text" id="Siglacomp" name="Siglacomp"  />
  </p>
  <p>
	<label for="Desccomp">Descrizione:</label>
    <input type="text" id="Desccomp" name="Desccomp" size=50 />
  </p>
  <input type="hidden" id="newID" name="newID" value="$newID" />
  <p></p><input type="submit" name="aggiungiComponenteMOD" value="Aggiungi Nuovo Componente al progetto" />
  
  </form>
  </fieldset>
  <p></p>  
  <fieldset>
	<b>Aggiungi Schema:</b><br />
	(formato immagine .$formatoImmagine dimensione massima $maxFileSize kbytes)
	<p> </p>
END;
  $schemaImage=$localdir."/".$newID.".".$formatoImmagine;
  if(file_exists($schemaImage)){
    echo "<img alt=\"schema elettrico progetto\" src=\"$schemaImage\" /> ";
  }else{
    echo " - Nessuna Immagine Caricata - ";
  }
echo<<<END

<form enctype="multipart/form-data" action="tecnico.php" method="post">

  <input type="hidden" name="MAX_FILE_SIZE" value=DIM_MAX />
  <input type="hidden" name="newID" value=$newID />
  <input type="hidden" id="nuovaDesc" name="nuovaDesc" value="$nuovaDesc""/>
  <input type="hidden" id="autoreOriginale" name="autoreOriginale" value="$autoreOriginale""/>
  <input type="hidden" id="descOriginale" name="descOriginale" value="$descrizione""/>
  <p></p><input type="file" name="myfile" />
  <p></p><input type="submit" name="uploadImageMOD" value="Carica Nuova Immagine"/>
  </form>

  </fieldset>
  <p></p>
</fieldset>
<p></p>
	 
END;
}else{
//**********************************************************
//************PAGINA INIZIALE TECNICO***********************
//**********************************************************
echo hyperlink("Logout", "logout.php"); //logout abilitato nella schermata iniziale del tecnico
echo "</td><td>";

//di default i due pulsanti sono disabilitati
$continueButton="disabled";
$modButton="disabled";

if ($_POST['salvaProgetto']) {  //controllo se ho appena concluso un nuovo progetto, lo salvo
  $idpro=$_POST['idPr'];
  $desc=$_POST['nomePr'];
  confermaNuovoProgetto($idpro,$desc);
  echo "<p></p> <b>Novita':</b> Hai appena creato con successo un nuovo progetto! -> <b>".get_ProjectName($idpro)."</b>";
  
}elseif ($_POST['salvaProgettoMOD']){//se ho appena modificato un progetto esistente
  $idpro=$_POST['newID'];
  $desc=$_POST['nuovoNome'];
  aggiornaNpezzi($idpro);              //aggiorno il numero di pezzi dei componenti del progetto
  confermaNuovoProgetto($idpro,$desc); //concludo il progetto aggiungendo l'ora attuale
  ultimaModificaProgetto($idpro,$IDtecnico); //salvo anche il tecnico che ha fatto l'ultima modifica
 
  echo "<p></p> <b>Novita':</b> Hai appena modificato con successo un progetto esistente! <br />
        <b>Novita':</b> E' stato creato il nuovo progetto -> <b>".get_ProjectName($idpro)."</b>";
 
}elseif($_POST['cancellaProgetto'] || $_POST['cancellaProgettoMOD']){
  $idpro=$_POST['newID'];
  if(scartaNuovoProgetto($IDtecnico,$idpro))
  //visualizzo il messaggio solo se ho trovato progetti in fase di progettazione e li ho cancellati
  echo "<p></p> <b>Novita':</b> Le modifiche al nuovo progetto sono state scartate.";
}

if(progettiIncompleti($IDtecnico)){
  echo "<p></p> <b>Novita':</b> Hai ancora dei progetti da completare, controlla la lista progetti e modificali.";
  //visto che ci sono progetti incompleti da continuare, 
  $continueButton="enabled";
}

//controllo se ci sono dei progetti da poter modificare
$dbname=DBname;
$conn = dbConnect($dbname);
$query= "SELECT count(*) as nProgetti FROM progetti WHERE DataCreazione IS NOT NULL;";
$result=mysql_query($query,$conn)
  or die("Query fallita" . mysql_error($conn));
$output=mysql_fetch_assoc($result);
if($output['nProgetti']>0)$modButton="enabled";  //abilito il pulsante modifica se ho almento un progetto in lista
  
echo<<<END
<p></p>Opzioni Disponibili:<br />

<form method="post" action="tecnico.php">
	<fieldset><p></p>
	<input type="submit" name="creaProgetto" value="Crea Nuovo Progetto" /><p></p>
	<input type="submit" name="modificaProgetto" value="Modifica Progetto Esistente" $modButton/>
	<select name="IdProgetto" id="IdProgetto" size="1" >
END;

//prelievo della lista di tutti i progetti completi, mentre quelli incompleti solo riguardanti al tecnico che è connesso
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT Id,IdTecnico,Descrizione FROM progetti WHERE DataCreazione IS NOT NULL";
$result=mysql_query($query, $conn)
  or die("Query lettura progetti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
	$idP=$row['Id'];
	$desc=$row['Descrizione'];
	echo "<option value=\"$idP\">$desc (creato da ".get_UserNamefromID($row['IdTecnico']).")</option>";
}
echo<<<END
	</select>
	<?-- per attivare il checkbox di default inserire: checked="checked" >
	<input type="checkbox" name="creaCopia" value="creaCopia" $modButton /> Crea una copia (upgrade)
	</form>
	<p></p>
	<form method="post" action="tecnico.php">
	<input type="submit" name="modificaProgetto" value="Continua Progetto Incompleto" $continueButton />
	<select name="IdProgetto" id="IdProgetto" size="1" >
END;

//prelievo della lista di tutti i progetti completi, mentre quelli incompleti solo riguardanti al tecnico che è connesso
$dbname=DBname;
$conn = dbConnect($dbname);
$query="SELECT Id,IdTecnico,Descrizione FROM progetti WHERE DataCreazione IS NULL AND IdTecnico=$IDtecnico;";
$result=mysql_query($query, $conn)
  or die("Query lettura progetti fallita! " . mysql_error());
while ($row = mysql_fetch_array($result)) {
	$idP=$row['Id'];
	$desc=$row['Descrizione'];
	echo "<option value=\"$idP\">ID: $idP $desc </option>";
}
echo<<<END
	</select>
	<p></p>
	
	</fieldset>
</form>
END;
}
/* fine tabella di formattazione *****************************/
echo "</td></tr>";
echo "</table>";

page_end();
?>