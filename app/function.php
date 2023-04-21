
<?php



error_reporting(0);


//=============== połączenie z bazą danych ==============

class DBObserver implements Observer {
    private $subject;

    public function __construct(Subject $subject) {
        $this->subject = $subject;
        $this->subject->attach($this);
    }

    public function update(Subject $subject) {
        // Do something when the database changes
        $query = $subject->getQuery();
        $affected_table = $this->getAffectedTable($query);
        if ($affected_table === 'filmy') {
            $db = new db();
            $db->showPopup('A new film has been added!');
        }
    }

    public function unsubscribe() {
        $this->subject->detach($this);
    }

    private function getAffectedTable($query) {
        preg_match('/UPDATE\s+(\w+)/i', $query, $matches);
        if (count($matches) > 1) {
            return $matches[1];
        }
        return null;
    }
}

class db {
    public function connect() {
        $connect = pg_connect("host=localhost dbname=check user=postgres password=kbkdh346");
        if (!$connect) {
            echo 'Błąd połączenia z bazą';
            exit();
        }
        return $connect;
    }

    public function showPopup($message) {
        echo "<script>alert('$message');</script>";
    }
}




// ======================= rejestracje ===============



class register extends db
{
    private $name;
    private $surname;
    private $email;
    private $password;
    private $r_password;
    public $error;



    function __construct($request = null)
    {
        $this->name = $request['imie'];
        $this->surname = $request['nazwisko'];
        $this->email = $request['email'];
        $this->password = $request['haslo'];
        $this->r_password = $request['r_haslo'];

        $this->error['validate'] = 0;
    }



    public function validate()
    {
        $required = ['imie', 'nazwisko', 'email', 'haslo', 'r_haslo'];

        foreach($required as $value)
        {
            $this->error[$value] = '';
        }




        if(isset($this->name) && $this->name == '')
        {
            $this->error['imie'] = 'To pole jest wymagane';
            $this->error['validate'] = 1;
        }


        if(isset($this->surname) && $this->surname == '')
        {
            $this->error['nazwisko'] = 'To pole jest wymagane';
            $this->error['validate'] = 1;
        }


        if(isset($this->email) && $this->email == '')
        {
            $this->error['email'] = 'To pole jest wymagane';
            $this->error['validate'] = 1;
        }
        elseif(isset($this->email) && $this->email != '')
        {
            $connect = $this->connect();
            $query = ("SELECT id FROM uzytkownicy WHERE email = '$this->email'");

            $wynik = pg_query($connect, $query);
            pg_close($connect);


            $unique = pg_num_rows($wynik);

            if($unique > 0){
                $this->error['email'] = 'Taki email już istnieje';
                $this->error['validate'] = 1;
            }
        }


        if(isset($this->password) && $this->password == '')
        {
            $this->error['haslo'] = 'To pole jest wymagane';
            $this->error['validate'] = 1;
        }


        if(isset($this->r_password) && $this->r_password == '')
        {
            $this->error['r_haslo'] = 'To pole jest wymagane';
            $this->error['validate'] = 1;
        }
        elseif(isset($this->r_password) && $this->r_password != $this->password)
        {
            $this->error['r_haslo'] = 'Nieprawidłowe hasło';
            $this->error['validate'] = 1;
        }



        if(!isset($this->name) || !isset($this->email) || !isset($this->password) || !isset($this->r_password))
        {
            $this->error['validate'] = 1;
        }



        if($this->error['validate'] == 1)
        {
            return $this->error;
        }
        else
        {
            return $this->register();
        }
    }



    protected function register()
    {

        $password = md5($this->password);

        $connect = $this->connect();

        $q = ("SELECT * FROM uzytkownicy");
        $wynik = pg_query($connect, $q);

        $count = pg_num_rows($wynik);



        if($count == 0)
        {
            $id_role = 1;
        }
        else
        {
            $id_role = 2;
        }

        $q2 = ("INSERT INTO uzytkownicy (imie, nazwisko, email, haslo, role) VALUES ('$this->name', '$this->surname', '$this->email', '$password', '$id_role')");
        pg_query($connect, $q2);

        $user = ("SELECT id FROM users WHERE email = '$this->email'");
        $wynik2 = pg_query($connect, $user);
        $id = pg_fetch_array($wynik2);

        pg_close($connect);

        if(!isset($_SESSION)) session_start();

        $_SESSION['auth'] = 1;
        $_SESSION['auth_id'] = $id['id'];
        $_SESSION['role_id'] = $id_role;

        if($id_role == 1)
        {
            return header("Location: ../admin/dashboard.php");
        }
        else
        {
            return header("Location: ../user/dashboard.php");
        }

    }
}




