<?php
require_once __DIR__ . '/classes/AbstractModel.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Photo.php';

use App\Admin;
use App\Buyer;
use App\User;
use App\Photo;

echo "OOP Demo - Encapsulation, Inheritance, & Abstraction\n";

// Encapsulation demo: properties are private, use methods to access them
$user = new User('andi', 'andi@example.com', 'rahasia');
echo "Username via getter: " . $user->getUsername() . "\n";

// Can't do $user->passwordHash (private) — that's encapsulation in practice

// Verify password via public method
echo "Password correct? " . ($user->verifyPassword('rahasia') ? 'yes' : 'no') . "\n";

// Inheritance demo: Admin and Buyer extend User
$admin = new Admin('admin', 'admin@example.com', 'adminpass');
echo "Admin username: " . $admin->getUsername() . "\n";

// Photo usage (encapsulation of metadata + helper to move file)
$photo = new Photo('example.jpg', 'Contoh Foto', 1);
// For demo purposes we won't actually move files here; show getters instead
echo "Photo filename: " . $photo->getFilename() . "\n";

echo "User and Photo both extend AbstractModel, so they share saveToDb()\n";

echo "\nLihat file: classes/AbstractModel.php, classes/User.php, dan classes/Photo.php untuk implementasi.\n";

// Quick note: to integrate these into your existing pages, require the class files
// then instantiate objects and call their methods instead of using global arrays.
