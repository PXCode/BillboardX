<?php
// enable sessions
session_start();
//header('Content-Type: text/html; charset=UTF-8');
//andmebaasiga ühendamine:
include('connect.php');

//kui meldimis andmed on postitatud
if(!empty($_POST["kasutaja"]) && !empty($_POST["parool"])){
    try{
        $kasutaja = $_POST["kasutaja"];
        $parool = $_POST["parool"];

        $sth = $pdo->prepare("SELECT * FROM kasutaja WHERE kasutaja = :kasutaja");
        $sth->bindParam(':kasutaja', $kasutaja);
        $sth->execute();

        $tulemus = $sth->fetchAll();

        if(count($tulemus) > 0){
            //lähen tulemus array sisse ja võtan sealt parooli key value
            foreach($tulemus as $t){
                $paroolABst= $t['parool'];
                $aktiveeritud = $t['aktiveeritud'];
            }

            if(password_verify($parool, $paroolABst) && $aktiveeritud == 1){
                $_SESSION["melditud"] = true;
                $_SESSION["kasutaja"] = $kasutaja;
            }
            //else{echo "Vale parool";}

            // redirect user to home page, using absolute path
            $host = $_SERVER["HTTP_HOST"];
            $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
            header("Location: http://$host$path/index.php");
            exit;


        }else{
            echo "Kasutajanimi või parool on vale";

        }
    }catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
}
//else if(!isset($_SESSION["melditud"]) && (empty($_POST["kasutaja"]) || empty($_POST["parool"]))) {
// echo "Esines tühja välja";}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et" lang="et">
<head>
    <title>Infoorum</title>
    <meta charset='utf-8'>
    <link rel="stylesheet" type="text/css" href="style.css">
    <!-- Added cdn check.-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="cdn.js"></script>
    <script src="script.js"></script>
</head>
<body>
<div id="päis">
    <a id="logo" href="index.php">Infoorum</a>
    <!-- see on tume taust pop_up(modal)'i jaoks: -->
    <div id="tume_taust"></div>

    <?php
    //kui on toimunud edukas sisselogimine, näita sisse loginud kasutajale:
    if (isset($_SESSION["melditud"]) && $_SESSION["melditud"] == true){
        //leian kõik kategooriad, salvestan array-na $tulemusse
        $sth = $pdo->prepare("SELECT * FROM get_kategooriad_idni");
        $sth->execute();
        $tulemus = $sth->fetchAll();


        echo "<div id = 'melditud'><div style = 'float: right;'>Oled sisse logitud, <span id='kasutaja'>".$_SESSION['kasutaja']."</span> <a href='logout.php'>Logi välja</a></div>";
        echo "<div id = 'postita_nupp'>Postita</div></div>";

        echo"<form id='pop_up'>
                <a href='#' id='sule_aken'>Sule aken</a><br>
               <select name='kat_id'>";
        foreach($tulemus as $t){
            echo "<option value =".$t['id'].">". ucfirst($t['nimi']) ." </option>";
        }
        echo "</select><br>
                <label for='pealkiri'>Pealkiri</label><br>
                <input type='text' name='pealkiri' placeholder='Pealkiri'><br>
                <label for='sisu_lisamine'>Sisu</label><br>
                <textarea form='pop_up' name = 'sisu' id='sisu_lisamine' rows='4' cols='50'></textarea><div id='counter'>0</div><br>
                <input type='submit' value='Postita'><br>

            </form>";

    }else{//Log-in form:
        echo'
        <form method="post" action="index.php">
            <label for="kasutaja">Kasutaja: </label>
            <input type="text" name="kasutaja" placeholder="Kasutaja" id="kasutaja">
            <label for="parool">Parool: </label>
            <input type="password" name="parool" placeholder="Parool" id="parool">
            <input type="submit" value="Sisene">
        <a href="regamine.php" >Registreeru</a>
    </form>
        ';
    }
    ?>
</div>

<div id="lingid">
    <a href="#" >Reeglid</a>
    <a href="#" >Info</a>
    <a href="#" >KKK</a>
    <a href="kontakt_katsetus.php" >Kontakt</a>
</div>

<div id="raam">

    <?php
    //
    //kategooriate/sisu laadimine AB-st
    ///////////////////////////////////
    try{
        $sth = $pdo->prepare("SELECT * FROM get_kategooriad");
        $sth->execute();
        $kategooriad = $sth->fetchAll();

        $sth = $pdo->prepare("SELECT * FROM get_postitused");
        $sth->execute();
        $sisud = $sth->fetchAll();

        foreach($kategooriad as $kateg){
            $kategooria_id = $kateg['id'];
            echo '<div class="container">
            <div class="kateg">'.$kateg['nimi'].'</div>
                <div class="cont" id = "'.$kategooria_id.'">
                    ';


            //ebeaefektiivne - iga kategooria korral käib sisu massiivi läbi
            foreach($sisud as $sis){
                if($sis['kat_id'] == $kategooria_id){
                    echo "<div class='sisu'>";
                    echo "<p class ='sisu_pealkiri'>".$sis['pealkiri']."</p>";
                    echo "<p class ='sisu_autor'>".$sis['autor']."</p>";
                    echo '<div class = "sisu_tekst">'.$sis['sisu'].'</div>';
                    //case insensitive comparing(strcasecmp)
                    if(!empty($_SESSION["kasutaja"]) && strcasecmp ($_SESSION["kasutaja"], $sis['autor']) == 0){
                        echo "<a class='kustuta_nupp' href='kustuta.php?id=".$sis['id']."'>Kustuta postitus</a>";
                    }

                    echo "</div>";
                }
            }
            echo '</div></div>';

        }
    }catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    ?>
</div>
</body>
</html>
