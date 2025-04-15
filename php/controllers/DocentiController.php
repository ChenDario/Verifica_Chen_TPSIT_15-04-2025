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

    if(!empty($results)){
      $response->getBody()->write(json_encode($results));
      return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }

    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Docente non trovato'
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(404);
  }


  public function search(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    //curl http://localhost:8080/scuole/1/docenti?nome=ruji&cognome=chen&sort=true&sort_per=id&sort_dir=DESC
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    $cognome = $queryParams['cognome'] ?? null;
    $sort = $queryParams['sort'] ?? null;
    $sort_per = $queryParams['sort_per'] ?? 'id';  // Default a 'id' se non specificato
    $sort_dir = isset($queryParams['sort_dir']) ? strtoupper($queryParams['sort_dir']) : 'ASC';

    // Validazione parametri di ordinamento
    $sort_dir = ($sort_dir === 'DESC') ? 'DESC' : 'ASC';
    $allowed_columns = ['id', 'nome', 'indirizzo'];
    $sort_per = in_array($sort_per, $allowed_columns) ? $sort_per : 'id';

    // Costruzione della condizione WHERE
    $conditions = [];
    $params = [];

    if (!empty($nome)) {
        $conditions[] = "nome LIKE '%" . $this->real_escape_string($nome) . "%'";
    }

    if (!empty($cognome)) {
        $conditions[] = "cognome LIKE '%" . $this->real_escape_string($cognome) . "%'";
    }

    $where = !empty($conditions) ? implode(' AND ', $conditions) : '1';

    $where .= " scuola_id = " . $args['id_scuola'];

    // Aggiungi ordinamento se richiesto
    if ($sort) {
        $where .= " ORDER BY $sort_per $sort_dir";
    }

    $query = "SELECT * FROM docenti WHERE 1=1 AND " . $where;

    $result = $mysqli_connection->query($query);
    $results = $result->fetch_all(MYSQLI_ASSOC);

    if(!empty($results)){
      $response->getBody()->write(json_encode($results));
      return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }

    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Docente non trovato'
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(404);
  }




  public function create(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    $data = json_decode($request->getBody()->getContents(), true);

    if(isset($data['nome'])&&isset($data['cognome'])){
      $stmt = $mysqli_connection->prepare("INSERT INTO docenti (nome, cognome, scuola_id) VALUES (?, ?, ?)");
      $stmt->bind_param("ssi", $data['nome'], $data['cognome'], $args['id_scuola']);
      if($stmt->execute()){
        //Risposta di successo
        $response->getBody()->write(json_encode([
          'status' => 'success',
          'message' => 'Docente creato con successo',
          'id' => $mysqli_connection->insert_id
        ]));
        
        return $response->withHeader("Content-type", "application/json")->withStatus(201); 
      }
  
      //Risposta di Fallimento
      $response->getBody()->write(json_encode([
          'status' => 'failed',
          'message' => 'Internal Error Server',
          'id' => $mysqli_connection->insert_id
      ]));
      
      return $response->withHeader("Content-type", "application/json")->withStatus(500); 
    }
    //Risposta di fallimento
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Dati errati/incompleti',
      'id' => $mysqli_connection->insert_id
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  }

  public function update(Request $request, Response $response, $args) {
    //curl -X PUT http://localhost:8080/scuole/2/docenti/4 -H "Content-Type: application/json" -d '{"nome": "Ruji", "cognome": "Chen"}'
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    // Recupera i dati dal body della richiesta (JSON)
    $data = json_decode($request->getBody()->getContents(), true);

    // Prepara la query SQL
    $stmt = $mysqli_connection->prepare("UPDATE docenti SET nome = ? AND cognome = ? WHERE scuola_id = ? AND id = ?");
    $stmt->bind_param("ssi", $data['nome'], $data['cognome'], $args['id_scuola'], $args['id_docente']);
    if($stmt->execute()){
      //Risposta di successo
      $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Docente Aggiornato con successo'
      ]));
    
    r eturn $response->withHeader("Content-type", "application/json")->withStatus(201); 
    }
    
    // Chiudi lo statement
    $stmt->close();
    
    //Risposta di successo
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Docente Fallito ad aggiornare'
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  }

  public function destroy(Request $request, Response $response, $args) {
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    // Prepara la query SQL
    $stmt = $mysqli_connection->prepare("DELETE FROM docenti WHERE scuola_id = ? AND id = ?");
    $stmt->bind_param("ii", $args['id_scuola'], $args['id_docente']);
    if($stmt->execute()){
      //Risposta di successo
      $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Docente Eliminato con successo',
      ]));
      
      return $response->withHeader("Content-type", "application/json")->withStatus(201); 
    }

    //Risposta di errore
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Errore durante il delete',
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  
  }
}
