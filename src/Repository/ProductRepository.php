<?php

namespace App\Repository;


use App\Entity\Product;
use Doctrine\DBAL\Connection;

class ProductRepository
{
    const TABLE = 'products';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function add(Product $product): Product
    {
        $state = $product->toState();

        $this->connection->insert(self::TABLE, $state);

        $state['id'] = $this->connection->lastInsertId();
        Product::fromState($state, $product);

        return $product;
    }

    public function find(int $id): ?Product
    {
        $builder = $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->where('id = ?')
            ->setParameter(0, $id)
            ->setMaxResults(1);

        $row = $builder->execute()->fetch();
        return Product::fromState($row);
    }
}