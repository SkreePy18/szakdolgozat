<?php include_once(INCLUDE_PATH . '/logic/validation.php') ?>
<?php
  $role_id = 0;
  $name = "";
  $description = "";
  $isEditting = false;
  $isDeleting = false;
  $roles = array();
  $errors = array();

  // ACTION: Save Role
  if (isset($_POST['save_role'])) {
    saveRole();
  }
  // ACTION: update role
  if (isset($_POST['update_role'])) {
    updateRole();
  }
  // ACTION: fetch role for editting
  if (isset($_GET["edit_role"])) {
    editRole();
  }
  // ACTION: Delete role with confirmation
  if (isset($_GET['delete_role'])) {
    deleteRole();
  }
  // ACTION: Force delete
  if (isset($_POST['force_delete_role'])) {
    forcedeleteRole();
  }

  if (isset($_GET['assign_permissions'])) {
    // The ID of the role whose permissions we are changing
    $role_id = filter_input(INPUT_GET, 'assign_permissions', FILTER_SANITIZE_NUMBER_INT);
  }

  // ACTION assign permissions to role
  if (isset($_POST['assign_permissions'])) {
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
    if(isset($_POST['permission'])) {
      $permissions = $_POST['permission'];
    } else {
      $permissions = [];
    }
    // sanitize the permissions
    $sanitized_array = array();
    foreach( $permissions as $perm ) {
      $sanitized_array[] = intval( filter_var($perm, FILTER_SANITIZE_NUMBER_INT) );
    }
    assignRolePermissions($sanitized_array, $role_id);
  }

  function getAllRoles(){
    global $conn;
    $sql = "SELECT id, name FROM roles";
    $roles = getMultipleRecords($sql);
    return $roles;
  }

  // Save role to database
  function saveRole(){
    global $conn, $errors, $name, $description;

    $role_data = filter_input_array(INPUT_POST, [
                  "name" => FILTER_SANITIZE_STRING,
                  "description" => FILTER_SANITIZE_STRING
                 ]);
    // receive form values
    $name = $role_data['name'];
    $description = $role_data['description'];

    if (! canCreateRole( )) {
      $_SESSION['error_msg'] = "No permissions to create role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    $errors = validateRole($role_data, ['save_role']);
    if (count($errors) === 0) {
      $sql = "INSERT INTO roles SET name=?, description=?";
      $result = modifyRecord($sql, 'ss', [$name, $description]);

      if ($result) {
        $_SESSION['success_msg'] = "Role created successfully";
        header("location: " . BASE_URL . "admin/roles/roleList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not save role";
      }
    }
  }

  function updateRole(){
    global $conn, $errors, $role_id, $name, $description, $isEditting; // pull in global form variables into function

    $role_data = filter_input_array(INPUT_POST, [
                  "role_id" => FILTER_SANITIZE_NUMBER_INT,
                  "name" => FILTER_SANITIZE_STRING,
                  "description" => FILTER_SANITIZE_STRING
                 ]);
    // receive form values
    $role_id = $role_data['role_id'];
    $name = $role_data['name'];
    $description = $role_data['description'];

    if (! canUpdateRoleByID( $role_id )) {
      $_SESSION['error_msg'] = "No permissions to update role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    $errors = validateRole($role_data, ['update_role']); // validate form
    if (count($errors) === 0) {
      $sql = "UPDATE roles SET name=?, description=? WHERE id=?";
      $result = modifyRecord($sql, 'ssi', [$name, $description, $role_id]);

      if ($result) {
        $_SESSION['success_msg'] = "Role successfully updated";
        header("location: " . BASE_URL . "admin/roles/roleList.php");
        exit(0);
      } else {
        $_SESSION['error_msg'] = "Could not update role data";
      }
    } else {
      $_SESSION['error_msg'] = "Could not update role";
    }
    $isEditting = false;
  }

  function editRole(){
    global $conn, $role_id, $name, $description, $isEditting;

    $role_id = filter_input(INPUT_GET, 'edit_role', FILTER_SANITIZE_NUMBER_INT);

    if (! canUpdateRoleByID( $role_id )) {
      $_SESSION['error_msg'] = "No permissions to edit role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    $sql = "SELECT * FROM roles WHERE id=? LIMIT 1";
    $role = getSingleRecord($sql, 'i', [$role_id]);

    $name = $role['name'];
    $description = $role['description'];
    $isEditting = true;
  }

  function deleteRole() {
    global $conn, $role_id, $name, $isDeleting;

    $role_id = filter_input(INPUT_GET, 'delete_role', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteRoleByID( $role_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    $sql = "SELECT * FROM roles WHERE id=?";
    $role_data = getSingleRecord($sql, 'i', [$role_id]);

    $name = $role_data['name'];
    $isDeleting = true;
  }


  function forcedeleteRole() {
    global $conn, $role_id;

    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);

    if (! canDeleteRoleByID( $role_id )) {
      // $_SESSION['error_msg'] = "No permissions to delete role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    $sql = "DELETE FROM roles WHERE id=?";
    $result = modifyRecord($sql, 'i', [$role_id]);
    if ($result) {
      $_SESSION['success_msg'] = "Role have been deleted!!";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Could not delete role";
    }
}

  function getAllPermissions(){
    global $conn;
    $sql = "SELECT * FROM permissions";
    $permissions = getMultipleRecords($sql);
    return $permissions;
  }

  function getRoleAllPermissions($role_id){
    global $conn;
    $sql = "SELECT permissions.* FROM permissions
            JOIN permission_role
              ON permissions.id = permission_role.permission_id
            WHERE permission_role.role_id=?";
    $permissions = getMultipleRecords($sql, 'i', [$role_id]);
    return $permissions;
  }

  function assignRolePermissions($permission_ids, $role_id) {
    global $conn;

    if (! canAssignRolePermissionsByID( $role_id )) {
      $_SESSION['error_msg'] = "No permissions to assign permissions to role";
      header("location: " . BASE_URL . "admin/roles/roleList.php");
      exit(0);
    }

    // start the transaction
    mysqli_autocommit($conn, false);
    mysqli_begin_transaction($conn);

    try {
      // delete all permissions
      $sql1 = "DELETE FROM permission_role WHERE role_id=?";
      $result1 = modifyRecord($sql1, 'i', [$role_id]);

      // insert all permissions
      foreach ($permission_ids as $id) {
        $sql2 = "INSERT INTO permission_role SET role_id=?, permission_id=?";
        $result2 = modifyRecord($sql2, 'ii', [$role_id, $id]);
      }

      mysqli_commit($conn);
      mysqli_autocommit ($conn, true);

      $_SESSION['warning_msg'] = "Permissions saved, but permissions will be applied to yourself _ONLY_ at next login!!!";
      header("location: roleList.php");
      exit(0);

    } catch(EXCEPTION $e){

      mysqli_rollback($conn);
      mysqli_autocommit ($conn, true);
      $_SESSION['error_msg'] = "Could not save permissions";
      throw $e;
    }

  }

