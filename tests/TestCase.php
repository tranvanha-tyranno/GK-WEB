<?php
declare(strict_types=1);
namespace Tests;
use App\Database\Connection;
use PDO;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected PDO $db;
    protected function setUp(): void
    {
        $this->db = Connection::open(':memory:');
        $this->db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
        $insert = $this->db->prepare('INSERT INTO users(id, username, display_name, password_hash) VALUES (?, ?, ?, ?)');
        foreach ([[1, 'alice', 'Alice Nguyen'], [2, 'bob', 'Bob Tran']] as $row) { $insert->execute([...$row, password_hash('test-password', PASSWORD_DEFAULT)]); }
        $this->db->exec("INSERT INTO rooms(id, name) VALUES (1, 'General'), (2, 'Engineering')");
    }
}
