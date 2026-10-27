<?php
declare(strict_types=1);

namespace App;

use App\Auth\SessionService;
use App\Database\Connection;
use App\Repositories\{UserRepository, RoomRepository, MessageRepository, SessionRepository};
use PDO;

final class Application
{
    public readonly PDO $db;
    public readonly UserRepository $users;
    public readonly RoomRepository $rooms;
    public readonly MessageRepository $messages;
    public readonly SessionService $sessions;

    public function __construct(public readonly array $config)
    {
        $this->db = Connection::open($config['database']);
        $this->users = new UserRepository($this->db);
        $this->rooms = new RoomRepository($this->db);
        $this->messages = new MessageRepository($this->db);
        $this->sessions = new SessionService(new SessionRepository($this->db), $config['secret']);
    }
}