//==================== login ====================


class auth extends db
{
    private $email;
    private $password;
    public $error = Array();




    public function __construct($request = null)
    {
        $this->email = $request['email'];
        $this->password = $request['haslo'];
        $this->error['validate'] = false;
    }




    public function validate()
    {
        $validate = ['email', 'haslo', 'auth'];
        foreach($validate as $val)
        {
            $this->error[$val] = '';
        }

        $connect = $this->connect();

        if(isset($this->email) && $this->email != '')
        {
            $query = ("SELECT email FROM uzytkownicy WHERE email='$this->email'");
            $wynik = pg_query($connect, $query);

            if(pg_num_rows($wynik) == 0)
            {
                $this->error['email'] = "Zły email";
                $this->error['validate'] = true;
            }
        }
        elseif(isset($this->email) && $this->email == '')
        {
            $this->error['email'] = "To pole jest wymagane";
            $this->error['validate'] = true;
        }


        if(isset($this->password) && $this->password == '')
        {
            $this->error['haslo'] = "To pole jest wymagane";
            $this->error['validate'] = true;
        }



        if($this->error['validate'] == true)
        {
            return $this->error;
        }
        else
        {
            return $this->login();
        }
    }




    protected function login()
    {
        $pass = md5($this->password);
        $connect = $this->connect();
        $query = ("SELECT * FROM uzytkownicy WHERE email='$this->email' AND haslo='$pass' LIMIT 1");
        $wynik = pg_query($connect, $query);

        if(pg_num_rows($wynik) == 0)
        {
            $this->email['auth'] = "Błędne dane";
            return $this->error;
        }
        else
        {
            $user = pg_fetch_array($wynik);

            if(!isset($_SESSION)) session_start();
            $_SESSION['auth'] = 1;
            $_SESSION['auth_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role'];


            if($user['role'] == 1)
            {
                return header("Location: ../admin/dashboard.php");
            }
            else
            {
                return header("Location: ../user/dashboard.php");
            }
        }
    }
}







//======================= user =======================


class user extends db
{
    public $id_premiery;

    public function panel_uzytkownika()
    {
        $connect = $this->connect();
        $filmy = [];

        $query = ("SELECT filmy.id, tytul, rezyser, gatunek, od_lat FROM filmy, premiery");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $filmy[$row['id']] = array(
                $row['id'] => array(
                    'tytul' => $row['tytul'],
                    'rezyser' => $row['rezyser'],
                    'gatunek' => $row['gatunek'],
                    'od_lat' => $row['od_lat'],
                ),
            );
        }

