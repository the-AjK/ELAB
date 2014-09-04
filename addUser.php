<?php
//script per aggiungere un nuovo lavoratore al sistema
require("functions.php");

/* verifica se l'utente e' autenticato e se si trova nella giusta sezione*/
$login=authenticate() or
  die("Non sei Autorizzato, esegui il <a href=\"login.php\">login</a>");

if('amministratori'<>user_check($login))
  die("Non sei un amministratore e quindi non puoi accedere a questa sezione. <a href=\"login.php\">Torna indietro.</a>");
  
page_start("Aggiunta Nuovo Lavoratore");

/* recupera i dati immessi */
$nome=$_POST['nome'];
$cognome=$_POST['cognome'];
$categoria=$_POST['categoria'];

//controllo la correttezza dei dati
if (!ctype_alnum($nome) ||  !ctype_alnum($cognome) || !ctype_alnum($categoria))
    $error="I dati inseriti non sono formalmente corretti!";
  elseif  ($categoria != 'operai' && $categoria != 'tecnici' && $categoria != 'venditori' && $categoria != 'amministratori')
    $error = "Errore! Categoria nuovo cliente invalida. ";
  else {
    //inserisco un nuovo utente
    $ID=new_user($nome, $cognome, $categoria);
  };

//se i dati sono incorretti rimando alla pagina precedente
if ($error){
	die($error."<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>");
}

subtitle("Riepilogo dati personali del nuovo utente:");

$account=get_accountInfo($ID);	//leggo il login e password(SHA1)
$datass=get_userDatass($ID);	//leggo la data di assunzione

echo "Nome: $nome <br />
	  Cognome: $cognome <br />
	  Categoria: $categoria <br />
	  Data Assunzione: $datass <br />
	  Username: ".$account['Username']." <br />
	  Password: ELAB <br /><p></p>";
	  
echo<<<END
<fieldset>
 *** ATTENZIONE *** <p>Si ricorda al nuovo utente di cambiare la password di default con<br /> 
                     con una nuova password personale il prima possibile.<br />
					 E' possibile effettuare il cambio password dal link presente nella pagina di Login.</p>
					 
	<SCRIPT LANGUAGE="JavaScript">
	if (window.print) {
	document.write('<form> '
	+ '<input type=button name=print value="Stampa promemoria dati nuovo utente" '
	+ 'onClick="javascript:window.print()"> </form>');
	}
	</script>

</fieldset>

<p></p><a href=admin.php>Ritorna alla pagina precedente.</a>

END;
  
  page_end();
?>