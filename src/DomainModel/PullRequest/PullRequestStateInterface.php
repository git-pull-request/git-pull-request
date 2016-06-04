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

namespace GitPullRequest\DomainModel\PullRequest;

/**
 * Representation of a pull request.
 * Pull request may have many state:
 *  - Is it locked ?
 *  - Is it merged ?
 *  - Is it mergeable ?
 *  - Is it clean ?
 */
interface PullRequestStateInterface
{
    const MERGEABLE = 0;
    const LOCKED    = 1;
    const UNKNOWN   = 2;
    const UNSTABLE  = 4;
    const DIRTY     = 8;
    const MERGED    = 16;

    /** @return int */
    public function getComputedState() : int;
}