        return $filmy;
    }


    public function poj_film($id)
    {
        $connect = $this->connect();
        $film = [];

        $query = ("SELECT id, tytul, opis, dlugosc, rezyser, gatunek, od_lat FROM filmy WHERE id = $id");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $film = array(
                    'tytul' => $row['tytul'],
                    'rezyser' => $row['rezyser'],
                    'gatunek' => $row['gatunek'],
                    'od_lat' => $row['od_lat'],
                    'dlugosc' => $row['dlugosc'],
                    'opis' => $row['opis'],
                    'id' => $row['id'],
            );
        }

        return $film;
    }


    public function premiery($id)
    {
        $connect = $this->connect();
        $premiery = [];

        $query = ("SELECT id, id_filmu, data, od  FROM premiery WHERE id_filmu = $id");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $premiery[$row['id']] = array(
                $row['id'] => array(
//                'id' => $row['tytul'],
                'id_filmu' => $row['id_filmu'],
                'data' => $row['data'],
                'od' => $row['od'],
                ),
            );
        }

        return $premiery;
    }


    public function abonamenty()
    {
        $connect = $this->connect();
        $abonamenty = [];

        $query = ("SELECT * FROM abonamenty");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $abonamenty[$row['id']] = array(
                $row['id'] => array(
                    'cena' => $row['cena'],
                    'nazwa' => $row['nazwa'],
                ),
            );
        }

        return $abonamenty;
    }



    public function kupione()
    {
        $connect = $this->connect();
        $kupione = [];

        $query = ("SELECT id_premiery FROM kupione");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $kupione[$row['id_premiery']] = array(
                $row['id_premiery'] => array(
                ),
            );
        }

        return $kupione;
    }


    public function lista_abo()
    {
        $connect = $this->connect();
        $id = $_SESSION['auth_id'];
        $abonamenty = null;


        $query = ("SELECT * FROM kupione WHERE id_uzytkownika = $id");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $abonamenty[$row['id']] = array(
                $row['id'] => array(
                    'cena' => $row['cena'],
                    'data' => $row['data'],
                    'od' => $row['od'],
                    'tytul' => $row['tytul'],
                ),
            );
        }

        return $abonamenty;
    }



    public function zakup($request, $id)
    {

        $connect = $this->connect();
        $kupione = [];
        $id_premiery = $request['id_premiery'];

        $query = ("SELECT * FROM premiery WHERE id = $id_premiery ");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $kupione = array(

                    'data' => $row['data'],
                    'od' => $row['od'],
 //                   'id_sali' => $row['id_sali'],
            );
        }

        $miejsce = $request['miejsce'];
        $cena = $request['cena'];
        $data = $kupione['data'];
        $tytul = $request['tytul'];
        $od = $kupione['od'];
        $id_uzytkownika = $_SESSION['auth_id'];


        $q2 = ("INSERT INTO kupione (id_premiery, id_filmu, cena, id_uzytkownika, data, od, tytul) VALUES ('$id_premiery', '$id', '$cena', '$id_uzytkownika', '$data', '$od', '$tytul')");
        pg_query($connect, $q2);


        $q3 = ("SELECT * FROM podsumowanie WHERE id_filmu = $id ");
        $w3 = pg_query($connect, $q3);

        $row = pg_fetch_all($w3);


        if(!isset($row) || empty($row))
        {

            $q4 = ("INSERT INTO podsumowanie (id_filmu, razem) VALUES ('$id', '$cena')");
            pg_query($connect, $q4);
        }
        else
        {

            $razem = $cena + $row[0]['razem'];
            $q5 = ("UPDATE podsumowanie SET id_filmu = '$id', razem = $razem  WHERE id_filmu = $id");
            pg_query($connect, $q5);

        }



        return header("Location: ../user/dashboard.php");
    }

}




//============= autoryzacja ============




class checkAuth
{


    // sprawdzenie czy użytkownik jest zalogowany

    public function user()
    {
        $folder = explode("/", $_SERVER['REQUEST_URI']);
        $path = 'http://'.$_SERVER['HTTP_HOST'].'/'.$folder[1].'/';

        if(!isset($_SESSION['auth']) || $_SESSION['auth'] != 1)
        {
            header("Location: $path");
        }
    }


    // sprawdzenie czy użytkownik jest zalogowany i jest adminem

    public function admin()
    {
        $folder = explode("/", $_SERVER['REQUEST_URI']);
        $path = 'http://'.$_SERVER['HTTP_HOST'].'/'.$folder[1].'/';

        if(!isset($_SESSION['auth']) || $_SESSION['auth'] != 1 || $_SESSION['role_id'] != 1)
        {
            header("Location: $path");
        }
    }
}



//====================== panel pracownika ===============




class pracownik extends db
{

    public function lista_uzytkownikow()
    {
        $connect = $this->connect();
        $users = [];

        $query = ("SELECT * FROM uzytkownicy");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $users[$row['id']] = array(
                $row['id'] => array(
                    'imie' => $row['imie'],
                    'nazwisko' => $row['nazwisko'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                ),
            );
        }

        return $users;
    }


    public function zmiana_roli_kont($request)
    {
        $connect = $this->connect();

        foreach($request as $key => $val)
        {
            $query = ("UPDATE uzytkownicy SET role = '$val'  WHERE id = $key");
            pg_query($connect, $query);
        }

        pg_close($connect);


        return header("Refresh:0");
    }


