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

namespace GitPullRequest\Infrastructure\HTTPClient;

/**
 * Class.
 */
final class GitHubAPIClient extends AbstractRemoteProviderClient
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('github');
    }

    /** @return array */
    protected function customConfig() : array
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ];
    }

    /**
     * @param int $pullRequestNumber
     *
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return string
     */
    protected function getPullRequestPath(int $pullRequestNumber) : string
    {
        $repoName = $this->config->mustGet('provider.repository');

        return 'repos/'.$repoName.'/pulls/'.$pullRequestNumber;
    }
}
