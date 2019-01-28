<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/12
 * Time: 9:52
 */

use Slim\Views\Twig;

return [
    \PDO::class => function(){
        $pdo = new \PDO('sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'db.sqlite3', '', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('CREATE TABLE IF NOT EXISTS `collections` (`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL CONSTRAINT name UNIQUE)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS `items` (`collection` INTEGER NOT NULL, `key` TEXT NOT NULL, `name` TEXT NOT NULL, PRIMARY KEY (`collection`, `key`), FOREIGN KEY(collection) REFERENCES collections(id) )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS `links` (`collection` INTEGER NOT NULL, `key` TEXT NOT NULL, `url` TEXT NOT NULL, PRIMARY KEY (`collection`, `key`, `url`), FOREIGN KEY(collection) REFERENCES collections(id))');
        // 完成数据库配置，返回PDO实例
        return $pdo;
    },
    Twig::class => function(){
        return new Twig(__DIR__ . DIRECTORY_SEPARATOR . 'views');
    },
    'settings.displayErrorDetails' => true,
];