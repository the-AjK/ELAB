SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS produzione;
DROP TABLE IF EXISTS commesse;
DROP TABLE IF EXISTS listacomponentiprogetto;
DROP TABLE IF EXISTS progetti;
DROP TABLE IF EXISTS componenti;
DROP TABLE IF EXISTS utenti;
DROP TABLE IF EXISTS clienti;

create table utenti(
	Id SMALLINT AUTO_INCREMENT PRIMARY KEY, 
	Categoria SET('operai','tecnici','venditori','amministratori') NOT NULL,
	Nome VARCHAR(20) NOT NULL,
	Cognome VARCHAR(20) NOT NULL,
	DataAssunzione TIMESTAMP NOT NULL DEFAULT NOW(),
	Username CHAR(8),
	Password CHAR(40)
)ENGINE=InnoDB;

create table componenti(
	Id CHAR(4), 
	SiglaProduttore VARCHAR(20) NOT NULL,
	Descrizione VARCHAR(100),
	PRIMARY KEY (Id)
)ENGINE=InnoDB;

create table progetti(
	Id SMALLINT AUTO_INCREMENT,
	IdUpgrade SMALLINT NULL,
	IdTecnico SMALLINT NOT NULL,
	IdTecnicoMOD SMALLINT NULL,
	Descrizione VARCHAR(100),
	DataCreazione TIMESTAMP NULL,
	DataUltimaModifica TIMESTAMP NULL,
	PRIMARY KEY (Id),
	FOREIGN KEY (IdTecnico) REFERENCES utenti (Id),
	FOREIGN KEY (IdTecnicoMOD) REFERENCES utenti (Id),
	FOREIGN KEY (IdUpgrade) REFERENCES progetti (Id)
)ENGINE=InnoDB;

create table commesse(
	Id SMALLINT NOT NULL AUTO_INCREMENT,
	IdProgetto SMALLINT NOT NULL,
	IdVenditore SMALLINT NOT NULL,
	IdCliente SMALLINT NOT NULL,
	QuantitaDaProdurre INT NOT NULL,
	DataCommessa TIMESTAMP NOT NULL DEFAULT NOW(),
	PRIMARY KEY (Id),
	FOREIGN KEY (IdProgetto) REFERENCES progetti (Id),
	FOREIGN KEY (IdVenditore) REFERENCES utenti (Id),
	FOREIGN KEY (IdCliente) REFERENCES clienti (Id)
)ENGINE=InnoDB;

create table clienti(
	Id SMALLINT NOT NULL AUTO_INCREMENT,
	Nome VARCHAR(20),
	Cognome VARCHAR(20),
	Societa VARCHAR(40) NOT NULL,
	Livello SET('new','bronzo','argento','oro') NOT NULL DEFAULT 'new',
	DataPrimoOrdine TIMESTAMP NOT NULL DEFAULT NOW(),
	PRIMARY KEY (Id)
)ENGINE=InnoDB;
	
create table produzione(
	Id SMALLINT NOT NULL AUTO_INCREMENT,
	IdCommessa SMALLINT NOT NULL,
	IdOperaio SMALLINT NOT NULL,
	DataInizioAssemblaggio TIMESTAMP NOT NULL DEFAULT NOW(),
	DataFineAssemblaggio TIMESTAMP NULL,
	PRIMARY KEY (Id),
	FOREIGN KEY (IdOperaio) REFERENCES utenti (Id),
	FOREIGN KEY (IdCommessa) REFERENCES commesse (Id)
                             ON DELETE CASCADE	
)ENGINE=InnoDB;

create table listacomponentiprogetto(
	IdP SMALLINT,
	IdC CHAR(4),
	NumeroPezzi INT NOT NULL,
	PRIMARY KEY (IdP,IdC),
	FOREIGN KEY (IdP) REFERENCES progetti (Id)
					  ON DELETE CASCADE,
	FOREIGN KEY (IdC) REFERENCES componenti (Id)
)ENGINE=InnoDB;

   


