<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';

logoutUser();

header('Location: ../pages/landingpage.php');
exit;
