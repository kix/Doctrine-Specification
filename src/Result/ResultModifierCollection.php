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

namespace Happyr\DoctrineSpecification\Result;

use Doctrine\ORM\AbstractQuery;
use Happyr\DoctrineSpecification\Exception\InvalidArgumentException;

class ResultModifierCollection implements ResultModifier
{
    /**
     * @var ResultModifier[]
     */
    private $children;

    /**
     * Construct it with one or more instances of ResultModifier.
     */
    public function __construct()
    {
        $this->children = func_get_args();
    }

    /**
     * @param AbstractQuery $query
     */
    public function modify(AbstractQuery $query)
    {
        foreach ($this->children as $child) {
            if (!$child instanceof ResultModifier) {
                throw new InvalidArgumentException(sprintf(
                    'Child passed to ResultModifierCollection must be an instance of %s, but instance of %s found',
                    ResultModifier::class,
                    get_class($child)
                ));
            }

            $child->modify($query);
        }
    }
}
