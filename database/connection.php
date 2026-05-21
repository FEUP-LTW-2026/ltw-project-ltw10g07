<?php
declare(strict_types=1);

function getDB(): PDO {
    $path = __DIR__ . '/ltw.db';

    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');

    return $db;
}
