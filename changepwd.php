<?php
/* script unico per permettere all'utente di modificare la propria password */
require("functions.php");
page_start("Modifica Password di Accesso al sistema");

if ($_POST['submit']) {
  /* recupera i dati immessi */
  $login=$_POST['login'];
  $oldPassword=$_POST['oldPassword'];
  $password=$_POST['password'];
  $confirm=$_POST['confirm'];
  
  if (!ctype_alnum($login) ||  !ctype_alnum($password) || !ctype_alnum($oldPassword))
    $error="Login e password devono essere alfanumerici e non vuoti";
  elseif  ($password != $confirm)
    /* verifica se le password e la conferma sono uguali */
    $error = "Errore! Nuova password e Conferma sono diverse. ";
  elseif (SHA1($oldPassword)<>get_pwd($login))
  	//controllo che logic e vecchia password siano corretti
    $error= "Errore! Login o vecchia password incorretti.";
  else {
    //aggiorno la passowrd dell'utente
    update_password($login,$password);
  };
};

if ($error || ! $_POST['submit']) {
  /* se c'era un errore nei dati oppure si arriva alla pagina per la
     prima volta, visualizza la form */ 
  echo<<<END
<form method="post" action="changepwd.php">
  <fieldset>
	Gentile utente, qui hai la possibilita' di sostituire la tua vecchia password personale con una nuova.<br />
	Ti ricordiamo che per motivi di sicurezza e' consigliato il cambio password ogni 30giorni.
     <p>
       <label for="login">Username:</label>
       <input type="text" id="login" name="login" />
     </p>
	 <p>
       <label for="oldPassword">Vecchia Password:</label>
       <input type="password" id="oldPassword" name="oldPassword" maxlength="8" />
     </p>
     <p>
       <label for="password">Nuova Password:</label>
       <input type="password" id="password" name="password" maxlength="8" />
     </p>
     <p>
       <label for="confirm">Conferma nuova password:</label>
        <input type="password" id="confirm" name="confirm" maxlength="8" />
     </p>
     <input type="submit" name="submit" value="Cambia password" />
</form>
END;
  
  if ($error) {
    echo "<br /><br /><b>$error</b><br />";
  };
  echo "</fieldset>";
} else {
  /* altrimenti cabio password ok! */
  echo "Cambio Password effettuata con successo! ";
};

echo "Ritorna al <a href=\"login.php\">login</a>.";

page_end();
?>
