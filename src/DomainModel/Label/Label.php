<?php

/*
 * This file is part of git-pull-request/git-pull-request.
 *
 * (c) Julien Dufresne <https://github.com/git-pull-request/git-pull-request>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GitPullRequest\DomainModel\Label;

/**
 * A label.
 */
final class Label implements LabelInterface
{
    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Checks if two label instances are equals.
     *
     * @param LabelInterface $label
     *
     * @return bool
     */
    public function equals(LabelInterface $label) : bool
    {
        if (!$label instanceof self) {
            return false;
        }

        return $this->name === $label->name;
    }
}
