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

use GitPullRequest\Infrastructure\Builder\RemoteRepository\BitbucketRemoteRepositoryBuilder;
use GitPullRequest\Infrastructure\Builder\RemoteRepository\RemoteRepositoryBuilderInterface;

/**
 * Build a branch from Bitbucket API.
 */
final class BitbucketBranchBuilder extends AbstractBranchBuilder
{
    /**
     * @param string $repoApiURI
     *
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return RemoteRepositoryBuilderInterface
     */
    protected function createBuilder(string $repoApiURI) : RemoteRepositoryBuilderInterface
    {
        return new BitbucketRemoteRepositoryBuilder($repoApiURI);
    }
}
