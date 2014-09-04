<?php
//script per visualizzare i dettagli dell'utente
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('amministratori'<>user_check($login))
  die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");

if ($_POST['licenziaUtente']){
	$Id=$_POST['IdUtente'];
	$LoggedUserAccountInfo=get_accountInfo($Id);
	if($_SESSION['logged']<>$LoggedUserAccountInfo['Username']){
		$nominativo=get_UserNamefromID($Id);
		removeUser($Id);
		die("L'utente ".$nominativo." e' stato/a licenziato/a!<br />
			<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>");
	}else{
		die("Non puoi licenziare te stesso!<br />
			<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>");
	}
}else{
	/* recupera i dati immessi */
	$Id=$_GET['Id'];
}

$nomecognome=get_UserNamefromID($Id); //leggo nome e cognome dal DB

page_start("Vuoi veramente Licenziare ".$nomecognome. " ?");

echo<<<END
<form method="POST" action="userInfo.php">
	<fieldset>
		<input type="hidden" name="IdUtente" value=$Id />
		<input type="submit" name="licenziaUtente" value="Licenzia Utente" />
	</fieldset>
</form>
END;
 
echo "<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>";

page_end();
?>