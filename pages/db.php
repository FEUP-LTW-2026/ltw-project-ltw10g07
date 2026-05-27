<?php
function getDB() {
    $path = __DIR__ . '/../ltw.db';
    echo $path; // remove esta linha depois
    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    return $db;
}
?>