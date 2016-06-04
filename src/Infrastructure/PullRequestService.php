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

namespace GitPullRequest\Infrastructure;

use GitPullRequest\DomainModel\PullRequest\PullRequest;
use GitPullRequest\Infrastructure\Builder\PullRequest\BitbucketPullRequestBuilder;
use GitPullRequest\Infrastructure\Builder\PullRequest\GitHubPullRequestBuilder;
use InvalidArgumentException;

final class PullRequestService
{
    /**
     * Gets a PullRequest.
     *
     * @param int    $pullRequestNumber
     * @param string $provider
     *
     * @throws \UnexpectedValueException
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \InvalidArgumentException
     *
     * @return PullRequest
     */
    public function get(int $pullRequestNumber, string $provider) : PullRequest
    {
        switch (strtolower($provider)) {
            case 'bitbucket':
                $builder = new BitbucketPullRequestBuilder($pullRequestNumber);
                break;
            case 'github':
                $builder = new GitHubPullRequestBuilder($pullRequestNumber);
                break;
            case 'gitlab':
                throw new InvalidArgumentException('Gitlab is not yet implemented. Contribution welcome.');
            default:
                throw new InvalidArgumentException(sprintf('Unknown provider name "%s".', $provider));
        }

        return new PullRequest(
            $builder->buildAuthor(),
            $builder->buildBase(),
            $builder->buildHead(),
            $builder->buildHtmlUrl(),
            $builder->buildLabels(),
            $builder->buildNumber(),
            $builder->buildState(),
            $builder->buildTitle()
        );
    }
}
