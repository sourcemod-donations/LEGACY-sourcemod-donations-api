<?php

namespace App\Entity;


class User
{
    /** @var int */
    private $id;

    /** @var string */
    private $steamid = '';

    /** @var string */
    private $name = '';

    public function __construct(string $steamid)
    {
        $this->steamid = $steamid;
    }

    public function toState(): array
    {
        return [
            'id' => $this->id,
            'steamid' => $this->steamid,
            'name' => $this->name
        ];
    }

    public static function fromState(array $state, ?self $self = null): self
    {
        $user = $self ?? new self($state['steamid']);

        $user->id = $state['id'];
        $user->name = $state['name'];

        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSteamid(): string
    {
        return $this->steamid;
    }

    public function getName(): string
    {
        return $this->name;
    }
}