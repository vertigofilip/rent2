<?php
include('../include/navbar.php');
require_once('../../app/function.php');


$class = new user();
$abonamenty = $class->lista_abo();
?>


<div class="employee-dashboard">


    <div class="head" style="display: flex; justify-content: center; padding-top: 40px; padding-bottom: 40px;">
        <h2>Wypożyczone filmy</h2>
    </div>

    <div class="container">
        <nav class="nav">
            <a class="nav-link" href="<?php echo $path.'views/user/dashboard.php'; ?>">Panel użytkownika</a>
            <a class="nav-link" href="<?php echo $path.'views/user/rent.php'; ?>">Wypożyczenia</a>
        </nav>
    </div>

    <div class="container">


        <div class="row">
            <div class="all-movies">

                <div class="col-md">
                    <div class="table-responsive-xl">

                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">Tytuł</th>
                                <th scope="col">Dostępne od/data</th>
                                <th scope="col">Dostępne od/godzina</th>
                                <th scope="col">Cena</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($abonamenty as $key => $val)
                            {
                                ?>
                                <th scope="row"><?php echo $key ?></th>
                                <td><?php echo $val[$key]['tytul']  ?></td>
                                <td><?php echo $val[$key]['data']  ?></td>
                                <td><?php echo $val[$key]['od']  ?></td>
                                <td><?php echo $val[$key]['cena']  ?></td>



                                </tr>

                                <?php
                            }
                            ?>

                            <tr>

                            </tbody>
                        </table>



                    </div>
                </div>

            </div>
        </div>

    </div>
</div>



<?php include('../include/footer.php'); ?>


