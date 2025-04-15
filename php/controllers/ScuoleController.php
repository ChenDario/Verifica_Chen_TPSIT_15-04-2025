<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ScuoleController
{
  public function index(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    $result = $mysqli_connection->query("SELECT * FROM scuole");
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
  $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
  $stmt = $mysqli_connection->prepare("SELECT * FROM scuole WHERE id = ?");
  $stmt->bind_param("i", $args['id_scuola']);
  $stmt->execute();
  $result = $stmt->get_result();
  $results = $result->fetch_all(MYSQLI_ASSOC);

  if(!empty($results)){
    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  $response->getBody()->write(json_encode([
    'status' => 'failed',
    'message' => 'Scuola non trovata'
  ]));

  return $response->withHeader("Content-type", "application/json")->withStatus(404);
}

  public function search(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    //curl http://localhost:8080/scuole?nome=meucci&sort=true&sort_per=id&sort_dir=DESC
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    $sort = $queryParams['sort'] ?? null;
    $sort_per = $queryParams['sort_per'] ?? 'id';  // Default a 'id' se non specificato
    $sort_dir = isset($queryParams['sort_dir']) ? strtoupper($queryParams['sort_dir']) : 'ASC';

    // Validazione parametri di ordinamento
    $sort_dir = ($sort_dir === 'DESC') ? 'DESC' : 'ASC';
    $allowed_columns = ['id', 'nome', 'indirizzo'];
    $sort_per = in_array($sort_per, $allowed_columns) ? $sort_per : 'id';

    // Costruzione della condizione WHERE
    $where = "";

    if (!empty($nome)) {
      $where = "nome LIKE '%{$nome}%'";
    }

    // Aggiungi ordinamento se richiesto
    if ($sort) {
      $where .= " ORDER BY $sort_per $sort_dir";
    }

    $query = "SELECT * FROM scuole WHERE 1=1 AND " . $where;

    $result = $mysqli_connection->query($query);
    $results = $result->fetch_all(MYSQLI_ASSOC);


    if(!empty($results)){
      $response->getBody()->write(json_encode($results));
      return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }

    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Scuola non trovata'
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(404);
  }

  public function create(Request $request, Response $response, $args){
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    $data = json_decode($request->getBody()->getContents(), true);

    if(isset($data['nome']) && isset($data['indirizzo'])){
      $stmt = $mysqli_connection->prepare("INSERT INTO scuole (nome, indirizzo) VALUES (?, ?)");
      $stmt->bind_param("ss", $data['nome'], $data['indirizzo']);
      
      if($stmt->execute()){
        //Risposta di successo
        $response->getBody()->write(json_encode([
          'status' => 'success',
          'message' => 'Scuola creato con successo',
          'id' => $mysqli_connection->insert_id
        ]));
        
        return $response->withHeader("Content-type", "application/json")->withStatus(201); 
      }

      //Risposta di fallimento
      $response->getBody()->write(json_encode([
        'status' => 'failed',
        'message' => 'Internal Server Error (input)',
      ]));
          
      return $response->withHeader("Content-type", "application/json")->withStatus(500); 

    }

    //Risposta di fallimento
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Dati scuola errati/incompleti',
    ]));
        
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  }

  public function update(Request $request, Response $response, $args) {
    //curl -X PUT http://localhost:8080/scuole/3 -H "Content-Type: application/json" -d '{"nome": "Ruji", "indirizzo": "Ciao"}'
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    // Recupera i dati dal body della richiesta (JSON)
    $data = json_decode($request->getBody()->getContents(), true);

    if(isset($data['nome'])&&isset($data['indirizzo'])){
      // Prepara la query SQL
      $stmt = $mysqli_connection->prepare("UPDATE scuole SET nome = ? AND indirizzo = ? WHERE id = ?");
      $stmt->bind_param("ssi", $data['nome'], $data['indirizzo'], $args['id_scuola']);
      $stmt->execute();
      
      // Chiudi lo statement
      $stmt->close();
      
      //Risposta di successo
      $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Scuola Aggiornato con successo',
        'id' => $mysqli_connection->insert_id
      ]));
      
      return $response->withHeader("Content-type", "application/json")->withStatus(201); 
    }

    //Risposta di errore
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Dati incompleti/errati'
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  }

  public function destroy(Request $request, Response $response, $args) {
    $mysqli_connection = new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
    
    // Prepara la query SQL
    $stmt = $mysqli_connection->prepare("DELETE FROM scuole WHERE id = ?");
    $stmt->bind_param("i", $args['id_scuola']);
    if($stmt->execute()){
      //Risposta di successo
      $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Scuola Eliminato con successo',
      ]));
      
      return $response->withHeader("Content-type", "application/json")->withStatus(201); 
    }
    
    //Risposta di errore
    $response->getBody()->write(json_encode([
      'status' => 'failed',
      'message' => 'Error in the delete',
    ]));
    
    return $response->withHeader("Content-type", "application/json")->withStatus(400); 
  }
}
