CREATE TABLE utilisateur(
   id SERIAL,
   mail VARCHAR(255)  NOT NULL,
   mdp VARCHAR(255)  NOT NULL, -- déjà haché
   nom VARCHAR(100)  NOT NULL,
   date_naissance DATE NOT NULL,
   UNIQUE(mail),
   PRIMARY KEY(id)
);

CREATE TABLE pin (
   id SERIAL,
   id_utilisateur INTEGER NOT NULL,
   pin INTEGER NOT NULL,
   duree INTEGER NOT NULL, -- Durée de validité en heures
   date_insertion TIMESTAMP,
   date_expiration TIMESTAMP NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id)
);


CREATE TABLE jeton (
   id SERIAL,
   jeton TEXT NOT NULL,
   duree DOUBLE PRECISION NOT NULL, -- Durée de validité en heures
   date_insertion TIMESTAMP NOT NULL,
   date_expiration TIMESTAMP NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id)
);


CREATE TABLE tentative_mdp_failed(
   id SERIAL,
   compteur_tentative INTEGER NOT NULL,
   date_derniere_tentative TIMESTAMP NOT NULL,
   isLocked BOOLEAN NOT NULL,
   unlock_time TIMESTAMP NOT NULL,
   id_utilisateur INTEGER NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id)
);

CREATE TABLE tentative_pin_failed(
   id SERIAL,
   compteur_tentative INTEGER NOT NULL,
   date_derniere_tentative TIMESTAMP NOT NULL,
   isLocked BOOLEAN NOT NULL,
   unlock_time TIMESTAMP NOT NULL,
   id_utilisateur INTEGER NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(id_utilisateur) REFERENCES utilisateur(id)
);

CREATE TABLE jeton_inscription(
   id SERIAL,
   mail VARCHAR(255)  NOT NULL,
   mdp TEXT NOT NULL,
   nom VARCHAR(100)  NOT NULL,
   date_naissance DATE NOT NULL,
   id_jeton INTEGER NOT NULL,
   PRIMARY KEY(id),
   UNIQUE(id_jeton),
   FOREIGN KEY(id_jeton) REFERENCES jeton(id)
);


