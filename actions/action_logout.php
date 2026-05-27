<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/landingpage.php');
    exit;
}

verifyCsrfToken('../pages/landingpage.php');

logoutUser();

header('Location: ../pages/landingpage.php');
exit;
