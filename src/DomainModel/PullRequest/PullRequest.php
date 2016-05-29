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

namespace GitPullRequest\DomainModel\PullRequest;

use GitPullRequest\DomainModel\Branch\BranchInterface;
use GitPullRequest\DomainModel\Config\Config;
use GitPullRequest\DomainModel\Label\LabelCollection;
use GitPullRequest\DomainModel\User\UserInterface;
use InvalidArgumentException;
use SemVer\SemVer\Version;
use SemVer\SemVer\VersionSorter;

/**
 * Representation of a pull request.
 */
final class PullRequest implements PullRequestInterface
{
    /** @var UserInterface */
    private $author;
    /** @var BranchInterface */
    private $base;
    /** @var Config */
    private $config;
    /** @var BranchInterface */
    private $head;
    /** @var string */
    private $htmlUrl;
    /** @var LabelCollection */
    private $labels;
    /** @var int */
    private $number;
    /** @var PullRequestStateInterface */
    private $state;
    /** @var string */
    private $title;

    /**
     * @param UserInterface             $author
     * @param BranchInterface           $base
     * @param BranchInterface           $head
     * @param string                    $htmlUrl
     * @param LabelCollection           $labels
     * @param int                       $number
     * @param PullRequestStateInterface $state
     * @param string                    $title
     */
    public function __construct(
        UserInterface $author,
        BranchInterface $base,
        BranchInterface $head,
        string $htmlUrl,
        LabelCollection $labels,
        int $number,
        PullRequestStateInterface $state,
        string $title
    ) {
        $this->author  = $author;
        $this->base    = $base;
        $this->config  = new Config();
        $this->head    = $head;
        $this->htmlUrl = $htmlUrl;
        $this->labels  = $labels;
        $this->number  = $number;
        $this->state   = $state;
        $this->title   = $title;
    }

    /** @return int */
    public function getComputedState() : int
    {
        return $this->state->getComputedState();
    }

    /** @return BranchInterface */
    public function getBase() : BranchInterface
    {
        return $this->base;
    }

    /** @return BranchInterface */
    public function getHead() : BranchInterface
    {
        return $this->head;
    }

    /** @return bool */
    public function isMajor() : bool
    {
        return !$this->isMinor() && !$this->isPatch() && $this->searchLabelOrTitle('labels.major');
    }

    /** @return bool */
    public function isMinor() : bool
    {
        return !$this->isPatch() && $this->searchLabelOrTitle('labels.minor');
    }

    /** @return bool */
    public function isPatch() : bool
    {
        return $this->searchLabelOrTitle('labels.patch');
    }

    /**
     * @param string $labelConfigKey
     *
     * @return bool
     */
    public function searchLabelOrTitle(string $labelConfigKey) : bool
    {
        $label = $this->config->mustGet($labelConfigKey);
        if ($this->hasLabels()) {
            return $this->labels->has($label);
        }

        return false !== strpos($this->title, $label);
    }

    /** @return int */
    public function getNumber() : int
    {
        return $this->number;
    }

    /** @return string */
    public function getTitle() : string
    {
        return $this->title;
    }

    /** @return string */
    public function getHtmlURL() : string
    {
        return $this->htmlUrl;
    }

    /** @return UserInterface */
    public function getAuthor() : UserInterface
    {
        return $this->author;
    }

    /**
     * @param Version[] $existingTags
     *
     * @throws \InvalidArgumentException
     *
     * @return Version
     */
    public function getTag(array $existingTags) : Version
    {
        $previousTag = $this->getPreviousTag($existingTags);
        if (null === $previousTag) {
            return new Version(0, 1, 0);
        }
        if ($this->isPatch()) {
            return $previousTag->patch();
        }
        if ($this->isMinor()) {
            return $previousTag->minor();
        }
        if ($this->isMajor()) {
            return $previousTag->major();
        }

        throw new InvalidArgumentException('Unable to guess next tag version.');
    }

    /**
     * @param Version[] $existingTags
     *
     * @return Version|null
     */
    public function getPreviousTag(array $existingTags)
    {
        if (0 === count($existingTags)) {
            return;
        }
        $tags = VersionSorter::sort($existingTags);

        return end($tags);
    }

    /** @return bool */
    private function hasLabels() : bool
    {
        return 0 !== count($this->labels->getItems());
    }
}
