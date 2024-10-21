<?php

declare(strict_types=1);

namespace WolfShop\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class DummyApiUser implements UserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_API'];
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return Uuid::v4()->toString();
    }
}
