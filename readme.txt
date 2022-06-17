Aby odpalić projekt należy pobrać xamppa
https://www.apachefriends.org/pl/index.html?fbclid=IwAR3We9KIZ4AuYVkoPza8_aRkQraPS46zPUMAsyBlzlb09e5WskaNzPLYG2Y
, postgresql 
https://www.postgresql.org/download/ 
oraz zaimportować bazę z pliku baza.sql i wpisać jej dane do funkcji connect w pliku function.php




Po wypakowaniu w c/xampp/htdocs i wpisaniu w przeglądarkę localhost/rent2 ukaże się powyższa strona.
Pierwsze zarejestrowane konto staję się administratorem, mającym sposobność nadawania uprawnień później zrobionym kontom.
Następnie utworzone konta domyślnie będą kontem klienta. Aplikacja posiada system rozpoznawania rodzajów kont oparty na sesji.
Użytkownik nie może wejść do panelu administratora i na odwrót.
