<?php
session_start();
session_destroy(); // Session löschen
header('Location: ../pages/login.php');
exit;
