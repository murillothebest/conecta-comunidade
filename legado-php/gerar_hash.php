<?php
/**
 * Utilitário opcional: regenera o hash da senha admin123.
 * Execute via CLI no XAMPP:
 *   C:\xampp\php\php.exe sql\gerar_hash.php
 */
echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;
