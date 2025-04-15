<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocentiController
{
  public function index(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    $result = $mysqli_connection->query("SELECT * FROM docenti where scuola_id = " .$args['scuola_id'] );
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    $stmt = $mysqli_connection->prepare("SELECT * FROM docenti WHERE scuola_id = ? AND id = ?");
    $stmt->bind_param("ii", $args['id_scuola'], $args['id_docente']);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function create(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $mysqli_connection->prepare("INSERT INTO docenti (nome, cognome, scuola_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $data['nome'], $data['cognome'], $args['id_scuola']);
    $stmt->execute();

    // Chiudi lo statement
    $stmt->close();

    //Risposta di successo
    $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Docente creato con successo',
        'id' => $mysqli_connection->insert_id
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(201); 
  }

  public function update(Request $request, Response $response, $args) {
    //curl -X PUT http://localhost:8080/scuole/2/docenti/4 -H "Content-Type: application/json" -d '{"nome": "Ruji", "cognome": "Chen"}'
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    // Recupera i dati dal body della richiesta (JSON)
    $data = json_decode($request->getBody()->getContents(), true);

    // Prepara la query SQL
    $stmt = $mysqli_connection->prepare("UPDATE docenti SET nome = ? AND cognome = ? WHERE scuola_id = ? AND id = ?");
    $stmt->bind_param("ssi", $data['nome'], $data['cognome'], $args['id_scuola'], $args['id_docente']);
    $stmt->execute();
    
    // Chiudi lo statement
    $stmt->close();
    
    //Risposta di successo
    $response->getBody()->write(json_encode([
      'status' => 'success',
      'message' => 'Docente Aggiornato con successo'
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(201); 
  }

  public function destroy(Request $request, Response $response, $args) {
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    // Prepara la query SQL
    $stmt = $mysqli_connection->prepare("DELETE FROM docenti WHERE scuola_id = ? AND id = ?");
    $stmt->bind_param("ii", $args['id_scuola'], $args['id_docente']);
    $stmt->execute();
    // Chiudi lo statement
    $stmt->close();
    
    //Risposta di successo
    $response->getBody()->write(json_encode([
      'status' => 'success',
      'message' => 'Docente Eliminato con successo',
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(201); 
  }
}
