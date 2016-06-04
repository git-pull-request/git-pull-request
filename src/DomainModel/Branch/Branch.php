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

namespace GitPullRequest\DomainModel\Branch;

use GitPullRequest\DomainModel\RemoteRepository\RemoteRepositoryInterface;

/**
 * Info about a branch.
 */
final class Branch implements BranchInterface
{
    /** @var string */
    private $name;
    /** @var RemoteRepositoryInterface */
    private $remoteRepository;

    /**
     * @param string                    $name
     * @param RemoteRepositoryInterface $repository
     */
    public function __construct(string $name, RemoteRepositoryInterface $repository)
    {
        $this->name             = $name;
        $this->remoteRepository = $repository;
    }

    /** @return string */
    public function getName() : string
    {
        return $this->name;
    }

    /** @return RemoteRepositoryInterface */
    public function getRemoteRepository() : RemoteRepositoryInterface
    {
        return $this->remoteRepository;
    }
}
