<?php

declare(strict_types=1);

namespace App\Authenticator;

use App\Model\Repository\AdminRepository;
use Nette;
use Nette\Security\Passwords;

class FrontAuthenticator implements Nette\Security\Authenticator
{
    use Nette\SmartObject;




    /**
     * @param \Nette\Security\Passwords $passwords
     */
    public function __construct(
        private AdminRepository $adminRepository,
        private Passwords $passwords,
    ) {
    }


    /**
     * @param string $login
     * @param string $password
     * @return \Nette\Security\SimpleIdentity
     * @throws \Nette\Security\AuthenticationException
     */
    public function authenticate(string $login, string $password): Nette\Security\SimpleIdentity
    {
        $row = $this->adminRepository->findBy(['login' => $login])->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        $identityData = $row->toArray();
        unset($identityData['password']);

        return new Nette\Security\SimpleIdentity($row->id, null, (array) $identityData);
    }
}
