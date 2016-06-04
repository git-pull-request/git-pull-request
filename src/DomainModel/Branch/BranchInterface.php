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

namespace GitPullRequest\DomainModel\Branch;

use GitPullRequest\DomainModel\RemoteRepository\RemoteRepositoryInterface;

/**
 * Specifications of a branch model object.
 */
interface BranchInterface
{
    /** @return RemoteRepositoryInterface */
    public function getRemoteRepository() : RemoteRepositoryInterface;

    /** @return string */
    public function getName() : string;
}
