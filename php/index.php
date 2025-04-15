<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/DocentiController.php';
require __DIR__ . '/controllers/ScuoleController.php';

$app = AppFactory::create();
//Scuole
//curl http://localhost:8080/scuole
$app->get('/scuole', "ScuoleController:show");
//curl http://localhost:8080/scuole/1
$app->get('/scuole/{id_scuola}', "ScuoleController:show");
//curl http://localhost:8080/scuole?nome=meucci&sort=true&sort_per=id&sort_dir=DESC
//$app->get('/scuole', "ScuoleController:search");
//curl -X POST http://localhost:8080/scuole -H "Content-Type: application/json" -d '{"nome": "Scuola Superiore Verga","indirizzo": "Via barbarinese 56"}'
$app->post('/scuole', "ScuoleController:create");
//curl -X PUT http://localhost:8080/scuole/3 -H "Content-Type: application/json" -d '{"nome": "Ruji"}'
$app->put('/scuole/{id_scuola}', "ScuoleController:update");
//curl -X DELETE http://localhost:8080/scuole/3
$app->delete('/scuole/{id_scuola}', "ScuoleController:destroy");


//Docenti
//curl http://localhost:8080/scuole/1/docenti
$app->get('/scuole/{scuola_id}/docenti', "DocentiController:index");
//curl http://localhost:8080/scuole/1/docenti/{id_docente}
$app->get('/scuole/{id_scuola}/docenti/{id_docente}', "DocentiController:show");
//curl -X POST http://localhost:8080/scuole/2/docenti -H "Content-Type: application/json" -d '{"nome": "nome", "cognome": "cognome"}'
$app->post('/scuole/{id_scuola}/docenti', "DocentiController:create");
//curl -X PUT http://localhost:8080/scuole/3/docenti/2 -H "Content-Type: application/json" -d '{"nome": "Ruji", "cognome": "Chen"}'
$app->put('/scuole/{id_scuola}/docenti/{id_docente}', "DocentiController:update");
//curl -X DELETE http://localhost:8080/scuole/3/docenti/2
$app->delete('/scuole/{id_scuola}/docenti/{id_docente}', "DocentiController:destroy");

$app->run();
