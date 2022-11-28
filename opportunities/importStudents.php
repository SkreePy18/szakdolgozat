<?php include_once('../config.php'); ?>

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
                $_SESSION['success_msg'] = "You have successfully imported the list of students for this opportunity.";
                $_SESSION['error_msg'] = "The following students are not imported because of duplication: " . $fileParseResults[1];
            } else {
                $_SESSION['success_msg'] = "You have successfully imported the list of students for this opportunity.";
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
        while (!feof($f)) {
            $content = explode(PHP_EOL, fgets($f));
            foreach($content as $key => $neptun_code){
                $sql = "SELECT id FROM `users` WHERE neptuncode = ?";
                $user_data = getSingleRecord($sql, 's', [$neptun_code]);
                if($user_data) {
                    // Check if user already exists, if so break the current loop iteration
                    if(! canImportPointsForOpportunityByID($opportunity_id, $user_data['id'])) {
                        // array_push($students, $neptun_code);
                        if($students == "") {
                            $students = $neptun_code;
                        } else {
                            $students = $students . ", " . $neptun_code;
                        }
                        continue;
                    }
                    $sql = "INSERT INTO `excellence_points` (opportunity_id, user_id) VALUES (?, ?)";
                    $result = modifyRecord($sql, 'ii', [$opportunity_id, $user_data['id']]);
                }
            }
        } 
        deleteFile($filePath);
        return array(true, $students);
    }

    function deleteFile($filePath){
        unlink($filePath);
        return true;
    }
?>