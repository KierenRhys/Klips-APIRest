<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();



$app = new \Slim\Slim(array(
	  'mode' => 'development',
    'debug' => true
));

$app->contentType('text/html; charset=utf-8');

$app->setName('KlipsApi');
$app_name = $app->getName();

$klipsDB = new mysqli("127.0.0.1", "root", "", "Klips");
if ($klipsDB->connect_errno) {
	  echo 'Echec lors de la connexion à MySQL: (' . $klipsDB->connect_errno .')' .$klipsDB->connect_error;
}


//SELECT

// Select user info thanks to his ID
$app->get('/user/:id', function ($id) use ($app, $klipsDB){

	  $queryUser = "SELECT * FROM user WHERE id = ".$id;
	  $res = $klipsDB->query($queryUser);

    while($row = $res->fetch_array()) {

      $user[] = array(
        'id' => $row['id'],
        'username' => $row['username'],
        'password' => $row['password'],
        'email' => $row['email'],
        'inscrption' => $row['inscription']
      );
    }

	  $response = $app->response();
	  $response->header('Access-Control-Allow-Origin', '*');
	  $response->write(json_encode($user));
});

// Select all the klips
$app->get('/klips', function () use ($app, $klipsDB){

	  $queryKlip = "SELECT *
					        FROM klip";

	  $res = $klipsDB->query($queryKlip);
	  $response = array();

    while($row = $res->fetch_array()) {
        $klip[] = array(
            'id' => $row['id'],
			      'title' => $row['title'],
            'description' => $row['description'],
            'date' => $row['dateUpload'],
            'link' => $row['link'],
            'thumbnail' => $row['thumbnail'],
            'nbUpvote' => $row['nbUpvote'],
            'nbDownvote' => $row['nbDownvote'],
            'nbSignalment' => $row['nbSignalment'],
            'idUser' => $row['idUser'],
            'idGame' => $row['idGame']
        );
    }

    $response[] = array(
        'klips' => $klip
    );
    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($response));
});


// Select klip info thanks to his ID
$app->get('/klip/:id', function ($id) use ($app, $klipsDB){

	  $queryKlip = "SELECT *
					        FROM klip
					        WHERE id = ".$id;

	  $res = $klipsDB->query($queryKlip);

	  while($row = $res->fetch_array()) {

		    $klip[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'date' => $row['dateUpload'],
            'link' => $row['link'],
            'thumbnail' => $row['thumbnail'],
            'nbUpvote' => $row['nbUpvote'],
            'nbDownvote' => $row['nbDownvote'],
            'nbSignalment' => $row['nbSignalment'],
            'idUser' => $row['idUser'],
            'idGame' => $row['idGame']
		    );
	  }

	  $resp = $app->response();
	  $resp->header('Access-Control-Allow-Origin', '*');
	  $resp->write(json_encode($resp));
});


// Select all the klips for a game
$app->get('/gameKlips/:init', function ($init) use ($app, $klipsDB){

    $queryGame = "SELECT id
                  FROM game
                  WHERE initials = '$init'";

    $res = $klipsDB->query($queryGame);

    if($row = $res->fetch_array()) {
        $id = $row['id'];
    }

    $queryKlip = "SELECT *
                  FROM klip
                  WHERE idGame = $id";

    $res = $klipsDB->query($queryKlip);

    while($row = $res->fetch_array()) {

        $klip[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'date' => $row['dateUpload'],
            'link' => $row['link'],
            'thumbnail' => $row['thumbnail'],
            'nbUpvote' => $row['nbUpvote'],
            'nbDownvote' => $row['nbDownvote'],
            'nbSignalment' => $row['nbSignalment'],
            'idUser' => $row['idUser'],
            'idGame' => $row['idGame']
        );

    }

    $response[] = array(
        'klips' => $klip
    );

	  $resp = $app->response();
	  $resp->header('Access-Control-Allow-Origin', '*');
	  $resp->write(json_encode($klip));

});


// Select all the klips made by an user
$app->get('/userKlips/:username', function ($username) use ($app, $klipsDB){

    $id = null;

    $queryUser = "SELECT id
                  FROM `user`
                  WHERE username = '$username'";

    $res = $klipsDB->query($queryUser);

    if($row = $res->fetch_array()) {
        $id = $row['id'];
    } else {
        $response[] = array(
            'msg' => 'Error'
        );
    }

    $queryKlip = "SELECT *
                  FROM klip
                  WHERE idUser = $id";
    $res = $klipsDB->query($queryKlip);

    while($row = $res->fetch_array()) {

        $klip[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
        'date' => $row['dateUpload'],
        'link' => $row['link'],
        'thumbnail' => $row['thumbnail'],
        'nbUpvote' => $row['nbUpvote'],
        'nbDownvote' => $row['nbDownvote'],
        'nbSignalment' => $row['nbSignalment'],
        'idUser' => $row['idUser'],
        'idGame' => $row['idGame']
      );

    }

    $response[] = array(
      'klips' => $klip
    );

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($klip));

});


