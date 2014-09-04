DROP FUNCTION IF EXISTS NumeroCommesseCliente;
DROP FUNCTION IF EXISTS TempoProduzioneUnita;
DROP FUNCTION IF EXISTS PezziProdotti;
DROP FUNCTION IF EXISTS PezziInAssemblaggio;
DROP FUNCTION IF EXISTS PezziDaProdurre;
DROP FUNCTION IF EXISTS UtenteInAssemblaggio;
DROP FUNCTION IF EXISTS produzioneInAssemblaggio;
DROP FUNCTION IF EXISTS commessaTerminata;
DROP FUNCTION IF EXISTS commessaInCorso;
DROP FUNCTION IF EXISTS DataFineUltimoLavoroCommessa;

SET GLOBAL log_bin_trust_function_creators=1;

delimiter $

CREATE FUNCTION NumeroCommesseCliente(IdC SMALLINT)
RETURNS INT
BEGIN
DECLARE numeroCommesse INT;

SELECT count(*) into numeroCommesse
FROM commesse
WHERE IdCliente=IdC;

RETURN numeroCommesse;
END$

CREATE FUNCTION TempoProduzioneUnita(IdP SMALLINT)
RETURNS INT
BEGIN
DECLARE intervallo INT;
DECLARE inizio TIMESTAMP;
DECLARE fine TIMESTAMP;

SELECT DataFineAssemblaggio, DataInizioAssemblaggio INTO fine, inizio 
FROM produzione
WHERE Id=IdP;

SELECT TIMESTAMPDIFF(HOUR,inizio,fine) INTO intervallo;

RETURN intervallo;
END$

CREATE FUNCTION PezziProdotti(IdC SMALLINT)
RETURNS INT
BEGIN
DECLARE Pezzi INT;
SELECT COUNT(*) INTO Pezzi
FROM produzione
WHERE IdCommessa=IdC AND DataFineAssemblaggio IS NOT NULL;

RETURN Pezzi;
END$

CREATE FUNCTION PezziInAssemblaggio(IdC SMALLINT)
RETURNS INT
BEGIN
DECLARE Pezzi INT;

SELECT COUNT(*) INTO Pezzi
FROM produzione
WHERE IdCommessa=IdC AND DataFineAssemblaggio IS NULL;

RETURN Pezzi;
END$

CREATE FUNCTION PezziDaProdurre(IdC SMALLINT)
RETURNS INT
BEGIN
DECLARE PezziRichiesti INT;

SELECT QuantitaDaProdurre INTO PezziRichiesti
FROM commesse
WHERE Id=IdC;

SET PezziRichiesti=PezziRichiesti-(PezziProdotti(Idc)-PezziInAssemblaggio(Idc));
RETURN PezziRichiesti;
END$

CREATE FUNCTION UtenteInAssemblaggio(IdU SMALLINT,numeroLavori SMALLINT)
RETURNS BOOL
BEGIN
DECLARE LavoriInCorso INT;
DECLARE statoOperaio BOOL;

SELECT COUNT(*) INTO LavoriInCorso
FROM produzione
WHERE IdOperaio=IdU AND DataFineAssemblaggio IS NULL;

IF(LavoriInCorso>numeroLavori)
THEN
  SET statoOperaio=1;
ELSE
  SET statoOperaio=0;
END IF;

RETURN statoOperaio;
END$

CREATE FUNCTION produzioneInAssemblaggio(IdU SMALLINT)
RETURNS SMALLINT
BEGIN
DECLARE IdProduzione SMALLINT;

SELECT Id INTO IdProduzione
FROM produzione
WHERE IdOperaio=IdU AND DataFineAssemblaggio IS NOT NULL;

RETURN IdProduzione;
END$

CREATE FUNCTION commessaTerminata(IdC SMALLINT)
RETURNS BOOL
BEGIN
DECLARE terminata BOOL;

SELECT count(*) INTO terminata
FROM commesse
WHERE Id=IdC AND (PezziProdotti(Id)+PezziInAssemblaggio(Id))=QuantitaDaProdurre;

RETURN terminata;
END$

CREATE FUNCTION commessaInCorso(IdP SMALLINT)
RETURNS BOOL
BEGIN
DECLARE npezzi INT;

SELECT count(*) INTO npezzi
FROM commesse
WHERE IdProgetto=IdP AND (PezziProdotti(Id)+PezziInAssemblaggio(Id))<QuantitaDaProdurre;

IF(npezzi>0)THEN
  RETURN TRUE;
ELSE
  RETURN FALSE;
END IF;
END$

CREATE FUNCTION DataFineUltimoLavoroCommessa(Idcomm SMALLINT)
RETURNS TIMESTAMP
BEGIN
DECLARE dataUltimoLavoro TIMESTAMP;

SELECT DataFineAssemblaggio INTO dataUltimoLavoro
FROM produzione p JOIN commesse c ON c.Id=p.IdCommessa
WHERE DataFineAssemblaggio IS NOT NULL AND IdCommessa=Idcomm
ORDER by p.Id DESC LIMIT 1;

RETURN dataUltimoLavoro;
END$

delimiter ;