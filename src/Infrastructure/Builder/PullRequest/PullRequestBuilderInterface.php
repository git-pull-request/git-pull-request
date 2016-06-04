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

namespace GitPullRequest\Infrastructure\Builder\PullRequest;

use GitPullRequest\DomainModel\Branch\BranchInterface;
use GitPullRequest\DomainModel\Label\LabelCollection;
use GitPullRequest\DomainModel\PullRequest\PullRequestStateInterface;
use GitPullRequest\DomainModel\User\UserInterface;

/**
 * Required methods to create a pull request.
 */
interface PullRequestBuilderInterface
{
    /** @param int $pullRequestNumber */
    public function __construct(int $pullRequestNumber);

    /** @return UserInterface */
    public function buildAuthor() : UserInterface;

    /** @return BranchInterface */
    public function buildBase() : BranchInterface;

    /** @return BranchInterface */
    public function buildHead() : BranchInterface;

    /** @return string */
    public function buildHtmlUrl() : string;

    /** @return LabelCollection */
    public function buildLabels() : LabelCollection;

    /** @return int */
    public function buildNumber() : int;

    /** @return PullRequestStateInterface */
    public function buildState() : PullRequestStateInterface;

    /** @return string */
    public function buildTitle() : string;
}