    public function panel_pracownika()
    {
        $connect = $this->connect();
        $filmy = [];

        $query = ("SELECT * FROM filmy");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $filmy[$row['id']] = array(
                $row['id'] => array(
                    'tytul' => $row['tytul'],
                    'opis' => $row['opis'],
                    'rezyser' => $row['rezyser'],
                    'gatunek' => $row['gatunek'],
                    'od_lat' => $row['od_lat'],
                    'dlugosc' => $row['dlugosc'],
                ),
            );
        }

        return $filmy;
    }



    public function lista_kupionych()
    {
        $connect = $this->connect();
        $sell = [];

        $query = ("SELECT podsumowanie.id, razem, filmy.tytul FROM podsumowanie JOIN filmy ON podsumowanie.id_filmu = filmy.id;");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $sell[$row['id']] = array(
                $row['id'] => array(
                    'razem' => $row['razem'],
                    'tytul' => $row['tytul'],
                ),
            );
        }

        return $sell;
    }

}





class film extends db
{
    public $tytul;
    public $opis;
    public $gatunek;
    public $rezyser;
    public $od_lat;
    public $dlugosc;
    public $edit;
    public $id;
    public $error;



    function __construct($request = null, $id = null)
    {
        $this->tytul = $request['tytul'];
        $this->opis = $request['opis'];
        $this->gatunek = $request['gatunek'];
        $this->rezyser = $request['rezyser'];
        $this->od_lat = $request['od_lat'];
        $this->dlugosc = $request['dlugosc'];
        $this->id = $id;

        $this->edit = $request['edit'];

        $this->error['validate'] = 0;
    }



    public function walidacja()
    {
        $required = ['tytul', 'opis', 'gatunek', 'rezyser', 'od_lat', 'dlugosc'];

        foreach($required as $value)
        {
            $this->error[$value] = '';
        }


        foreach($required as $req)
        {
            if(isset($this->$req) && $this->$req == '')
            {
                $this->error[$req] = 'To pole jest wymagane';
                $this->error['validate'] = 1;
            }

            if(!isset($this->$req))
            {
                $this->error['validate'] = 1;
            }
        }



        if($this->error['validate'] == 1)
        {
            return $this->error;
        }
        elseif(isset($this->edit) && $this->edit == 1)
        {
            return $this->zapisz_zmiany();
        }
        else
        {
            return $this->dodaj_film();
        }
    }



    public function dodaj_film()
    {
        $connect = $this->connect();
        $query = ("INSERT INTO filmy (tytul, opis, rezyser,  gatunek, od_lat, dlugosc) VALUES ('$this->tytul', '$this->opis', '$this->rezyser', '$this->gatunek', '$this->od_lat', '$this->dlugosc')");
        $x = pg_query($connect, $query);

        pg_close($connect);

        return header("Location: dashboard.php");
    }



    public function dane_filmu($id)
    {
        $connect = $this->connect();
        $film = [];

        $query = ("SELECT * FROM filmy WHERE id = $id");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $film = array(

                'tytul' => $row['tytul'],
                'opis' => $row['opis'],
                'rezyser' => $row['rezyser'],
                'gatunek' => $row['gatunek'],
                'od_lat' => $row['od_lat'],
                'dlugosc' => $row['dlugosc'],

            );
        }

        return $film;
    }


    public function zapisz_zmiany()
    {
        $connect = $this->connect();


            $query = ("UPDATE filmy SET tytul = '$this->tytul', opis = '$this->opis', rezyser = '$this->rezyser', gatunek = '$this->gatunek', od_lat = '$this->od_lat', dlugosc = '$this->dlugosc'  WHERE id = $this->id");
            pg_query($connect, $query);

        pg_close($connect);



        return header("Refresh:0");
    }
}


//===== abonamenty ===============

class abonament extends db
{
    public $nazwa;
    public $cena;

    public $edit;
    public $id;
    public $error;



    function __construct($request = null, $id = null)
    {
        $this->nazwa = $request['nazwa'];
        $this->cena = $request['cena'];

        $this->id = $id;

        $this->edit = $request['edit'];

        $this->error['validate'] = 0;
    }



