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

use GitPullRequest\DomainModel\PullRequest\BitbucketField\BitbucketStateField;

/**
 * Representation of all the fields that defines GitHub to determine whether the pull request is mergeable or not.
 */
final class BitbucketPullRequestState implements PullRequestStateInterface
{
    /** @var BitbucketStateField */
    private $state;

    /**
     * @param BitbucketStateField $state
     */
    public function __construct(BitbucketStateField $state)
    {
        $this->state = $state;
    }

    /** @return int */
    public function getComputedState() : int
    {
        $globalState = 0;
        switch ((string) $this->state) {
            case BitbucketStateField::FULFILLED:
                $globalState |= PullRequestStateInterface::MERGED;
                break;
            case BitbucketStateField::REJECTED:
                $globalState |= PullRequestStateInterface::LOCKED;
                break;
        }

        return $globalState;
    }
}
