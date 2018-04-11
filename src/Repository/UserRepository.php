<?php

namespace App\Repository;


use App\Entity\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    const TABLE = 'users';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function add(User $user): User
    {
        $state = $user->toState();

        $this->connection->insert(self::TABLE, $state);

        $state['id'] = $this->connection->lastInsertId();
        User::fromState($state, $user);

        return $user;
    }

    public function findBySteamid(string $steamid): ?User
    {
        $builder = $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->where('steamid = ?')
            ->setParameter(0, $steamid)
            ->setMaxResults(1);

        $row = $builder->execute()->fetch();

        if (!$row)
            return null;

        return User::fromState($row);
    }
}