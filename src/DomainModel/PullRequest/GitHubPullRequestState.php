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

use GitPullRequest\DomainModel\PullRequest\GitHubField\GitHubMergeableField;
use GitPullRequest\DomainModel\PullRequest\GitHubField\GitHubMergeableStateField;

/**
 * Representation of all the fields that defines GitHub to determine whether the pull request is mergeable or not.
 */
final class GitHubPullRequestState implements PullRequestStateInterface
{
    /** @var bool */
    private $locked = false;
    /** @var GitHubMergeableField */
    private $mergeable;
    /** @var GitHubMergeableStateField */
    private $mergeableState;
    /** @var bool */
    private $merged = false;

    /**
     * @param bool                      $locked
     * @param GitHubMergeableField      $mergeable
     * @param GitHubMergeableStateField $mergeableState
     * @param bool                      $merged
     */
    public function __construct(
        bool $locked,
        GitHubMergeableField $mergeable,
        GitHubMergeableStateField $mergeableState,
        bool $merged
    ) {
        $this->locked         = $locked;
        $this->mergeable      = $mergeable;
        $this->mergeableState = $mergeableState;
        $this->merged         = $merged;
    }

    /** @return int */
    public function getComputedState() : int
    {
        $globalState = 0;
        if ($this->merged) {
            $globalState |= PullRequestStateInterface::MERGED;
        }
        if ($this->locked) {
            $globalState |= PullRequestStateInterface::LOCKED;
        }
        switch ((string) $this->mergeableState) {
            case GitHubMergeableStateField::UNSTABLE:
                $globalState |= PullRequestStateInterface::UNSTABLE;
                break;
            case GitHubMergeableStateField::UNKNOWN:
                $globalState |= PullRequestStateInterface::UNKNOWN;
                break;
            case GitHubMergeableStateField::DIRTY:
                $globalState |= PullRequestStateInterface::DIRTY;
                break;
        }

        return $globalState;
    }
}
