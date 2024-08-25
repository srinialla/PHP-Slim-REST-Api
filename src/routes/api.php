<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
 
// Import Monolog classes into the global namespace
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$container = $app->getContainer();

$container['cache'] = function () {
	return new \Slim\HttpCache\CacheProvider();
};
$secret = SECRET;

$container["logger"] = function ($c) {
	// create a log channel
	$log = new Logger("api");
	$log->pushHandler(new StreamHandler(__DIR__ . "/../logs/app.log", Logger::INFO));

	return $log;
};

$app->add(new \Slim\HttpCache\Cache('private', 300, true));

/**
 * This method restricts access to addresses. <br/>
 * <b>post: </b>To access is required a valid token.
 */


$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path" => "/api/admin", 
    "secret" => $secret,
    "algorithm" => ["HS256"],
	 
    "error" => function ($response, $arguments) {
        $data = ["status" => "error", "message" => $arguments["message"]];
        return $response->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

/**
 * This method settings CORS requests
 *
 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
 * @param	callable                                 	$next     	Next middleware
 *
 * @return	\Psr\Http\Message\ResponseInterface
 */
$app->add(function (Request $request, Response $response, $next) {
	$response = $next($request, $response);
	// Access-Control-Allow-Origin: <domain>, ... | *
	$response = $response->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	return $response;
});

/**
 * This method creates an urls group. <br/>
 * <b>post: </b>establishes the base url "/public/api/".
 */
$app->group("/api", function () use ($app) {
	/**
	 * This method is used for testing the api.<br/>
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return	string
	 */
	
	$app->get("/status", function (Request $request, Response $response) {
		$data["databseConnection"] = "";
		try {

		$conn = PDOConnection::getConnection();
		$data["databseConnection"] = "🎉Hurray🎉,Database connected. Now let’s see how long it stays that way…";
		}
		catch (PDOException $e) {
			$errorMessage = $e->getMessage();
			if (strpos($errorMessage, 'Unknown database') !== false) {
				$data["databseConnection"] = "😢Failed, Database not found. It’s probably hiding from you.";
 
			} else {
				$data["databseConnection"] = "😢Connection failed: " . $errorMessage;
			}
		}
		
		$data["status"] = "Server up and running! Time to grab a coffee and pretend I’m working.";
		$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
		return $response;

	});

	/**
	 * This method post an user into the database.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return	\Psr\Http\Message\ResponseInterface
	 */
	$app->post("/login", function (Request $request, Response $response) {
		/** @var string $user - Username */
		$user = $request->getParam	("username");
		/** @var string $pass - Password */
		$pass = $request->getParam("password");
		if(!$user ||  !$pass ){
			return $response->withHeader("Content-Type", "application/json")
			->withStatus(500, "Internal Server Error")
			->withJson(['Status'=>'Please enter valid credentials']);
		}
		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Gets the user into the database
			$sql = "SELECT	*
					FROM	USERS
					WHERE	USERNAME = :user
						AND	STATUS = 1";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":user", $user);
			$stmt->execute();
			$query = $stmt->fetchObject();

			// If user exist
			if ($query) {
				// If password is correct
				if (password_verify($pass, $query->PASSWORD)) {
					// Create a new resource
					$data["token"] = JWTAuth::getToken($query->ID_USER, $query->USERNAME);
				} else {
					// Password wrong
					$data["status"] = "Error: The password you have entered is wrong.";
				}
			} else {
				// Username wrong
				$data["status"] = "Error: The user specified does not exist.";
			}

			// Return the result
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(201, "Created")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});

	/**
	 * This method sets an user into the database.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return	\Psr\Http\Message\ResponseInterface
	 */
	$app->post("/register", function (Request $request, Response $response) {
		/** @var string $guid - Unique ID */
		$guid = uniqid();
		$err = '';
		/** @var string $user - Username */
		 $user = $request->getParam("username");
		/** @var string $pass - Password */
		 $pass = password_hash($request->getParam("password"), PASSWORD_DEFAULT);
		/** @var string $email - Email */
		 $email = trim(strtolower($request->getParam("email")));
		/** @var string $created - Date of created */
		$created = date("Y-m-d");
		
		
		if(!$user ||  !$pass || !$email ){
			return $response->withHeader("Content-Type", "application/json")
			->withStatus(500, "Internal Server Error")
			->withJson(['Status'=>'Please check input data']);
		}
		try {
 			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Gets the user into the database
			$sql = "INSERT INTO	USERS (GUID, USERNAME, PASSWORD, CREATED_AT)
					VALUES (:guid, :user, :pass, :created)";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":guid", $guid);
			$stmt->bindParam(":user", $user);
			$stmt->bindParam(":pass", $pass);
			$stmt->bindParam(":created", $created);

			$result = $stmt->execute();
			 
			// If user has been registered
			if ($result) {
				$data["status"] = "Your account has been successfully created. ";
				return $response->withHeader("Content-Type", "application/json")
				->withStatus(201, "OK")
				->withJson($data);
				// $to = $email;
				// $name = $user;
				// $subject = "Confirm your email address";

 				// $html = "Click on the link to verify your email <a href='http://{yourdomain}/public/api/validate/{$user}/{$token}' target='_blank'>Link</a>";
				// $text = "Go to the link to verify your email: http://{yourdomain}/public/api/validate/{$user}/{$token}";

				// Sent mail verification
				//Mailer::send($to, $name, $subject, $html, $text);
			} else {
				$data["status"] = "Error: Your account cannot be created at this time. Please try again later.";
			}
			return $response->withHeader("Content-Type", "application/json")
			->withStatus(500)
			->withJson($data);
			
			 
		} catch (PDOException $e) {
			$err = $e->getMessage();
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$err = $e->getMessage();
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			
			// Destroy the database connection
			$conn = null;
		}
		return $response->withHeader("Content-Type", "application/json")
			->withStatus(500)
			->withJson(["status"=>$err]);
	});

	 
	 

	/**
	 * This method cheks the token.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return 	\Psr\Http\Message\ResponseInterface
	 */
	$app->get("/verify", function (Request $request, Response $response) {
		$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
		if (!$authHeader) {
			// Handle the case where no Authorization header is present
			return $response->withStatus(401)->withJson(['error' => 'No `Authorization` header present']);
		}
		// Gets the token of the header.
		// Authorization: Bearer {token}
		/** @var string $token - Token */
		$token = str_replace("Bearer ", "", $request->getServerParams()["HTTP_AUTHORIZATION"]);
		// Verify the token.
		$result = JWTAuth::verifyToken($token);
		// Return the result
		if ($result) {
			$data["id_user"] = $result->header->id;
			$data["username"] = $result->header->user;
			$data["status"] = true;
		} else {
			$data["status"] = "Error: Authentication token is invalid.";
		}
		$response = $response->withHeader("Content-Type", "application/json")
			->withStatus(200, "OK")
			->withJson($data);
		return $response;
	});

	/**
	 * This method publish short text messages of no more than 120 characters.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return	\Psr\Http\Message\ResponseInterface
	 */
	$app->post("/admin/post", function (Request $request, Response $response) {
		/** @var string $quote - The text of post */
		$quote = $request->getParam("quote");
		/** @var string $id - The user ID */
		$id = $request->getAttribute('token')['header']->id;

		if(!$quote ){
			return $response->withHeader("Content-Type", "application/json")
			->withStatus(500, "Internal Server Error")
			->withJson(['Status'=>'Please enter quotation']);
		}
		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Gets the user into the database
			$sql = "SELECT	*
					FROM	USERS
					WHERE	ID_USER = :id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			$query = $stmt->fetchObject();

			// If user exist
			if ($query) {
				// Truncate the text
				if (strlen($quote) > 120) {
					$quote = substr($quote, 0, 120);
				}

				// Insert post into the database
				$sql = "INSERT INTO	QUOTES (QUOTE, ID_USER)
						VALUES		(:quote, :id)";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(":quote", $quote);
				$stmt->bindParam(":id", $id);
				$result = $stmt->execute();

				$data["status"] = $result;
			} else {
				// Username wrong
				$data["status"] = "Error: The user specified does not exist.";
			}

			// Return the result
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});

	$app->put("/admin/post", function (Request $request, Response $response, $args) {
		/** @var string $quote - The updated text of the post */
		$quote = $request->getParam("quote");
		/** @var string $userId - The user ID */
		$userId = $request->getAttribute('token')['header']->id;
		/** @var int $postId - The ID of the post to update */
		$postId = $request->getParam('id');
		$statusCode;
		if(!$quote || !$postId ){
			return $response->withHeader("Content-Type", "application/json")
			->withStatus(500, "Internal Server Error")
			->withJson(['Status'=>'Please enter quotation and Id']);
		}
		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();
			// Start transaction
			$conn->beginTransaction();
			// Check if the post exists and belongs to the user
			$sql = "SELECT * FROM QUOTES WHERE ID_QUOTE = :postId AND ID_USER = :userId";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":postId", $postId);
			$stmt->bindParam(":userId", $userId);
			$stmt->execute();
			$post = $stmt->fetchObject();
	
			if ($post) {
				// Truncate the text if necessary
				if (strlen($quote) > 500) {
					$quote = substr($quote, 0, 500);
				}
	
				// Update the post in the database
				$sql = "UPDATE QUOTES SET QUOTE = :quote WHERE ID_QUOTE = :postId";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(":quote", $quote);
				$stmt->bindParam(":postId", $postId);
				$result = $stmt->execute();
	
				$data["status"] = $result ? "Success" : "Error: Failed to update the post.";
				$statusCode = $result ? 200 : 500;
			} else {
				$data["status"] = "Error: The post does not exist or you don't have permission to edit it.";
				$statusCode =   500;
			}
			// Commit the transaction
			$conn->commit();
			// Return the result
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus($statusCode)
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			if ($conn->inTransaction()) {
				$conn->rollBack();
			}
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
			return $response->withStatus(500, "Internal Server Error");

		} catch (Exception $e) {
			if ($conn->inTransaction()) {
				$conn->rollBack();
			}
			$this["logger"]->error("General Error: {$e->getMessage()}");
			return $response->withStatus(500, "Internal Server Error");

		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});

	/**
	 * This method list the latest published messages.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return 	\Psr\Http\Message\ResponseInterface
	 */
	$app->get("/admin/posts", function (Request $request, Response $response) {
		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();
			
			// Gets the posts into the database
			$sql = "SELECT		Q.ID_QUOTE AS id,
								Q.QUOTE AS quote,
								Q.POST_DATE AS postdate,
								Q.LIKES AS likes,
								U.USERNAME AS user
					FROM		QUOTES AS Q
					INNER JOIN	USERS AS U
							ON	Q.ID_USER = U.ID_USER
					ORDER BY	likes DESC";
			$stmt = $conn->query($sql);
			$data = $stmt->fetchAll();

			// Return a list
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});

	/**
	 * This method list the users for likes.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return 	\Psr\Http\Message\ResponseInterface
	 */
	$app->get("/admin/likes/{id}", function (Request $request, Response $response) {
		/** @var string $id - The quote ID */
		echo $id = $request->getAttribute("id");

		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Gets the posts into the database
			$sql = "SELECT	 
								U.USERNAME AS user
					FROM		LIKES AS L
					INNER JOIN	USERS AS U
							ON	L.ID_USER = U.ID_USER
					AND			L.ID_QUOTE = :id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			$data = $stmt->fetchAll();

			// Return a list
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});

	/**
	 * This method searches for messages by your text.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return 	\Psr\Http\Message\ResponseInterface
	 */
	$app->get("/admin/search/{quote}", function (Request $request, Response $response) {
  
  		/** @var string $quote - The content text in quote */
		$quote = "%" . $request->getAttribute("quote") . "%";

		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Search into the database
			$sql = "SELECT		Q.ID_QUOTE AS id,
								Q.QUOTE AS quote,
								Q.POST_DATE AS postdate,
								Q.LIKES AS likes,
								U.USERNAME AS user
					FROM		QUOTES AS Q
					INNER JOIN	USERS AS U
							ON	Q.ID_USER = U.ID_USER
					WHERE		QUOTE LIKE :quote
					ORDER BY	likes DESC";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":quote", $quote);
			$stmt->execute();
			$data = $stmt->fetchAll();

			// Return the result
			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});


	$app->post("/admin/like/{quoteId}", function (Request $request, Response $response, array $args) {
		try {
			$quoteId = $args['quoteId'];
			$userId = $request->getAttribute('token')['header']->id;	
			// Get database connection
			$conn = PDOConnection::getConnection();
			
			// Start transaction
			$conn->beginTransaction();
	
			// Check if the like already exists
			$checkSql = "SELECT COUNT(*) FROM LIKES WHERE ID_USER = :userId AND ID_QUOTE = :quoteId";
			$checkStmt = $conn->prepare($checkSql);
			$checkStmt->execute(['userId' => $userId, 'quoteId' => $quoteId]);
			$likeExists = $checkStmt->fetchColumn() > 0;
	
			if (!$likeExists) {
				// Insert into LIKES table
				$insertSql = "INSERT INTO LIKES (ID_USER, ID_QUOTE) VALUES (:userId, :quoteId)";
				$insertStmt = $conn->prepare($insertSql);
				$insertStmt->execute(['userId' => $userId, 'quoteId' => $quoteId]);
	
				// Increment like count in QUOTES table
				$updateSql = "UPDATE QUOTES SET LIKES = LIKES + 1 WHERE ID_QUOTE = :quoteId";
				$updateStmt = $conn->prepare($updateSql);
				$updateStmt->execute(['quoteId' => $quoteId]);
	
				// Commit transaction
				$conn->commit();
	
				$response = $response->withJson(['message' => 'Like added successfully'])
									 ->withStatus(200);
			} else {
				$response = $response->withJson(['message' => 'Already liked'])
									 ->withStatus(400);
			}
	
		} catch (PDOException $e) {
			// Rollback transaction on error
			if ($conn) {
				$conn->rollBack();
			}
			$this->get('logger')->error("Database Error: {$e->getMessage()}");
			$response = $response->withJson(['error' => 'Database error occurred'])
								 ->withStatus(500);
		} catch (Exception $e) {
			$this->get('logger')->error("General Error: {$e->getMessage()}");
			$response = $response->withJson(['error' => 'An error occurred'])
								 ->withStatus(500);
		} finally {
			// Close the database connection
			$conn = null;
		}
	
		return $response;
	});

	/**
	 * This method deletes a specific message by its id.
	 *
	 * @param	\Psr\Http\Message\ServerRequestInterface	$request	PSR7 request
	 * @param	\Psr\Http\Message\ResponseInterface      	$response	PSR7 response
	 *
	 * @return 	\Psr\Http\Message\ResponseInterface
	 */
	$app->delete("/admin/delete", function (Request $request, Response $response) {
		/** @var string $id - The quote id */
		$id = $request->getParam("id");

		try {
			// Gets the database connection
			$conn = PDOConnection::getConnection();

			// Delete the quote
			$sql = "DELETE FROM	QUOTES
					WHERE		ID_QUOTE = :id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(":id", $id);
			$result = $stmt->execute();

			// Return the result
			$data["status"] = $result;

			$response = $response->withHeader("Content-Type", "application/json")
				->withStatus(200, "OK")
				->withJson($data);
			return $response;
		} catch (PDOException $e) {
			$this["logger"]->error("DataBase Error: {$e->getMessage()}");
		} catch (Exception $e) {
			$this["logger"]->error("General Error: {$e->getMessage()}");
		} finally {
			// Destroy the database connection
			$conn = null;
		}
	});
});
