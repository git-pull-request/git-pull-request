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

namespace GitPullRequest\Infrastructure\Builder\RemoteRepository;

use GitPullRequest\Infrastructure\HTTPClient\GitHubAPIClient;

/**
 * Class.
 */
final class GitHubRemoteRepositoryBuilder implements RemoteRepositoryBuilderInterface
{
    /** @var array */
    private $rawContent = [];

    /**
     * @param string $apiRepositoryUrl
     *
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     */
    public function __construct(string $apiRepositoryUrl)
    {
        $this->rawContent = (new GitHubAPIClient())->get($apiRepositoryUrl);
    }

    /** @return string */
    public function buildCloneUrl() : string
    {
        return $this->rawContent['clone_url'];
    }

    /** @return string */
    public function buildHtmlURL() : string
    {
        return $this->rawContent['html_url'];
    }

    /** @return string */
    public function buildDefaultBranch() : string
    {
        return $this->rawContent['default_branch'];
    }

    /** @return string */
    public function buildName() : string
    {
        return $this->rawContent['full_name'];
    }

    /** @return string */
    public function buildSshURL() : string
    {
        return $this->rawContent['ssh_url'];
    }
}
