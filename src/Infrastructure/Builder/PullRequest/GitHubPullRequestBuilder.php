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
use GitPullRequest\DomainModel\Label\Label;
use GitPullRequest\DomainModel\Label\LabelCollection;
use GitPullRequest\DomainModel\PullRequest\GitHubField\GitHubMergeableField;
use GitPullRequest\DomainModel\PullRequest\GitHubField\GitHubMergeableStateField;
use GitPullRequest\DomainModel\PullRequest\GitHubPullRequestState;
use GitPullRequest\DomainModel\PullRequest\PullRequestStateInterface;
use GitPullRequest\DomainModel\User\User;
use GitPullRequest\DomainModel\User\UserInterface;
use GitPullRequest\Infrastructure\Builder\Branch\GitHubBranchBuilder;
use GitPullRequest\Infrastructure\HTTPClient\GitHubAPIClient;

/**
 * Build a PullRequest from GitHub API.
 */
final class GitHubPullRequestBuilder implements PullRequestBuilderInterface
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
        $client           = new GitHubAPIClient();
        $this->rawContent = $client->getPullRequest($pullRequestNumber);
    }

    /** @return UserInterface */
    public function buildAuthor() : UserInterface
    {
        $user = $this->rawContent['user'];

        return new User(
            $user['avatar_url'],
            $user['login'],
            $user['html_url']
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
        return $this->buildBranch('base');
    }

    /**
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \GuzzleHttp\Exception\TransferException
     *
     * @return BranchInterface
     */
    public function buildHead() : BranchInterface
    {
        return $this->buildBranch('head');
    }

    /** @return string */
    public function buildHtmlUrl() : string
    {
        return $this->rawContent['html_url'];
    }

    /**
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return LabelCollection
     */
    public function buildLabels() : LabelCollection
    {
        $apiUri = $this->rawContent['issue_url'].'/labels';
        /* @var string[][] $rawLabels */
        $collection = new LabelCollection();
        $rawLabels  = (new GitHubAPIClient())->get($apiUri);
        foreach ($rawLabels as $rawLabel) {
            $collection->add(new Label($rawLabel['name']));
        }

        return $collection;
    }

    /** @return int */
    public function buildNumber() : int
    {
        return $this->rawContent['number'];
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return PullRequestStateInterface
     */
    public function buildState() : PullRequestStateInterface
    {
        return new GitHubPullRequestState(
            $this->rawContent['locked'],
            new GitHubMergeableField($this->rawContent['mergeable']),
            new GitHubMergeableStateField($this->rawContent['mergeable_state']),
            $this->rawContent['merged']
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
        $builder = new GitHubBranchBuilder($base['ref'], $base['repo']['url']);

        return new Branch(
            $builder->buildName(),
            $builder->buildRepository()
        );
    }
}
