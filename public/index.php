<?php
//preciser l'acceea l'api par des adresse
header('Access-Control-Allow-Origin: http://192.168.56.110');
//preciser les methode sinon il faut se connecter sur le site et avoir les droits pour les utiliser
//header('Access-Control-Allow-Methods: GET');

	// https://github.com/sferey/slim3-wine-api
	require_once '../vendor/autoload.php';

	$settings = array('settings' => array(
		'dsn' => 'sqlite:../data/cellar.db',
//		'dsn' => 'mysql:dbname=cellar;host=127.0.0.1',
//		'username' => '',
//		'password' => '',
		'displayErrorDetails' => true
	));

	$app = new Slim\App($settings);

	$container = $app->getContainer();
	$container['pdo'] = function ($config) {
		return new PDO($config['settings']['dsn']);
	};

	$app->get('/ping', function($request, $response) {
		$body = json_encode(['ack' => time()]);
		$response->write($body);
		$response = $response->withHeader('Content-Type', 'application/json');

		return $response;
	});

	//www.project.dev/v1/wines
	$app->group('/v1', function() {

		$this->get('/install', function($request, $response) {
			$dbh = $this->get('pdo');

			$schemaSql = file_get_contents(dirname(dirname(__FILE__)) . '/data/scripts/schema.sqlite.sql');
			$dbh->exec($schemaSql);
		});


		//CRUD wine
		$this->post('/wines', function($request, $response, $args){

			$wine = $request->getParsedBody();

			try {
				$dbh = $this->get('pdo');

				$sql = "INSERT INTO wines (name, grapes, country, region, year, description)
				VALUES (:name, :grapes, :country, :region, :year, :description);";

				$stmt = $dbh->prepare($sql);
				$stmt->execute($wine);

				$wine['id'] = $dbh->lastInsertId();

				$response = $response->withJson($wine, 201);
			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() . '}}', 500);
			} finally {
				return $response;
			}


		});

		$this->get('/wines', function($request, $response){
			try {
				$dbh = $this->get('pdo');

				$sql = "SELECT * FROM wines ORDER BY `name`";

				$stmt = $dbh->query($sql);
				$wines = $stmt->fetchAll(PDO::FETCH_OBJ);

				if(!$wines) {
					$response = $response->withStatus(404);
				} else {
					$response = $response->withJson($wines, 200);
				}

			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() . '}}', 500);
			} finally {
				return $response;
			}
		});

		$this->get('/wines/{id:[0-9]+}', function($request, $response, $args){
			$id = $args['id'];

			try {
				$dbh = $this->get('pdo');

				$sql = "SELECT * FROM `wines` WHERE `wines`.`id`=:id";

				$stmt = $dbh->prepare($sql);
				$stmt->bindParam("id", $id);
				$stmt->execute();

				$wine = $stmt->fetch(PDO::FETCH_OBJ);

				if(!$wine) {
					$response = $response->withStatus(404);
				} else {
					$response = $response->withJson($wine, 200);
				}

			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() . '}}', 500);
			} finally {
				return $response;
			}
		});

		$this->put('/wines/{id:[0-9]+}', function($request, $response, $args){
			$wine = $request->getParsedBody();
			$wine['id'] = $args["id"];

			try {
				$dbh = $this->get('pdo');

				$sql = "UPDATE wines
				SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description
				WHERE id=:id
				LIMIT 1;";

				$stmt = $dbh->prepare($sql);
				$stmt->execute($wine);
				$rowAffected = $stmt->rowCount();

				if($rowAffected == 1){
					$response = $response->withStatus($wine,201);
				}else{
					$response = $response->withStatus(404);
				}

			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() . '}}', 500);
			} finally {
				return $response;
			}
		});

		$this->delete('/wines/{id:[0-9]+}', function($request, $response, $args){
			$id = $args['id'];

			try {
				$dbh = $this->get('pdo');

				$sql = "DELETE FROM `wines` WHERE `wines`.`id`=:id LIMIT 1;";

				$stmt = $dbh->prepare($sql);
				$stmt->bindParam("id", $id);
				$stmt->execute();
				$rowAffected = $stmt->rowCount();

				if($rowAffected == 1){
					$response = $response->withStatus(204);
				}else{
					$response = $response->withStatus(404);
				}

			} catch(PDOException $e) {
				$response = $response->withJson('{"error":{"text":'. $e->getMessage() . '}}', 500);
			} finally {
				return $response;
			}
		});
	});

	$app->run();
