<?php

namespace App\Repository;

use App\Entity\MailRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MailRecipient>
 */
class MailRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailRecipient::class);
    }

    /**
     * @return MailRecipient[]
     */
    public function findForDigest(bool $weekly): array
    {
        return $this->findBy([$weekly ? 'weekly' : 'daily' => true]);
    }
}