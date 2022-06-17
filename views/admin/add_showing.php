<?php
include('../include/navbar.php');
require_once('../../app/function.php');


$class = new premiera();
$error = $class->walidacja();




if(isset($_POST['submit']))
{
    $request = $_POST['data'];
    $class = new premiera($request, $_GET['id_filmu']);

    $error = $class->walidacja();
}


?>

<div class="new-date">
    <div class="head" style="display: flex; justify-content: center; padding-top: 40px; padding-bottom: 40px;">
        <h2>Dodawanie premierau</h2>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md">
                <div class="add-date">

                    <form method="post" action="">

                


                        <div class="form-group col-md-4">
                            <label for="exampleFormControlFile1">Od daty: </label>
                            <input type="date" class="form-control" name="data[data]" >
                            <?php echo $error['data'] ?>

                        </div>

                        <div class="form-row custom">
                            <div class="form-group col-md-2">
                                <label for="exampleFormControlFile1">Od godziny: </label>
                                <input type="time"  class="form-control duration" name="data[od]" required>
                                <?php echo $error['od'] ?>


                            </div>
                        </div>

                        <input type="hidden" name="data[edit]" value="0">

                        <div class="submit-button">
                            <button type="submit" name="submit">Dodaj nową datę</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>




<?php include('../include/footer.php'); ?>