// INSERT INTO

// Add a new klip
$app->post('/klip', function() use ($app, $klipsDB) {

    $title = $app->request()->params('title');
    $description = $app->request()->params('description');
    $link = $app->request()->params('link');
    $thumbnail = $app->request()->params('thumbnail');
    $username = $app->request()->params('username');
    $initials = $app->request()->params('initials');
    $idU = null;
    $idG = null;

    date_default_timezone_set('UTC');
    $date = date("Y-m-d H:i:s");

    $queryUser = "SELECT id
                  FROM user
                  WHERE username = '$username'";

    $res = $klipsDB->query($queryUser);

    if($row = $res->fetch_array()) {
        $idU = $row['id'];
    }

    $queryGame = "SELECT id
                  FROM game
                  WHERE initials = '$initials'";

    $res = $klipsDB->query($queryGame);

    if($row = $res->fetch_array()) {
      $idG = $row['id'];
    }

    $queryAdd = "INSERT INTO klip
                  VALUES ('', '$title', '$description', '$date', '$link', '$thumbnail', 0, 0, 0, $idU, $idG)";

    if($klipsDB->query($queryAdd) === true) {
        $arr[] = array (
          'msg' => 'Klip added'
        );
    } else {
        $arr[] = array (
          'msg' => 'Error'
        );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});

// Add an user
$app->post('/user', function() use ($app, $klipsDB) {

    $username = $app->request()->params('username');
    $password = $app->request()->params('password');
    $email = $app->request()->params('email');

    date_default_timezone_set('UTC');
    $date = date("Y-m-d H:i:s");

    $queryAdd = "INSERT INTO `user`
                 VALUES ('', '$username', '$password', '$email', '$date')";

    if($klipsDB->query($queryAdd) === true) {
        $arr[] = array (
          'msg' => 'User added'
        );
    } else {
        $arr[] = array (
          'msg' => 'Error'
        );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});


// UPDATE

// Update a klip thanks to his ID
$app->put('/klip/:id', function($id) use($app, $klipsDB) {

    $title = $app->request()->params('title');
    $description = $app->request()->params('description');
    $link = $app->request()->params('link');
    $thumbnail = $app->request()->params('thumbnail');

    $queryKlip = "UPDATE klip
                  SET title = '$title', description = '$description', link = '$link', thumbnail = '$thumbnail'
                  WHERE id = $id";

    if($klipsDB->query($queryKlip) === true) {
        $arr[] = array (
          'msg' => 'Klip updated'
        );
    } else {
        $arr[] = array (
          'msg' => 'Error'
        );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});

// Update an user thanks to his ID
$app->put('/user/:id', function($id) use($app, $klipsDB) {

    $username = $app->request()->params('username');
    $password = $app->request()->params('password');
    $email = $app->request()->params('email');

    $queryKlip = "UPDATE user
                    SET username = '$username', password = '$password', email = '$email'
                    WHERE id = $id";

    if($klipsDB->query($queryKlip) === true) {
      $arr[] = array (
        'msg' => 'User updated'
      );
    } else {
      $arr[] = array (
        'msg' => 'Error'
      );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});


// DELETE

// Delete a klip thanks to his ID
$app->delete('/klip/:id', function($id) use($app, $klipsDB) {

    $queryKlip = "DELETE
                  FROM klip
                  WHERE id = $id";

    if($klipsDB->query($queryKlip) === true) {
        $arr[] = array (
          'msg' => 'Klip deleted'
        );
    } else {
        $arr[] = array (
          'msg' => 'Error'
        );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});

// Delete an user and his klips thanks to his ID
$app->delete('/user/:id', function($id) use($app, $klipsDB) {

    $queryUser = "DELETE
                  FROM `user`
                  WHERE id = $id";

    if($klipsDB->query($queryUser) === true) {
        $queryGame = "DELETE
                      FROM klip
                      WHERE idUser = $id";

        if($klipsDB->query($queryGame) === true) {
            $arr[] = array (
                'msg' => 'Account deleted'
            );
        } else {
            $arr[] = array (
              'msg' => 'Error during klips delete'
            );
        }
    } else {
        $arr[] = array (
          'msg' => 'Error during account delete'
        );
    }

    $resp = $app->response();
    $resp->header('Access-Control-Allow-Origin', '*');
    $resp->write(json_encode($arr));
});

$app->run();
