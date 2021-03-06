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

namespace GitPullRequest\Infrastructure\Builder\Branch;

use GitPullRequest\DomainModel\RemoteRepository\RemoteRepositoryInterface;

/**
 * Required methods to create a branch model.
 */
interface BranchBuilderInterface
{
    /** @return string */
    public function buildName() : string;

    /** @return RemoteRepositoryInterface */
    public function buildRepository() : RemoteRepositoryInterface;
}
