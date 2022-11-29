<?php include_once('../config.php'); ?>

<?php
    // Get all students when using this page to avoid latency and bugs
    function getAllUsers() {
        $sql = "SELECT * FROM `users`";
        $users = getMultipleRecords($sql);
        $users_fixed = array();
        foreach($users as $key => $value) {
            $users_fixed[$value['id']] = $value['neptuncode'];
        }
        return $users_fixed;
    }
?>

<?php
    $file = $_FILES['fileToUpload'];
    $opportunity_id = filter_input(INPUT_POST, "opportunity_id", FILTER_SANITIZE_STRING);
    if($file) {
        // First we upload the file, then do the SQL transaction
        $fileUpload = uploadFile($file);
        if(!$fileUpload) {
            $_SESSION['error_msg'] = "There was an error while importing list.";
            header("location: " . BASE_URL . "opportunities/opportunityView.php?view_opportunity=" . $opportunity_id);
            exit(0);
        }
        $fileParseResults = parseFile($file, $opportunity_id);
        if($fileParseResults[0] == true) {
            if($fileParseResults[1] != "") {
                $_SESSION['success_msg'] = "You have successfully imported the list of students for this opportunity. Number: " . $fileParseResults[2];
                $_SESSION['error_msg'] = "The following students are not imported because of duplication: " . $fileParseResults[1];
            } else {
                $_SESSION['success_msg'] = "You have successfully imported the list of students for this opportunity. Number: " . $fileParseResults[2];
            }
            header("location: " . BASE_URL . "opportunities/opportunityView.php?view_opportunity=" . $opportunity_id);
            exit(0);
        }
    } else {
        $_SESSION['error_msg'] = "File not found to import!";
        header("location: " . BASE_URL . "opportunities/opportunityView.php?view_opportunity=" . $opportunity_id);
        exit(0);
    }

    function uploadFile($file){
        $target_dir = "importedFiles/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        // Check if file already exists for some reason
        if (file_exists($target_file)) {
          return false;
        }

        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            return true;
        } else {
            return false;
        }
    }

    function parseFile($file, $opportunity_id) {
        $filePath = "importedFiles/" . $file["name"];
        $f = fopen($filePath, "r");
        $i = 0;
        $students = "";
        $user_data = array();
        $users = getAllUsers();
        $lines = [];


        foreach (file($filePath) as $line) {
            $lines[] = $line;
            // $content = explode("\n", $line);
            // foreach($content as $key => $neptun) {

            //     $neptun = str_replace(" ", "", $neptun);
            //     $index = array_search($neptun, $users);
            //     if ($index) {
            //         $i++;
                    
            //         if (!canImportPointsForOpportunityByID($opportunity_id, $index)) {
            //             if ($students == "") {
            //                 $students = $neptun;
            //             } else {
            //                 $students = $students . ", " . $neptun;
            //             }
            //             continue;
            //         }
            //         $sql = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
            //         $result = modifyRecord($sql, 'ii', [$opportunity_id, $index]);
            //     }
            // }
        }


        foreach($lines as $line => $neptun_code) {
            $sql = "SELECT id FROM `users` WHERE neptuncode = ?";
            $index = getSingleRecord($sql, 's', [$neptun_code]);
            if($index) {
                $i++;
                if (!canImportPointsForOpportunityByID($opportunity_id, $index['id'])) {
                     if ($students == "") {
                         $students = $neptun_code;
                     } else {
                         $students = $students . ", " . $neptun_code;
                     }
                     continue;
                 }
                 $sql = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
                 $result = modifyRecord($sql, 'ii', [$opportunity_id, $index['id']]);
            }
        }

        // while (!feof($f)) {
        //     // $content = explode(PHP_EOL, fgets($f));
        //     // Alter solution
        //     $row = fgets($f);
        //     if (!empty($row)) {
        //         $index = array_search($row, $users);
        //         $i++;
        //         // if ($index) {
        //             $neptun_code = $users[$index];
        //             if (!canImportPointsForOpportunityByID($opportunity_id, $index)) {
        //                 if ($students == "") {
        //                     $students = $neptun_code;
        //                 } else {
        //                     $students = $students . ", " . $neptun_code;
        //                 }
        //                 continue;
        //             }
        //             $sql = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
        //             $result = modifyRecord($sql, 'ii', [$opportunity_id, $index]);
        //         // }
        //     }

            // Loop through user_data and check if the Neptun codes are valid
            // foreach ($user_data as $key => $neptun_code) {
            //     // Actual check with sql -> we get the user ID
            //     $sql = "SELECT `id` FROM `users` WHERE `neptuncode` = ?";
            //     $result = getSingleRecord($sql, 's', [$neptun_code]);

            //     if (!empty($result)) {
            //         if (!canImportPointsForOpportunityByID($opportunity_id, $result['id'])) {
            //             if ($students == "") {
            //                 $students = $neptun_code;
            //             } else {
            //                 $students = $students . ", " . $neptun_code;
            //             }
            //             continue;
            //         }
            //         $insertStatement = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
            //         $insertResult = modifyRecord($insertStatement, 'ii', [$opportunity_id, $result['id']]);
            //     }
            // }


            // foreach($content as $key => $neptun_code){
            //     $sql = "SELECT id FROM `users` WHERE neptuncode = ?";
            //     $user_data = getSingleRecord($sql, 's', [$neptun_code]);
            //     if(! empty($user_data)) {
            //         // Check if user already exists, if so break the current loop iteration
            //         if(! canImportPointsForOpportunityByID($opportunity_id, $user_data['id'])) {
            //             // array_push($students, $neptun_code);
            //             if($students == "") {
            //                 $students = $neptun_code;
            //             } else {
            //                 $students = $students . ", " . $neptun_code;
            //             }
            //             continue;
            //         }
            //         $sql = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
            //         $result = modifyRecord($sql, 'ii', [$opportunity_id, $user_data['id']]);
            //     }
            // }
        // } 
        deleteFile($filePath);
        return array(true, $students, $i);
    }

    function deleteFile($filePath){
        unlink($filePath);
        return true;
    }
?>