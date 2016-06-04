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
 * Specification for the Label model object.
 * A label is used to give a quick idea of what the pull request or issue is about.
 */
interface LabelInterface
{
    /**
     * Checks if two label instances are equals.
     *
     * @param LabelInterface $label
     *
     * @return bool
     */
    public function equals(LabelInterface $label) : bool;
}
