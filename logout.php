<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth.php';

logout_user();
flash('success', 'Anda telah keluar dari aplikasi.');
redirect('login.php');
