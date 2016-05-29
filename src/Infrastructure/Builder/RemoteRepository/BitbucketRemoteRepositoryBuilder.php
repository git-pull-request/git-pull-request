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

use GitPullRequest\Infrastructure\HTTPClient\BitbucketAPIClient;

/**
 * Class.
 */
final class BitbucketRemoteRepositoryBuilder implements RemoteRepositoryBuilderInterface
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
        $this->rawContent = (new BitbucketAPIClient())->get($apiRepositoryUrl);
    }

    /** @return string */
    public function buildCloneUrl() : string
    {
        return $this->buildURL('https');
    }

    /** @return string */
    public function buildHtmlURL() : string
    {
        return $this->rawContent['links']['html']['href'];
    }

    /** @return string */
    public function buildDefaultBranch() : string
    {
        return '';
    }

    /** @return string */
    public function buildName() : string
    {
        return $this->rawContent['full_name'];
    }

    /** @return string */
    public function buildSshURL() : string
    {
        return $this->buildURL('ssh');
    }

    /**
     * @param string $urlType
     *
     * @return string
     */
    private function buildURL(string $urlType) : string
    {
        /** @var array $clones */
        $clones = $this->rawContent['links']['clone'];
        foreach ($clones as $clone) {
            if ($urlType === $clone['name']) {
                return $clone['href'];
            }
        }

        return '';
    }
}
