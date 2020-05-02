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
                    
                    $project_id = $filesop[13];
                    $instance_id = $filesop[14];
                    $shell_chainid = $filesop[15];
                    
                    $status = 'active';
                    
                    $sql_select = "SELECT * FROM respondents WHERE cellphone LIKE '$cellphone';";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);

                    if($resultCheck === 0)
                    {
                        $sql_insert = "insert into respondents(resp_id,name,year_of_birth,gender,address,house_no,street,ward,district,province_id,phone,cellphone,email,project_id,instance_id,shell_chainid,status) values('$resp_id','$name',$year_of_birth,'$gender','$address','$house_no','$street','$ward','$district','$province_id','$phone','$cellphone','$email','$project_id','$instance_id','$shell_chainid','$status')";
                    
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
                    $project_id = $filesop[1];
                    $status_of_contact = $filesop[2];
                    
                    $sql_insert = "insert into respondents_histories(resp_id,project_id,status_of_contact) values('$resp_id','$project_id','$status_of_contact')";

                    $stmt = mysqli_prepare($conn, $sql_insert);
                    mysqli_stmt_execute($stmt);
                }

                $c = $c + 1;
            }
        }
        else
        {
            echo "Invalid File Type. Upload CSV File.";
        }
    }
    elseif(isset($_POST["submit_add_new_project"]))
    {
        $project_name = $_POST["text_project_name"];
        $project_description = $_POST["text_description"];

        if(strlen($project_name) >= 0 && strlen($project_description) >= 0)
        {
            $sql_select = "SELECT * FROM projects WHERE project_id LIKE '$project_name';";

            $result = mysqli_query($conn, $sql_select);
            $resultCheck = mysqli_num_rows($result);

            if($resultCheck == 0)
            {
                $sql_insert = "insert into projects(project_id,description) values('$project_name','$project_description')";

                $stmt = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_execute($stmt);
            }
        }
    }
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Bootstrap Template Company</title>

		<meta chatset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1'>

        <link rel='stylesheet' type='text/css' href='../css/components.css'>
	</head>
	<body>
        <form action="" method="post" enctype="multipart/form-data" name="frmCSVImport" id="frmCSVImport">
            <div class="box">
                <h4>Add a new project</h4>
                <label for="text_project">Project name:</label>
                <input type="text" name="text_project_name" />
                <br/>
                <label for="text_description">Description:</label>
                <textarea name="text_description" rows="4" cols="50"></textarea>
            </div>
            <input type="submit" name="submit_add_new_project" class="btn-default" value="Add" />
            <br/>
            <div class="box">
                <h4>Upload List of Respondents</h4>
                <input type="file" name="file_resps" id="file_resps" class="inputfile" accept=".csv" multiple=false/>
                <label for="file_resps"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg><span>Choose a file</span></label>
            </div>
            <input type="submit" name="submit_resps" class="btn-default" value="Submit"/>
            <br/>
            <br/>
            <div class="box">
                <h4>Upload The status of Respondents</h4>
                <input type="file" name="file_status_of_resps" id="file_status_of_resps" class="inputfile" accept=".csv" multiple=false/>
                <label for="file_status_of_resps"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg><span>Choose a file</span></label>
            </div>
            <input type="submit" name="submit_status_of_resps" class="btn-default" value="Submit"/>
            <br/>
            <br/>
            <input type="submit" name="submit_show_report" class="btn-default" value="Show" />
            <input type="submit" name="submit_export_excel" class="btn-default" value="Export" />
            <?php
                if(isset($_POST["submit_show_report"]))
                {
                    $sql_select = "SELECT respondents_histories.status_of_contact AS status_of_contact_label, Count(*) AS number_of_respondents FROM respondents_histories INNER JOIN (SELECT resp_id, MAX(participate_at) AS participate_at FROM respondents_histories GROUP BY(resp_id)) max_participate ON respondents_histories.resp_id = max_participate.resp_id AND respondents_histories.participate_at = max_participate.participate_at GROUP BY respondents_histories.status_of_contact";
            
                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);
            
                    $table = "";
            
                    if($resultCheck > 0)
                    {
                        $table = "<table>";
            
                        while($row = mysqli_fetch_assoc($result))
                        {
                            $tbl_row = "<tr><td>" . $row['status_of_contact_label'] . "</td><td>" . $row['number_of_respondents'] . "</td></tr>";
            
                            $table = $table  . $tbl_row;
                        }
            
                        $table = $table  . "</table>";
                    }
            
                    echo $table;
                }

                if(isset($_POST["submit_export_excel"]))
                {
                    $sql_select = "SELECT respondents_histories.status_of_contact AS status_of_contact_label, Count(*) AS number_of_respondents FROM respondents_histories INNER JOIN (SELECT resp_id, MAX(participate_at) AS participate_at FROM respondents_histories GROUP BY(resp_id)) max_participate ON respondents_histories.resp_id = max_participate.resp_id AND respondents_histories.participate_at = max_participate.participate_at GROUP BY respondents_histories.status_of_contact";
                    
                    $resultSet = mysqli_query($conn, $sql_select) or die("database error:". mysqli_error($conn));
                    $developersData = array();
                    while( $developer = mysqli_fetch_assoc($resultSet) ) {
                        $developersData[] = $developer;
                    }	

                    $fileName = "webdamn_export_".date('Ymd') . ".xls";			
                    header("Content-Type: application/vnd.ms-excel");
                    header("Content-Disposition: attachment; filename=\"$fileName\"");	
                    $showColoumn = false;
                    if(!empty($developersData)) {
                    foreach($developersData as $developerInfo) {
                        if(!$showColoumn) {		 
                        echo implode("\t", array_keys($developerInfo)) . "\n";
                        $showColoumn = true;
                        }
                        echo implode("\t", array_values($developerInfo)) . "\n";
                    }
                    }
                    exit;  
                }
            ?>

            <script type="text/javascript" src="../js/components.js"></script>
        </form>
	</body>
</html>