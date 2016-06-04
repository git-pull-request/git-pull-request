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

use GitPullRequest\DomainModel\Config\Config;
use GuzzleHttp\Client;

/**
 * AbstractRemoteProviderClient.
 */
abstract class AbstractRemoteProviderClient implements RemoteProviderClientInterface
{
    /** @var Config */
    protected $config;
    /** @var string */
    private $providerName;

    /**
     * Constructor.
     *
     * @param string $providerName
     */
    public function __construct(string $providerName)
    {
        $this->config       = new Config();
        $this->providerName = $providerName;
    }

    /**
     * @param string $url
     *
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return mixed
     */
    public function get(string $url)
    {
        $client = $this->createClient();

        $body = (string) $client->request('GET', $url)->getBody();

        return json_decode($body, true);
    }

    /**
     * @param int $pullRequestNumber
     *
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return array
     */
    public function getPullRequest(int $pullRequestNumber)
    {
        return $this->get($this->getPullRequestPath($pullRequestNumber));
    }

    /**
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return Client
     */
    protected function createClient() : Client
    {
        $config = array_merge(
            [
                'base_uri' => $this->config->mustGet('provider.api'),
                'auth'     => [
                    $this->config->mustGet(['auth.user', sprintf('providers.%s.user', $this->providerName)]),
                    $this->config->mustGet(['auth.password', sprintf('providers.%s.password', $this->providerName)]),
                ],
            ],
            $this->customConfig()
        );

        return new Client($config);
    }

    /**
     * @return array
     */
    protected function customConfig() : array
    {
        return [];
    }

    /**
     * @param int $pullRequestNumber
     *
     * @return string
     */
    abstract protected function getPullRequestPath(int $pullRequestNumber) : string;
}
