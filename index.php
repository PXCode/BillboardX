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
        $parool = md5($_POST["parool"]);
  
        $sth = $pdo->prepare("SELECT * FROM kasutaja WHERE kasutaja = :kasutaja and parool = :parool");
        $sth->bindParam(':kasutaja', $kasutaja);
        $sth->bindParam(':parool', $parool);
        $sth->execute();

        $tulemus = $sth->fetchAll();
        if(count($tulemus) > 0){
            $_SESSION["melditud"] = true;
            $_SESSION["kasutaja"] = $kasutaja;

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
        $sth = $pdo->prepare("SELECT id, nimi FROM kategooria");
        $sth->execute();
        $tulemus = $sth->fetchAll();

        echo "<div style = 'float: right;'>Oled sisse logitud, ".$_SESSION['kasutaja']." <a href='logout.php'>Logi välja</a></div>";
        echo "<div id = 'postita_nupp'>Postita</div>";
        echo"<form id='pop_up'>
                <a href='#' id='sule_aken'>Sule aken</a><br>
       <a href='logout.php'>Logi välja</a>         <select name='kat_id'>";
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
        <a href="#" >Registreeru</a>
    </form>
        ';
    }
    ?>
</div>

<div id="lingid">
    <div class="container_lingid" align="center">
        <div class="cont_lingid">
            <a href="#" >Reeglid</a>
            <a href="#" >Info</a>
            <a href="#" >KKK</a>
            <a href="#" >Kontakt</a>
        </div>
    </div>
</div>

<div id="raam">

    <?php
    //
    //kategooriate/sisu laadimine AB-st
    ///////////////////////////////////
    $host = "localhost";
    $user = 'infoorum_rauno';
    $pass = 'k33ruline';
    $database = "infoorum_db";

    try{
        $sth = $pdo->prepare("SELECT * FROM kategooria");
        $sth->execute();
        $kategooriad = $sth->fetchAll();

        $sth = $pdo->prepare("SELECT * FROM postitus");
        $sth->execute();
        $sisud = $sth->fetchAll();

        foreach($kategooriad as $kateg){
            echo '<div class="container">
                <div class="cont">
                    <div class="kateg">'.$kateg['nimi'].'</div>';
            $kategooria_id = $kateg['id'];

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

    <!--    <div class="container">
            <div class="cont">
                <div class="kateg">Kategooria 1</div>
                <div class="sisu">Sisu1</div>
                <div class="sisu">Sisu2</div>
                <div class="sisu">Sisu3</div>
                <div class="sisu">Sisu4</div>
                <input type="submit" class="lisa_sisu" value="lisa sisu">
            </div>
        </div>
    </div>
    <input type="submit" id="lisa_kat" value="lisa kategooria">-->

    <!-- Added cdn check.-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.1/jquery.min.js"></script>
    <script>
        if (typeof jQuery == 'undefined') {
            document.write(unescape("%3Cscript src='/jquery-2.2.1.min.js' type='text/javascript'%3E%3C/script%3E"));
        }
    </script>

    <script>
        $( document ).ready(function() {
            $('#postita_nupp').on('click', function(event){
                document.getElementById('pop_up').style.display='block';
                document.getElementById('tume_taust').style.display='block';
                event.preventDefault();
            });
            $('#sule_aken').on('click', function(){
                document.getElementById('pop_up').style.display='none';
                document.getElementById('tume_taust').style.display='none';
            });

            $("#pop_up").submit(function(){
                var values = $(this).serialize();
                $.ajax({
                    url: "postita.php",
                    type: "post",
                    data: values ,
                    async: false,

                    success: function (vastus) {
                        // you will get response from your php page (what you echo or print)
                        alert(vastus);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }

                });
            });

            $('#sisu_lisamine').keyup(function(){
                $("#counter").text($(this).val().length);
            });
            
            $('.kustuta_nupp').on('click', function(e){
                if(!confirm("Tahad oma postitust kustutada?")){
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>