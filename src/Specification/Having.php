<?php

namespace Happyr\DoctrineSpecification\Specification;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Filter\Filter;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * @deprecated Will be removed in 2.0. Use \Happyr\DoctrineSpecification\Query\Having instead.
 */
class Having implements Specification
{
    /**
     * @var Filter|QueryModifier|string
     */
    protected $child;

    /**
     * @param Filter|QueryModifier|string $child
     */
    public function __construct($child)
    {
        $this->child = $child;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        if ($this->child instanceof QueryModifier) {
            $this->child->modify($qb, $dqlAlias);
        }

        if ($this->child instanceof Filter) {
            $qb->having($this->child->getFilter($qb, $dqlAlias));
        } else {
            $qb->having($this->child);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     *
     * @return string
     */
    public function getFilter(QueryBuilder $qb, $dqlAlias)
    {
        return;
    }
}
