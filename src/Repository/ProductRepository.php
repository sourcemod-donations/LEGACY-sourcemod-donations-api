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

    public function update(Product $product): void
    {
        $state = $product->toState();
        $id = $state['id'];
        unset($state['id']);

        $this->connection->update(self::TABLE, $state, ['id' => $id]);
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
        if(!$row)
            return null;

        return Product::fromState($row);
    }

    /**
     * @return Product[]
     */
    public function findPaged(int $lastId, int $pageSize = 10): array
    {
        $builder = $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->orderBy('id', 'DESC')
            ->setMaxResults($pageSize);

        if($lastId > 0)
        {
            $builder = $builder
            ->where('id < ?')
            ->setParameter(0, $lastId);
        }

        $rows = $builder->execute()->fetchAll();
        $products = [];

        foreach ($rows as $row)
        {
            $products[] = Product::fromState($row);
        }

        return $products;
    }
}