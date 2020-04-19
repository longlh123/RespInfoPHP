<?php
    include_once 'config.php';
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Bootstrap Template Company</title>

		<meta chatset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1'>

		<link rel='stylesheet' type='text/css' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
		<link rel='stylesheet' type='text/css' href='../css/style.css'>


		<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'></script>
		<script type='text/javascript' src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
	</head>
	<body>
        <?php
            $table = $agegroupError = $genderError = $gender = "";
            $min_age = date("Y") - 120;
            $max_age = date("Y") - 0; 

            if($_SERVER["REQUEST_METHOD"] == "POST")
            {
                if(empty($_POST["gender"]))
                {
                    $genderError = "Gender is required.";
                }
                else
                {
                    $gender = test_input($_POST["gender"]);
                }

                if(empty($_POST["min_age"]))
                {
                    $agegroupError = "Age group is required.";
                }
                else
                {
                    $min_age = $_POST["min_age"];
                }

                if(empty($_POST["max_age"]))
                {
                    $agegroupError = "Age group is required.";
                }
                else
                {
                    $max_age = $_POST["max_age"];
                }

                if(isset($_POST["btn_filter"]))
                {   
                    $table = "<table class='tbl-default'>";

                    foreach($_POST["sel_provinces"] as $province)
                    {
                        $arr = explode("@_@!", $province);

                        $sql_select = "SELECT * FROM respondents WHERE respondents.province_id LIKE '$arr[0]'";

                        if($gender !== "All")
                        {
                            $sql_select = $sql_select . " AND gender LIKE '$gender'";
                        }

                        $sql_select = $sql_select . " AND (year_of_birth >= $min_age AND year_of_birth <= $max_age)";

                        $sql_select = $sql_select . ";";

                        $result = mysqli_query($conn, $sql_select);
                        $resultCheck = mysqli_num_rows($result);

                        $table = $table . "<tr>";
                        $table = $table . "<td colspan='2'>$arr[1]</td>";
                        $table = $table . "</tr>";

                        if($resultCheck === 0)
                        {
                            $table = $table . "<tr>";
                            $table = $table . "<td colspan='2'>No data</td>";
                            $table = $table . "</tr>";
                        }
                        else
                        {
                            while($row = mysqli_fetch_assoc($result))
                            {
                                $table = $table . "<tr>";
                                $table = $table . "<td>" .$row['resp_id'] . "</td>";
                                $table = $table . "<td>" .$row['name'] . "</td>";
                                $table = $table . "</tr>";
                            }
                        }
                    }

                    $table = $table . "</table>";
                }

                if(isset($_POST["btn_csv_export"]))
                {
                    $output = fopen("php://output", "w");

                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename=data.csv');
                    fputcsv($output, array("ID", "Name", "Gender","Birth of year"));
                    fclose($output);
                }
            }

            function test_input($data){
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <h4>SELECT PROVINCES</h4>

            <select name="sel_provinces[]" multiple size="10">
                <?php
                    $sql_select = "SELECT * FROM provinces ORDER BY CAST(province_id AS UNSIGNED) ASC;";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);

                    if($resultCheck > 0)
                    {
                        while($row = mysqli_fetch_assoc($result))
                        {
                            echo "<option value='" .$row['province_id'] . "@_@!" .$row['name'] . "'>" . $row['name'] . "</option>";
                        }
                    }
                ?>
            </select>
            <br/>
            <h4>SELECT GENDER</h4>
            <input type="radio" name="gender" value="All" checked>All
            <input type="radio" name="gender" <?php if(isset($gender) && $gender == "Nam") echo "checked"; ?> value="Nam">Nam
            <input type="radio" name="gender" <?php if(isset($gender) && $gender == "Nữ") echo "checked"; ?> value="Nữ">Nữ
            <span class="error">* <?php echo $genderError ?></span>
            <br/>
            <h4>SELECT AGE GROUP</h4>
            From: <input type="number" id="min_age" name="min_age" min=<?php echo date("Y") - 120; ?> max=<?php echo date("Y") - 0; ?> value=<?php echo date("Y") - 120; ?>>
            To: <input type="number" id="max_age" name="max_age" min=<?php echo date("Y") - 120; ?> max=<?php echo date("Y") - 0; ?> value=<?php echo date("Y") - 0; ?>>
            <span class="error">* <?php echo $agegroupError ?></span>
            <br/>
            <br/>
            <input type="submit" name="btn_filter" id="btn_filter" value="Filter">
            <br/>
            <br/>
            <?php print($table); ?>
            <br/>
            <br/>
            <input type="submit" name="btn_csv_export" id="btn_csv_export" value="Export">
        </form>
	</body>
</html>