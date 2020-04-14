<?php
    include_once 'config.php';
    
    $allowed_file_type = ['text/csv'];

    if(isset($_POST["submit_resps"]))
    {
        if(in_array($_FILES['file_resps']['type'], $allowed_file_type))
        {
            $file = $_FILES['file_resps']['tmp_name'];
            $handle = fopen($file, "r");
            $c = 0;

            while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
            {
                if($c > 0)
                { 
                    $resp_id = $filesop[0];
                    $name = $filesop[1];
                    $year_of_birth = $filesop[2];
                    $gender = $filesop[3];
                    $address = $filesop[4];
                    $house_no = $filesop[5];
                    $street = $filesop[6];
                    $ward = $filesop[7];
                    $district = $filesop[8];
                    $province_id = $filesop[9];

                    $phone = $email = $resource = "";

                    if(strlen($filesop[10]) > 0)
                    {
                        $phone = md5($filesop[10]);
                    }
                    
                    $cellphone = md5($filesop[11]);
                    
                    if(strlen($filesop[12]) > 0)
                    {
                        $email = md5($filesop[12]);
                    }
                    
                    if(strlen($filesop[13]) > 0)
                    {
                        $resource = md5($filesop[13]);
                    }
                    
                    $status = 'active';
                    
                    $sql_select = "SELECT * FROM respondents WHERE cellphone LIKE '$cellphone';";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);

                    if($resultCheck === 0)
                    {
                        $sql_insert = "insert into respondents(resp_id,name,year_of_birth,gender,address,house_no,street,ward,district,province_id,phone,cellphone,email,resource,status) values('$resp_id','$name',$year_of_birth,'$gender','$address','$house_no','$street','$ward','$district','$province_id','$phone','$cellphone','$email','$resource','$status')";
                    
                        $stmt = mysqli_prepare($conn, $sql_insert);
                        mysqli_stmt_execute($stmt);
                    }
                }

                $c = $c + 1;
            }
        }
        else
        {
            echo "Invalid File Type. Upload CSV File.";
        }
    }
    elseif(isset($_POST["submit_status_of_resps"]))
    {
        if(in_array($_FILES['file_status_of_resps']['type'], $allowed_file_type))
        {
            $file = $_FILES['file_status_of_resps']['tmp_name'];
            $handle = fopen($file, "r");
            $c = 0;
            
            while(($filesop = fgetcsv($handle, 1000, ',')) !== false)
            {
                if($c > 0)
                {
                    $resp_id = $filesop[0];

                    $sql_select = "SELECT * FROM respondents WHERE resp_id = $resp_id;";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);
                    
                    if($resultCheck === 1)
                    {
                        $status_of_reason = $filesop[1];
                        $status = $filesop[2];

                        $sql_update = "update respondents set status_of_reason = '$status_of_reason', status = '$status', status_of_reason_at = current_timestamp where resp_id = $resp_id";
                        
                        $stmt = mysqli_prepare($conn, $sql_update);
                        mysqli_stmt_execute($stmt);
                    }
                }

                $c = $c + 1;
            }
        }
        else
        {
            echo "Invalid File Type. Upload CSV File.";
        }
    }
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
        <form action="" method="post" enctype="multipart/form-data" name="frmCSVImport" id="frmCSVImport">
            <div class="form-group">
                <label for="input_file">Upload Respondents</label>
                <input type="file" name="file_resps" id="file_resps" accept=".csv" size="150">
                <p class="help-block">Only Excel/ CSV File Import.</p>
            </div>
            <button type="submit" class="btn btn-default" name="submit_resps" value="submit_resps">Upload</button>
            <br/>
            <br/>
            <div class="form-group">
                <label for="input_file">Upload The Status Of Respondents</label>
                <input type="file" name="file_status_of_resps" id="file_status_of_resps" accept=".csv" size="150">
                <p class="help-block">Only Excel/ CSV File Import.</p>
            </div>
            <button type="submit" class="btn btn-default" name="submit_status_of_resps" value="submit_status_of_resps">Upload</button>
        </form>
	</body>
</html>