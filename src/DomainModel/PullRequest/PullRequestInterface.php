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

use GitPullRequest\DomainModel\Branch\BranchInterface;
use GitPullRequest\DomainModel\User\UserInterface;
use SemVer\SemVer\Version;

/**
 * Specification for the PullRequest model object.
 */
interface PullRequestInterface
{
    /** @return UserInterface */
    public function getAuthor() : UserInterface;

    /** @return int */
    public function getComputedState() : int;

    /** @return BranchInterface */
    public function getBase() : BranchInterface;

    /** @return BranchInterface */
    public function getHead() : BranchInterface;

    /** @return string */
    public function getHtmlURL() : string;

    /** @return int */
    public function getNumber() : int;

    /**
     * @param Version[] $existingTags
     *
     * @return Version|null
     */
    public function getPreviousTag(array $existingTags);

    /** @return string */
    public function getTitle() : string;

    /**
     * @param Version[] $existingTags
     *
     * @return Version
     */
    public function getTag(array $existingTags) : Version;

    /** @return bool */
    public function isMajor() : bool;

    /** @return bool */
    public function isMinor() : bool;

    /** @return bool */
    public function isPatch() : bool;
}
