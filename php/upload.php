<?php
    include_once 'config.php';
    include_once '../php_excel/Classes/PHPExcel.php';

    $allowed_file_type = ['text/csv'];

    if(isset($_POST["submit_resps"]))
    {
        if(in_array($_FILES['file_resps']['type'], $allowed_file_type))
        {
            $file = $_FILES['file_resps']['tmp_name'];
            $handle = fopen($file, "r");
            $c = 0; 

            $sql_select = "SELECT province_id, MAX(CONVERT(SUBSTRING(resp_id, 5, LENGTH(resp_id) - 4),UNSIGNED INTEGER)) AS id_max FROM respondents GROUP BY province_id ORDER BY CAST(province_id AS UNSIGNED)";

            $result = mysqli_query($conn, $sql_select);
            $resultCheck = mysqli_num_rows($result);

            $id_maxs = array();

            while($row = mysqli_fetch_assoc($result)){

                $id_maxs[$row['province_id']] = $row['id_max'];
            }
            
            while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
            {
                if($c > 0)
                { 
                    //Check cellphone if exists
                    $cellphone = md5($filesop[11]);

                    $sql_select = "SELECT * FROM respondents WHERE cellphone LIKE '$cellphone';";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);

                    if($resultCheck === 0)
                    {
                        //Set id max
                        $province_id = $filesop[9];

                        $id_max = 1;

                        if(array_key_exists($province_id, $id_maxs))
                        {
                            $id_max = $id_maxs[$province_id] + 1;
                        }

                        //VN01000000000001
                        $resp_id = "VN" . sprintf("%02d", $province_id) . sprintf("%012d", $id_max);

                        $id_maxs[$province_id] = $id_max;

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

                        if(strlen($filesop[12]) > 0)
                        {
                            $email = md5($filesop[12]);
                        }
                        
                        $project_id = $filesop[13];
                        $instance_id = $filesop[14];
                        $shell_chainid = $filesop[15];
                        
                        $status = 'active';

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
    elseif(isset($_POST["submit_update_data"])){
        
        if(in_array($_FILES['file_update_data']['type'], $allowed_file_type))
        {
            $file = $_FILES['file_update_data']['tmp_name'];
            $handle = fopen($file, 'r');
            $r = 0;
            
            $cols = array();

            while(($filesop = fgetcsv($handle, 1000, ',')) !== false)
            {
                if($r == 0)
                {
                    for($c = 0; $c < count($filesop); $c++)
                    {
                        array_push($cols, $filesop[$c]);
                    }
                }
                else
                {
                    $resp_id = $filesop[0];

                    for($c = 1; $c < count($cols); $c++)
                    {
                        $set = "";

                        if(strlen($set) == 0)
                        {
                            $set = $cols[$c] . " = '" . $filesop[$c] . "'"; 
                        }
                        else
                        {
                            $set += "," + $cols[$c] . " = '" . $filesop[$c] . "'";
                        }
                    }

                    $sql_update = "update respondents set $set where resp_id LIKE '$resp_id'";

                    $stmt = mysqli_prepare($conn, $sql_update);
                    mysqli_stmt_execute($stmt);
                }

                $r++;
            }
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
            <div class="box">
                <h4>Update Data</h4>
                <input type="file" name="file_update_data" id="file_update_data" class="inputfile" accept=".csv" multiple=false/>
                <label for="file_update_data"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg><span>Choose a file</span></label>
            </div>
            <input type="submit" name="submit_update_data" class="btn-default" value="Submit"/>
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

                    $table = "<table>";

                    $tbl_row = "<tr>";
                    $tbl_row = $tbl_row ."<td rowspan='3'>Province</td>";
                    $tbl_row = $tbl_row ."<td colspan='3'>Gender</td>";
                    $tbl_row = $tbl_row ."<td colspan='28'>Gender</td>";
                    $tbl_row = $tbl_row ."</tr>";
                    $tbl_row = $tbl_row ."<tr>";
                    $tbl_row = $tbl_row ."<td rowspan='2'>Base</td>";
                    $tbl_row = $tbl_row ."<td rowspan='2'>Male</td>";
                    $tbl_row = $tbl_row ."<td rowspan='2'>Female</td>";
                    $tbl_row = $tbl_row ."<td colspan='7'>Male</td>";
                    $tbl_row = $tbl_row ."<td colspan='7'>Female</td>";
                    $tbl_row = $tbl_row ."<td colspan='7'>Male</td>";
                    $tbl_row = $tbl_row ."<td colspan='7'>Female</td>";
                    $tbl_row = $tbl_row ."</tr>";
                    $tbl_row = $tbl_row ."<tr>";
                    $tbl_row = $tbl_row ."<td>Base</td>";
                    $tbl_row = $tbl_row ."<td>Below 18</td>";
                    $tbl_row = $tbl_row ."<td>18 - 24</td>";
                    $tbl_row = $tbl_row ."<td>25 - 34</td>";
                    $tbl_row = $tbl_row ."<td>35 - 44</td>";
                    $tbl_row = $tbl_row ."<td>45 - 54</td>";
                    $tbl_row = $tbl_row ."<td>Over 54</td>";
                    $tbl_row = $tbl_row ."<td>Base</td>";
                    $tbl_row = $tbl_row ."<td>Below 18</td>";
                    $tbl_row = $tbl_row ."<td>18 - 24</td>";
                    $tbl_row = $tbl_row ."<td>25 - 34</td>";
                    $tbl_row = $tbl_row ."<td>35 - 44</td>";
                    $tbl_row = $tbl_row ."<td>45 - 54</td>";
                    $tbl_row = $tbl_row ."<td>Over 54</td>";
                    $tbl_row = $tbl_row ."<td>Base</td>";
                    $tbl_row = $tbl_row ."<td>Dưới 3,000,001 VND</td>";
                    $tbl_row = $tbl_row ."<td>4,000,001 – 7,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>4,000,001 – 7,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>7,500,001 – 12,000,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>12,000,001 – 23,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>23,500,001 VND trở lên</td>";
                    $tbl_row = $tbl_row ."<td>Base</td>";
                    $tbl_row = $tbl_row ."<td>Dưới 3,000,001 VND</td>";
                    $tbl_row = $tbl_row ."<td>4,000,001 – 7,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>4,000,001 – 7,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>7,500,001 – 12,000,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>12,000,001 – 23,500,000 VND</td>";
                    $tbl_row = $tbl_row ."<td>23,500,001 VND trở lên</td>";
                    $tbl_row = $tbl_row ."</tr>";
                    
                    
                    $sql_select = "SELECT P.name AS Province, Count(*) AS Count FROM respondents AS R INNER JOIN provinces AS P ON P.province_id = R.province_id GROUP BY P.name";

                    $result = mysqli_query($conn, $sql_select);
                    $resultCheck = mysqli_num_rows($result);
                    
                    $r = 1;

                    $labels = array("base", "total", "male", "female", "male_agegroup_base", "male_below_18", "male_18_24", "male_25_34", "male_35_44", "male_45_54", "male_over_54", "female_agegroup_base", "female_below_18", "female_18_24", "female_25_34", "female_35_44", "female_45_54", "female_over_54", "male_class_f", "male_class_base", "male_class_e", "male_class_d", "male_class_c", "male_class_b", "male_class_a", "female_class_base", "female_class_f", "female_class_e", "female_class_d", "female_class_c", "female_class_b", "female_class_a");

                    $provinces = array();

                    while($row = mysqli_fetch_assoc($result))
                    {
                        $tbl_row = $tbl_row ."<tr>";
                        $tbl_row = $tbl_row ."<td>" . $row["Province"]  . "</td>";
                        $tbl_row = $tbl_row ."<td>" . $row["Count"]  . "</td>";

                        $provinces[$row["Province"]] = array("name" => $row["Province"], "index" => $r); 
                        
                        for($c = 3; $c <= 32; $c++)
                        {
                            $tbl_row = $tbl_row ."<td>{#" . $row["Province"] . "_" . $labels[$c - 1] . "_" . $r . "_" . $c . "}</td>";
                        }

                        $tbl_row = $tbl_row ."</tr>";

                        $r++;
                    }

                    $table = $table  . $tbl_row;
                    $table = $table ."</table>";
                    
                    $wheres = array(
                        3 => "WHERE R.gender LIKE 'Nam'",
                        4 => "WHERE R.gender LIKE 'Nữ'",
                        5 => "WHERE R.gender LIKE 'Nam'",
                        6 => "WHERE R.gender LIKE 'Nam' AND year_of_birth > YEAR(CURDATE()) - 18",
                        7 => "WHERE R.gender LIKE 'Nam' AND year_of_birth >= YEAR(CURDATE()) - 24 AND year_of_birth <= YEAR(CURDATE()) - 18",
                        8 => "WHERE R.gender LIKE 'Nam' AND year_of_birth >= YEAR(CURDATE()) - 34 AND year_of_birth <= YEAR(CURDATE()) - 25",
                        9 => "WHERE R.gender LIKE 'Nam' AND year_of_birth >= YEAR(CURDATE()) - 44 AND year_of_birth <= YEAR(CURDATE()) - 35",
                        10 => "WHERE R.gender LIKE 'Nam' AND year_of_birth >= YEAR(CURDATE()) - 54 AND year_of_birth <= YEAR(CURDATE()) - 45",
                        11 => "WHERE R.gender LIKE 'Nam' AND year_of_birth < YEAR(CURDATE()) - 54",
                        12 => "WHERE R.gender LIKE 'Nữ'",
                        13 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth > YEAR(CURDATE()) - 18",
                        14 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth >= YEAR(CURDATE()) - 24 AND year_of_birth <= YEAR(CURDATE()) - 18",
                        15 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth >= YEAR(CURDATE()) - 34 AND year_of_birth <= YEAR(CURDATE()) - 25",
                        16 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth >= YEAR(CURDATE()) - 44 AND year_of_birth <= YEAR(CURDATE()) - 35",
                        17 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth >= YEAR(CURDATE()) - 54 AND year_of_birth <= YEAR(CURDATE()) - 45",
                        18 => "WHERE R.gender LIKE 'Nữ' AND year_of_birth < YEAR(CURDATE()) - 54",
                        19 => "WHERE R.gender LIKE 'Nam' AND R.householdincome IS NOT NULL",
                        20 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE 'Dưới 3,000,001 VND'",
                        21 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE '3,000,001 – 4,000,000 VND'",
                        22 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE '4,000,001 – 7,500,000 VND'",
                        23 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE '7,500,001 – 12,000,000 VND'",
                        24 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE '12,000,001 – 23,500,000 VND'",
                        25 => "WHERE R.gender LIKE 'Nam' AND R.householdincome LIKE '23,500,001 VND trở lên'",
                        26 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome IS NOT NULL",
                        27 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE 'Dưới 3,000,001 VND'",
                        28 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE '3,000,001 – 4,000,000 VND'",
                        29 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE '4,000,001 – 7,500,000 VND'",
                        30 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE '7,500,001 – 12,000,000 VND'",
                        31 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE '12,000,001 – 23,500,000 VND'",
                        32 => "WHERE R.gender LIKE 'Nữ' AND R.householdincome LIKE '23,500,001 VND trở lên'"
                    );

                    for($c = 3; $c <= 32; $c++)
                    {
                        $sql_select = "SELECT P.name AS Province, Count(*) AS Count FROM respondents AS R INNER JOIN provinces AS P ON P.province_id = R.province_id " . $wheres[$c] . " GROUP BY P.name";

                        $result = mysqli_query($conn, $sql_select);
                        $resultCheck = mysqli_num_rows($result);

                        $r = 1;
                        
                        while($row = mysqli_fetch_assoc($result))
                        {
                            $label = "{#" . $row["Province"] . "_" . $labels[$c - 1] . "_" . $provinces[$row["Province"]]["index"] . "_" . $c . "}";
                            $table = str_replace($label, $row['Count'], $table);

                            $r++;
                        }

                        foreach($provinces as $province)
                        {
                            $label = "{#" . $province["name"] . "_" . $labels[$c - 1] . "_" . $province["index"] . "_" . $c . "}";

                            $table = str_replace($label, "0", $table);
                        }
                    }
                    
                    echo $table;
                }

                if(isset($_POST["submit_export_excel"]))
                {
                    //Tạo một đối tượng Excel
                    $objectExcel = new PHPExcel();

                    $objectExcel->setActiveSheetIndex(0);

                    //Set sheet active và dat ten sheet
                    $sheet = $objectExcel->getActiveSheet()->setTitle("data");

                    $rowCount = 1;
                    $sheet->setCellValue('A'.$rowCount, 'Data');

                    $sheet->setCellValue('A'.$rowCount++, "1");

                    $objectWriter = new PHPExcel_Writer_Excel2007($objectExcel);

                    $filename = '/usr/share/export_data.xlsx';

                    $objectWriter->save($filename);

                    header('Content-Disposition: attachment; filename = "' . $filename .'"');
                    header('Content-Type: application/vnd.openxmlformatsofficedocument.spreadsheetml.sheet');
                    header('Content-Length: ' . $filesize($filename));
                    header('Content-Transfer-Encoding: binary');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: no-cache');
                    readfile($filename);
                    return;
                }
            ?>

            <script type="text/javascript" src="../js/components.js"></script>
        </form>
	</body>
</html>