<?php

namespace App\Repository;

use App\Entity\Proxy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proxy>
 *
 * @method Proxy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proxy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proxy[]    findAll()
 * @method Proxy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProxyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proxy::class);
    }

    /**
     * @param string $ip
     * @param int    $port
     *
     * @return Proxy|null
     */
    public function findByIpAndPort(string $ip, int $port): ?Proxy
    {
        return $this->findOneBy([
            "ip"      => $ip,
            "port"    => $port,
            "enabled" => true,
        ]);
    }

    /**
     * @param string $usage
     *
     * @return Proxy|null
     *
     * @throws NonUniqueResultException
     */
    public function findLeastUsed(string $usage): ?Proxy
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("p")
            ->from(Proxy::class, "p")
            ->where("p.enabled = true")
            ->andWhere("p.usage = :usage")
            ->orderBy("p.lastUsage", "ASC")
            ->setParameter("usage", $usage)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string      $usage
     * @param string|null $countryIsoCode
     * @param string|null $provider
     *
     * @return Proxy|null
     *
     * @throws NonUniqueResultException
     */
    public function findForUsage(string $usage, ?string $countryIsoCode = null, ?string $provider = null): ?Proxy
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select("p")
                     ->from(Proxy::class, "p")
                     ->where("p.enabled = true")
                     ->andWhere("p.usage = :usage")
                     ->orderBy("p.lastUsage", "ASC")
                     ->setParameter("usage", $usage)
                     ->setMaxResults(1);

        if (!empty($countryIsoCode)) {
            $queryBuilder->andWhere("p.targetCountryIsoCode = :countryIsoCode")
                ->setParameter('countryIsoCode', $countryIsoCode);
        }

        if (!empty($provider)) {
            $queryBuilder->andWhere("p.provider = :provider")
                 ->setParameter('provider', $provider);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

}
