<?php

/**
 * This file is part of the Happyr Doctrine Specification package.
 *
 * (c) Tobias Nyholm <tobias@happyr.com>
 *     Kacper Gunia <kacper@gunia.me>
 *     Peter Gribanov <info@peter-gribanov.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Happyr\DoctrineSpecification;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Filter\Filter;
use Happyr\DoctrineSpecification\Query\QueryModifier;
use Happyr\DoctrineSpecification\Result\ResultModifier;

/**
 * This trait should be used by a class extending \Doctrine\ORM\EntityRepository.
 */
trait EntitySpecificationRepositoryTrait
{
    /**
     * @var string alias
     */
    private $alias = 'e';

    /**
     * Get results when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @return mixed[]
     */
    public function match($specification, ResultModifier $modifier = null)
    {
        $query = $this->getQuery($specification, $modifier);

        return $query->execute();
    }

    /**
     * Get single result when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @throw Exception\NonUniqueException  If more than one result is found
     * @throw Exception\NoResultException   If no results found
     *
     * @return mixed
     */
    public function matchSingleResult($specification, ResultModifier $modifier = null)
    {
        $query = $this->getQuery($specification, $modifier);

        try {
            return $query->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new Exception\NonUniqueResultException($e->getMessage(), $e->getCode(), $e);
        } catch (NoResultException $e) {
            throw new Exception\NoResultException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get single result or null when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @throw Exception\NonUniqueException  If more than one result is found
     *
     * @return mixed|null
     */
    public function matchOneOrNullResult($specification, ResultModifier $modifier = null)
    {
        try {
            return $this->matchSingleResult($specification, $modifier);
        } catch (Exception\NoResultException $e) {
            return null;
        }
    }

    /**
     * Get single scalar result when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @throw Exception\NonUniqueException  If more than one result is found
     * @throw Exception\NoResultException   If no results found
     *
     * @return mixed
     */
    public function matchSingleScalarResult($specification, ResultModifier $modifier = null)
    {
        $query = $this->getQuery($specification, $modifier);

        try {
            return $query->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            throw new Exception\NonUniqueResultException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get scalar result when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @throw Exception\NonUniqueException  If more than one result is found
     * @throw Exception\NoResultException   If no results found
     *
     * @return mixed
     */
    public function matchScalarResult($specification, ResultModifier $modifier = null)
    {
        $query = $this->getQuery($specification, $modifier);

        return $query->getScalarResult();
    }

    /**
     * Prepare a Query with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @return Query
     */
    public function getQuery($specification, ResultModifier $modifier = null)
    {
        $query = $this->getQueryBuilder($specification)->getQuery();

        if (null !== $modifier) {
            $modifier->modify($query);
        }

        return $query;
    }

    /**
     * @param Filter|QueryModifier $specification
     * @param string|null          $alias
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder($specification, $alias = null)
    {
        $qb = $this->createQueryBuilder($alias ?: $this->getAlias());
        $this->applySpecification($qb, $specification, $alias);

        return $qb;
    }

    /**
     * Iterate results when you match with a Specification.
     *
     * @param Filter|QueryModifier $specification
     * @param ResultModifier|null  $modifier
     *
     * @return \Traversable
     */
    public function iterate($specification, ResultModifier $modifier = null)
    {
        foreach ($this->getQuery($specification, $modifier)->iterate() as $row) {
            yield current($row);
        }
    }

    /**
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param QueryBuilder                    $queryBuilder
     * @param Filter|QueryModifier|mixed|null $specification
     * @param string                          $alias
     *
     * @throws \InvalidArgumentException
     */
    protected function applySpecification(QueryBuilder $queryBuilder, $specification = null, $alias = null)
    {
        if (null === $specification) {
            return;
        }

        if (!$specification instanceof QueryModifier && !$specification instanceof Filter) {
            throw new \InvalidArgumentException(sprintf(
                'Expected argument of type "%s" or "%s", "%s" given.',
                QueryModifier::class,
                Filter::class,
                is_object($specification) ? get_class($specification) : gettype($specification)
            ));
        }

        if ($specification instanceof QueryModifier) {
            $specification->modify($queryBuilder, $alias ?: $this->getAlias());
        }

        if ($specification instanceof Filter
            && $filter = $specification->getFilter($queryBuilder, $alias ?: $this->getAlias())
        ) {
            $queryBuilder->andWhere($filter);
        }
    }
}
