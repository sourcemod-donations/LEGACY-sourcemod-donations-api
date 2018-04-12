<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class Product
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var int
     */
    private $price = 0;

    public function toState(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price
        ];
    }

    public static function fromState(array $state, ?self $self = null): self
    {
        $product = $self ?? new self();

        $product->id = $state['id'];
        $product->name = $state['name'];
        $product->description = $state['description'];
        $product->price = $state['price'];

        return $product;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }
}