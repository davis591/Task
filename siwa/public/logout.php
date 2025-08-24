<?php
require __DIR__ . '/../lib/auth.php';
Auth::logout();
header('Location: /public/index.php');
exit;