<?php

use DI\Container;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;

/**
 * @var $di Container
 */
$di = require '../init.php';

$app = new App($di);

function getPostField(Request $request, string $key)
{
    return trim($request->getParsedBodyParam($key));
}

$app->group('', function(){
    /** @var Slim\App $this */
    $this->get('/', function(Response $response, Twig $twig, \PDO $pdo){
        $counter = function($table) use($pdo){
            return intval($pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn(0));
        };
        return $twig->render($response, 'index.twig', [
            'collections' => $counter('collections'),
            'items' => $counter('items'),
            'links' => $counter('links'),
        ]);
    });
    $this->get('/search/{collection:[0-9]+}', function($collection, Request $request, Response $response, \PDO $pdo){
        $key = trim($request->getParam('key'));
        $name = trim($request->getParam('name'));
        if(!empty($key)){
            $stmt = $pdo->query("SELECT key, name FROM items WHERE collection = ? AND key = ? ORDER BY name ASC");
            $stmt->execute([$collection, $key]);
        }elseif(!empty($name)){
            $stmt = $pdo->query("SELECT key, name FROM items WHERE collection = ? AND name LIKE ? ORDER BY name ASC LIMIT 100");
            $stmt->execute([$collection, "%$name%"]);
        }else{
            return $response->withJson([]);
        }
        $result = [];
        while($item = $stmt->fetch(PDO::FETCH_ASSOC)){
            $result[] = $item;
        }
        return $response->withJson($result);
    });
    $this->get('/links/{collection:[0-9]+}/{key}', function($collection, $key, Response $response, \PDO $pdo){
        $stmt = $pdo->prepare('SELECT url FROM links WHERE collection = ? AND key = ? ORDER BY url ASC');
        $stmt->execute([$collection, $key]);
        $urls = [];
        while($url = $stmt->fetchColumn(0)) $urls[] = $url;
        return $response->withJson($urls);
    });
    $this->post('/collection', function(Request $request, Response $response, \PDO $pdo){
        $name = getPostField($request, 'name');
        if(empty($name)) return $response->withStatus(400)->withJson(['error' => 'Empty Name']);
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO collections (name) VALUES (?)');
        $stmt->execute([$name]);
        return $response->withJson(['id' => intval($pdo->lastInsertId())]);
    });
    $this->post('/item', function(Request $request, Response $response, \PDO $pdo){
        $collection = intval(getPostField($request, 'collection'));
        $key = getPostField($request, 'key');
        $name = getPostField($request, 'name');
        if(empty($collection) || empty($key) || empty($name)) return $response->withStatus(400)->withJson(['error' => 'Empty Name']);
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO items (collection, key, name) VALUES (?, ?, ?)');
        $stmt->execute([$collection, $key, $name]);
        return $response->withJson(['id' => intval($stmt->errorCode())]);
    });
    $this->post('/link', function(Request $request, Response $response, \PDO $pdo){
        $collection = intval(getPostField($request, 'collection'));
        $key = getPostField($request, 'key');
        $url = getPostField($request, 'url');
        if(empty($collection) || empty($key) || empty($url)) return $response->withStatus(400)->withJson(['error' => 'Empty Name']);
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO links (collection, key, url) VALUES (?, ?, ?)');
        $stmt->execute([$collection, $key, $url]);
        return $response->withJson(['id' => intval($stmt->errorCode())]);
    });
})->add(function(Request $request, Response $response, callable $next){
    /** @var Response $response */
    $response = $next($request, $response);
    return $response->withAddedHeader('Access-Control-Allow-Origin', '*');
});

try{
    $app->run();
}catch (\Exception $e){
    var_dump($e);
}
if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();
