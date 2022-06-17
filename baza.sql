CREATE TABLE uzytkownicy
(
	id SERIAL PRIMARY KEY NOT NULL,
	imie VARCHAR(30),
	nazwisko VARCHAR(30),
	email VARCHAR(40) UNIQUE,
	haslo VARCHAR(100),
	role INT
);


CREATE TABLE filmy
(
	id SERIAL PRIMARY KEY NOT NULL,
	tytul VARCHAR(50),
	opis TEXT,
	gatunek VARCHAR(40),
	rezyser VARCHAR(100),
	od_lat INTEGER NOT NULL,
	dlugosc INTEGER NOT NULL
);

CREATE TABLE abonamenty
(
	id SERIAL PRIMARY KEY NOT NULL,
	nazwa VARCHAR(50),
	cena INTEGER
);


CREATE TABLE premiery
(
	id SERIAL PRIMARY KEY NOT NULL,
	id_filmu INTEGER,
    data DATE,
    od VARCHAR,
     FOREIGN KEY (id_filmu) REFERENCES filmy(id) ON DELETE CASCADE
);


CREATE TABLE kupione
(
	id SERIAL PRIMARY KEY NOT NULL,
	id_premiery INTEGER,
    id_filmu INTEGER,
    cena VARCHAR,
    id_uzytkownika INTEGER,
    data DATE,
    od VARCHAR,
    tytul VARCHAR,
     FOREIGN KEY (id_premiery) REFERENCES premiery(id) ON DELETE CASCADE,
     FOREIGN KEY (id_uzytkownika) REFERENCES uzytkownicy(id) ON DELETE CASCADE

);

CREATE TABLE podsumowanie
(
	id SERIAL PRIMARY KEY NOT NULL,
    id_filmu INTEGER,
    razem INTEGER,
     FOREIGN KEY (id_filmu) REFERENCES filmy(id) ON DELETE CASCADE

);