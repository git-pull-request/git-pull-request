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

use GitPullRequest\DomainModel\Branch\Branch;
use GitPullRequest\DomainModel\Branch\BranchInterface;
use GitPullRequest\DomainModel\Label\LabelCollection;
use GitPullRequest\DomainModel\PullRequest\BitbucketField\BitbucketStateField;
use GitPullRequest\DomainModel\PullRequest\BitbucketPullRequestState;
use GitPullRequest\DomainModel\PullRequest\PullRequestStateInterface;
use GitPullRequest\DomainModel\User\User;
use GitPullRequest\DomainModel\User\UserInterface;
use GitPullRequest\Infrastructure\Builder\Branch\BitbucketBranchBuilder;
use GitPullRequest\Infrastructure\HTTPClient\BitbucketAPIClient;

/**
 * Build a PullRequest from Bitbucket API.
 */
final class BitbucketPullRequestBuilder implements PullRequestBuilderInterface
{
    /** @var array */
    private $rawContent = [];

    /**
     * @param int $pullRequestNumber
     *
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     */
    public function __construct(int $pullRequestNumber)
    {
        $client           = new BitbucketAPIClient();
        $this->rawContent = $client->getPullRequest($pullRequestNumber);
    }

    /** @return UserInterface */
    public function buildAuthor() : UserInterface
    {
        $user = $this->rawContent['author'];

        return new User(
            $user['links']['avatar']['href'],
            $user['display_name'],
            $user['links']['html']['href']
        );
    }

    /**
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \GuzzleHttp\Exception\TransferException
     *
     * @return BranchInterface
     */
    public function buildBase() : BranchInterface
    {
        return $this->buildBranch('destination');
    }

    /**
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \GuzzleHttp\Exception\TransferException
     *
     * @return BranchInterface
     */
    public function buildHead() : BranchInterface
    {
        return $this->buildBranch('source');
    }

    /** @return string */
    public function buildHtmlUrl() : string
    {
        return $this->rawContent['links']['html']['href'];
    }

    /** @return LabelCollection */
    public function buildLabels() : LabelCollection
    {
        return new LabelCollection();
    }

    /** @return int */
    public function buildNumber() : int
    {
        return $this->rawContent['id'];
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return PullRequestStateInterface
     */
    public function buildState() : PullRequestStateInterface
    {
        return new BitbucketPullRequestState(
            new BitbucketStateField(strtolower($this->rawContent['state']))
        );
    }

    /** @return string */
    public function buildTitle() : string
    {
        return $this->rawContent['title'];
    }

    /**
     * @param string $baseOrHead
     *
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return BranchInterface
     */
    private function buildBranch(string $baseOrHead) : BranchInterface
    {
        $base    = $this->rawContent[$baseOrHead];
        $builder = new BitbucketBranchBuilder($base['branch']['name'], $base['repository']['links']['self']['href']);

        return new Branch(
            $builder->buildName(),
            $builder->buildRepository()
        );
    }
}
