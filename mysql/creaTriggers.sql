DROP TRIGGER IF EXISTS bonusClienti;
DROP TRIGGER IF EXISTS upgradeCommessa;

delimiter $

CREATE TRIGGER bonusClienti AFTER INSERT ON commesse
FOR EACH ROW
BEGIN
	IF(NumeroCommesseCliente(new.IdCliente)>100)
	THEN
		UPDATE clienti
		SET Livello='oro'
		WHERE Id=new.IdCliente;	
	ELSE IF (NumeroCommesseCliente(new.IdCliente)>50)	
		THEN
			UPDATE clienti
			SET Livello='argento'
			WHERE Id=new.IdCliente;
		ELSE IF (NumeroCommesseCliente(new.IdCliente)>25)	
			THEN
				UPDATE clienti
				SET Livello='bronzo'
				WHERE Id=new.IdCliente;	  
			END IF;
		END IF;
	END IF;
END$

CREATE TRIGGER upgradeCommessa AFTER UPDATE ON progetti
FOR EACH ROW
BEGIN
DECLARE OLDcommessa SMALLINT;
DECLARE venditore SMALLINT;
DECLARE cliente SMALLINT;
DECLARE quantita INT;
DECLARE pezziProdotti INT;
DECLARE NEWquantita INT;

DECLARE flagFineFetch INT DEFAULT FALSE;
DECLARE cursoreCommesse CURSOR FOR 
		SELECT Id,IdVenditore,IdCliente,QuantitaDaProdurre
		FROM commesse
		WHERE IdProgetto=new.IdUpgrade AND (PezziProdotti(Id)+PezziInAssemblaggio(Id))<QuantitaDaProdurre;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET flagFineFetch=TRUE;

IF (old.IdUpgrade>=1 AND old.DataCreazione IS NULL AND new.Descrizione!="- in progettazione -" AND commessaInCorso(new.IdUpgrade))
	THEN
		OPEN cursoreCommesse;
		loopAggiornaCommesse: LOOP
		
			FETCH cursoreCommesse INTO OLDcommessa,venditore,cliente,quantita;
			IF flagFineFetch THEN
			  LEAVE loopAggiornaCommesse;
			END IF;
		
			UPDATE commesse
			SET QuantitaDaProdurre=PezziProdotti(OLDcommessa)+PezziInAssemblaggio(OLDcommessa)
			WHERE Id=OLDcommessa;
		
			SET pezziProdotti=PezziProdotti(OLDcommessa)+PezziInAssemblaggio(OLDcommessa);
			SET NEWquantita=quantita-pezziProdotti;
			INSERT INTO commesse (IdProgetto,IdVenditore,IdCliente,QuantitaDaProdurre)
			VALUES (new.Id,venditore,cliente,NEWquantita);
			
		END LOOP;	
		CLOSE cursoreCommesse;
	END IF;	
END$

delimiter ;