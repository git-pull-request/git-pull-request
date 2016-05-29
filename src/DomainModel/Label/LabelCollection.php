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
 * Contains every label of a single issue / pull request.
 */
final class LabelCollection
{
    /**
     * @var LabelInterface[]
     */
    private $items = [];

    /**
     * @return LabelInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param LabelInterface $label
     */
    public function add(LabelInterface $label)
    {
        $this->items[] = $label;
    }

    /**
     * @param string|LabelInterface $label
     *
     * @return bool
     */
    public function has($label) : bool
    {
        if (!$label instanceof LabelInterface) {
            $label = new Label($label);
        }

        foreach ($this->items as $item) {
            if ($label->equals($item)) {
                return true;
            }
        }

        return false;
    }
}
