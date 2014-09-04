<?php
/* pagina di login e gestore del login stesso */
require("functions.php");

/* se l'utente e` gia` autenticato, controlla il tipo di utente*/
session_start();
if ($_SESSION['logged']){
	$wheretogo="/operaio.php";
 	switch (user_check($_SESSION['logged'])){
		case 'operai':
			$wheretogo="/operaio.php";
		    break;
		case 'tecnici':
			$wheretogo="/tecnico.php";
		    break;
		case 'venditori':
			$wheretogo="/venditore.php";
		    break;
		case 'amministratori':
			$wheretogo="/admin.php";
		    break;
	}
	header("Location: " . base_url() . $wheretogo);
}
  
/* verifica se sono stati immessi dei dati di login e se sono corretti */
$login=$_POST['login'];
$pwd=$_POST['pwd'];

if ($login && (SHA1($pwd) == get_pwd($login))) {
  //se utilizzo i cookies
  if(USE_COOKIES){
    $hashcode=SHA1($login.$chiaveSegreta);
	setcookie('logged',$login.",".$hashcode);
  }else{
    //salvo il login nella sezione
    $_SESSION['logged']=$login;
  }  
  switch (user_check($login)){
    case 'operai':
	  $wheretogo="operaio.php";
	  break;
	case 'tecnici':
	  $wheretogo="tecnico.php";
	  break;
    case 'venditori':
	  $wheretogo="venditore.php";
	  break;	
	case 'amministratori':
	  $wheretogo="admin.php";
	  break;	
	}
	header("Location: ".$wheretogo);	//reindirizzo l'utente nella pagina corretta
} else {
  loginPage_start();
};

?>

<div align=center>
<br /><br /><br />
<img alt="Electric LAB logo" src="images/logo.png" /> <p></p>

<form method="post" action="login.php">
    <p>
      <label for="login">Username:</label> 
      <input type="text" id="login" name="login" />
    </p>
    <p>
      <label for="pwd">Password:  </label>
      <input type="password" id="pwd" name="pwd" maxlength="8" />
    </p>
    <input type="submit" value="Login" style=" width:100px"/>
</form>

<p>
  <a href="changepwd.php">Cambia Password</a>
</p>
</div>
<br /><br />
<div align=center>
ELAB - Electronic_LAB S.r.l.<br />
AjK (C) 2013 All Rights Reserved.
</div>

<?php
   if ($login or $pwd) {
     echo "<p>Autenticazione fallita!</p>";
   }
   
  page_end();
?>
