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
 * Helpers to get information from Bitbucket API.
 */
final class BitbucketAPIClient extends AbstractRemoteProviderClient
{
    const API_VERSION = '2.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('bitbucket');
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

        return self::API_VERSION.'/repositories/'.$repoName.'/pullrequests/'.$pullRequestNumber;
    }
}
