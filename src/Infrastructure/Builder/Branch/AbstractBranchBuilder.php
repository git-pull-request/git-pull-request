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

use GitPullRequest\DomainModel\RemoteRepository\RemoteRepository;
use GitPullRequest\DomainModel\RemoteRepository\RemoteRepositoryInterface;
use GitPullRequest\Infrastructure\Builder\RemoteRepository\RemoteRepositoryBuilderInterface;

/**
 * AbstractBranchBuilder.
 */
abstract class AbstractBranchBuilder implements BranchBuilderInterface
{
    /** @var string */
    private $branchName;
    /** @var string */
    private $repoApiURI;

    /**
     * @param string $branchName
     * @param string $repoApiURI
     */
    public function __construct(string $branchName, string $repoApiURI)
    {
        $this->branchName = $branchName;
        $this->repoApiURI = $repoApiURI;
    }

    /** @return string */
    public function buildName() : string
    {
        return $this->branchName;
    }

    /**
     * @return RemoteRepositoryInterface
     */
    public function buildRepository() : RemoteRepositoryInterface
    {
        $remoteRepositoryBuilder = $this->createBuilder($this->repoApiURI);

        return new RemoteRepository(
            $remoteRepositoryBuilder->buildCloneUrl(),
            $remoteRepositoryBuilder->buildDefaultBranch(),
            $remoteRepositoryBuilder->buildHtmlURL(),
            $remoteRepositoryBuilder->buildName(),
            $remoteRepositoryBuilder->buildSshURL()
        );
    }

    /**
     * @param string $repoApiURI
     *
     * @return RemoteRepositoryBuilderInterface
     */
    abstract protected function createBuilder(string $repoApiURI) : RemoteRepositoryBuilderInterface;
}