    public function walidacja()
    {
        $required = ['nazwa', 'cena'];

        foreach($required as $value)
        {
            $this->error[$value] = '';
        }


        foreach($required as $req)
        {
            if(isset($this->$req) && $this->$req == '')
            {
                $this->error[$req] = 'To pole jest wymagane';
                $this->error['validate'] = 1;
            }

            if(!isset($this->$req))
            {
                $this->error['validate'] = 1;
            }
        }



        if($this->error['validate'] == 1)
        {
            return $this->error;
        }
        elseif(isset($this->edit) && $this->edit == 1)
        {
            return $this->zapisz_zmiany();
        }
        else
        {
            return $this->dodaj_abonament();
        }
    }


    public function lista_abonamentow()
    {
        $connect = $this->connect();
        $abonamenty = [];

        $query = ("SELECT * FROM abonamenty");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $abonamenty[$row['id']] = array(
                $row['id'] => array(
                    'nazwa' => $row['nazwa'],
                    'cena' => $row['cena'],
                ),
            );
        }

        return $abonamenty;
    }


    public function dodaj_abonament()
    {
        $connect = $this->connect();
        $query = ("INSERT INTO abonamenty (nazwa, cena) VALUES ('$this->nazwa', '$this->cena')");
        pg_query($connect, $query);
        pg_close($connect);

        return header("Location: ticket.php");
    }



    public function dane_abonament($id)
    {
        $connect = $this->connect();
        $abonament = [];

        $query = ("SELECT * FROM abonamenty WHERE id = $id");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {

            $abonament = array(

                'nazwa' => $row['nazwa'],
                'cena' => $row['cena'],
            );
        }

        return $abonament;
    }


    public function zapisz_zmiany()
    {
        $connect = $this->connect();


        $query = ("UPDATE abonamenty SET nazwa = '$this->nazwa', cena = '$this->cena' WHERE id = $this->id");
        pg_query($connect, $query);

        pg_close($connect);



        return header("Refresh:0");
    }
}







//============ premiery ========

class premiera extends db
{
    public $id_sali;
    public $data;
    public $od;

    public $edit;
    public $id;
    public $error;
    public $id_filmu;
    public $id_premiery;



    function __construct($request = null, $id_filmu = null, $id_premiery = null)
    {
        $this->data = $request['data'];
        $this->od = $request['od'];

        $this->id_filmu = $id_filmu;
        $this->id_premiery = $id_premiery;
        $this->edit = $request['edit'];
        $this->error['validate'] = 0;
    }



    public function walidacja()
    {
        $required = [ 'data', 'od'];

        foreach($required as $value)
        {
            $this->error[$value] = '';
        }


        foreach($required as $req)
        {
            if(isset($this->$req) && $this->$req == '')
            {
                $this->error[$req] = 'To pole jest wymagane';
                $this->error['validate'] = 1;
            }

            if(!isset($this->$req))
            {
                $this->error['validate'] = 1;
            }
        }



        if($this->error['validate'] == 1)
        {
            return $this->error;
        }
//        elseif(isset($this->edit) && $this->edit == 1)
//        {
////            return $this->zapisz_zmiany();
//        }
        else
        {
            return $this->dodaj_premier();
        }
    }





    public function lista_premier()
    {
        $connect = $this->connect();
        $premiery = [];

        $query = ("SELECT  premiery.id, data, tytul, od FROM premiery JOIN filmy ON premiery.id_filmu = filmy.id;");
        $wynik = pg_query($connect, $query);

        while ($row = pg_fetch_assoc($wynik)) {





            $premiery[$row['id']] = array(
                $row['id'] => array(
      //              'id_sali' => $row['id_sali'],
                    'data' => $row['data'],
                    'od' => $row['od'],
                    'tytul' => $row['tytul'],
                ),
            );
        }



        return $premiery;
    }



    public function dodaj_premier()
    {
        $connect = $this->connect();
        $query = ("INSERT INTO premiery (id_filmu, data, od) VALUES ('$this->id_filmu', '$this->data', '$this->od')");
        pg_query($connect, $query);
        pg_close($connect);

        return header("Location: dashboard.php");
    }




    public function usun_premier($id)
    {
        $connect = $this->connect();

        $query = ("DELETE FROM premiery WHERE id = '$id'");
        pg_query($connect, $query);
        pg_close($connect);


        return header("Refresh:0");
    }
}
