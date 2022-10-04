<?php include_once('config.php'); ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php xecho(APP_NAME) ?> - Home</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <!-- Custome styles -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include_once(INCLUDE_PATH . "/layouts/navbar.php") ?>
  <div class="container" style="margin-bottom: 50px;">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <h1 class="text-center">Home page</h1>

        <p><font color="ff0000">This software is in development and beta version. Please do not abuse it!</font></p>
        <p>
        Please choose a diploma topic on this web site. The rules are the following:
        <ul>
        <li>After you have registered in this program then the coordinator has to change your status from Guest to Student. This will only happen for students who have registered in the Neptun system for a Project or Diploma work course. In all other cases, please communicate with the program coordinator.</li>
        <li>To start working on a Project or Diploma work and later to get a signature for the course you must have an accepted topic and supervisor in this system!</li>
        <li>You can register for as many topics as you want.</li>
        <li>During the registration either you have to provide some reasons why you want to choose that topic, or you have to answer some questions specified by the supervisor in the topic requirements.</li>
        <li>You can change your mind and unregister from any selected topic.</li>
        <li>Once a supervisor accepts (approves) your registration for a topic then:
          <ul>
            <li>you cannot register for any further topic,</li> 
            <li>you cannot unregister from any topic, as all other registrations for all other topics will be deleted,</li> 
            <li>no other supervisor can accept your registration for a topic,</li>
            <li>thus typically the approval decision is final. (See below to change it.)</li>
            <li>The supervisor cannot change the topic in the system after acceptance. Of course  throughout the semester, as your work progresses some changes in the title and and in the specification may be required, but they do not have to appear here, in this system, only in the final, submitted work. However for all these changes you will need the approval of your supervisor.</li>
          </ul>
        </li>
        <li>The approved topic will describe your work for a diploma thesis!</li>
        <li>If you want to change your final selection, then you have to ask the supervisor of your accepted topic to withdraw the approval. After this you will be able to register for a new topic and the process starts again.</li>
        <li>All approved topics disappear from the list presented to students. So if you do not see a topic any more, then maybe it has been assigned to another student or it has been unpublished.</li>
        <li>When a topic is unpublished all registrations and the approved registration will be deleted. Again, students will not see this topic and cannot register for this topic.</li>
        </ul>
        </p>
        <?php if (hasPermissionTo('create-topic')): ?>
          <p>For lecturers:</p>
          <ul>
            <li><button class="btn btn-sm btn-success glyphicon glyphicon-info-sign"></button> - The topic can be viewed. Students can register for the topic. The creator can approve a registration or withdraw an approval.</li>
            <li><button class="btn btn-sm btn-primary glyphicon glyphicon-info-sign"></button> - The topic has an approved student. Student cannot register any more for any other topic. Lecturer cannot edit, delete, or unpublish the topic. Lecturer can only withdraw approval.</li>
            <li><button class="btn btn-sm btn-success glyphicon glyphicon-pencil"></button> - The topic can be edited and modified by the creator or the admin. Lecturer can unpublish a topic, but consider the consequences as described above.</li>
            <li><button class="btn btn-sm btn-danger glyphicon glyphicon-trash"></button> - The topic can be deleted.</li>
            <li><button class="btn btn-sm btn-secondary glyphicon glyphicon-pencil"></button> - The topic is archived, therefore it cannot be editer any more.</li>
            <li><button class="btn btn-sm btn-secondary glyphicon glyphicon-trash"></button> - The topic is archived, therefore it cannot be deleted any more.</li>
          </ul>
          Topics with approved student are archived at the end of every semester. 
          After archiving the topic can be viewed, but not edited or deleted, by the lecturers. Students cannot view archived topics any more.
        <?php endif ?>

      </div>
    </div>
  </div>

  <?php include_once(INCLUDE_PATH . "/layouts/footer.php") ?>
