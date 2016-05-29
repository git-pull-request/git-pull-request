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

namespace GitPullRequest\Application;

use GitPullRequest\Application\Merge\MergePullRequestService;
use GitPullRequest\DomainModel\PullRequest\PullRequestInterface;
use GitPullRequest\DomainModel\PullRequest\PullRequestStateInterface;
use GitPullRequest\Infrastructure\PullRequestService;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to merge a pull request.
 */
final class MergeCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('merge')
            ->setDescription('Merge a Pull Request')
            ->addArgument('identifier', InputArgument::REQUIRED, 'ID/Number of the pull request.')
            ->addOption('tag', 't', InputOption::VALUE_NONE, 'If the merge succeed, create a tag.')
            ->addOption('changelog', 'c', InputOption::VALUE_NONE, 'Add an entry in the CHANGELOG.md.')
            ->setHelp(file_get_contents(__DIR__.'/.help/merge'));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if ($input->getOption('changelog')) {
            $input->setOption('tag', true);
        }

        try {
            return $this->doExecute($input);
        } catch (\Exception $exception) {
            $this->getIo()->error($exception->getMessage());

            return 1;
        }
    }

    /**
     * @param InputInterface $input
     *
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return int
     */
    private function doExecute(InputInterface $input) : int
    {
        $pullRequest = $this->stepContactRemoteRepository((int) $input->getArgument('identifier'));
        if (!$this->stepCheckPullRequestStatus($pullRequest)) {
            return 1;
        }

        $this->startStep('Create clean copy');
        $mergePullRequestService = new MergePullRequestService($pullRequest);
        $mergePullRequestService->prepare();
        $this->stepSucceeded();
        if ($input->getOption('changelog')) {
            $this->startStep('Update changelog');
            $mergePullRequestService->addChangelog();
            $this->stepSucceeded();
        }
        $this->startStep('Merge');
        $mergePullRequestService->merge();
        $this->stepSucceeded();
        if ($input->getOption('tag')) {
            $this->startStep('Create tag');
            $mergePullRequestService->tag();
            $this->stepSucceeded();
        }

        $this->getIo()->note(
            'You may now review the merge (run your tests script, ...) and push it to your remote server.'
        );

        return 0;
    }

    /**
     * @param int $pullRequestNumber
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     *
     * @return PullRequestInterface
     */
    private function stepContactRemoteRepository(int $pullRequestNumber) : PullRequestInterface
    {
        $this->startStep(sprintf('Retrieve information about PR #%d', $pullRequestNumber));
        $provider           = $this->getConfig()->mustGet('provider.name');
        $pullRequestService = new PullRequestService();
        $pullRequest        = $pullRequestService->get($pullRequestNumber, $provider);
        $this->stepSucceeded();

        return $pullRequest;
    }

    /**
     * @param PullRequestInterface $pullRequest
     *
     * @return bool
     */
    private function stepCheckPullRequestStatus(PullRequestInterface $pullRequest) : bool
    {
        $this->startStep('Check pull request status');
        $state = $pullRequest->getComputedState();
        if (($state & PullRequestStateInterface::MERGED) === PullRequestStateInterface::MERGED) {
            return $this->stepFailed('This pull request is already merged.');
        }

        if (($state & PullRequestStateInterface::UNSTABLE) === PullRequestStateInterface::UNSTABLE) {
            return $this->stepFailed('This pull request is unstable. We can not merge it safely.');
        }

        if (($state & PullRequestStateInterface::DIRTY) === PullRequestStateInterface::DIRTY) {
            return $this->stepFailed('This pull request is dirty. We can not merge it safely.');
        }

        if (!$this->checkOneStatus($state, PullRequestStateInterface::UNKNOWN, 'has not been tested yet.')) {
            return false;
        }

        if (!$this->checkOneStatus($state, PullRequestStateInterface::LOCKED, 'is locked.')) {
            return false;
        }

        $this->stepSucceeded();

        return true;
    }

    /**
     * @param int    $state
     * @param int    $comparedToState
     * @param string $warningMessage
     *
     * @return bool
     */
    private function checkOneStatus(int $state, int $comparedToState, string $warningMessage) : bool
    {
        if (($state & $comparedToState) !== $comparedToState) {
            return true;
        }

        $this->stepWarning('This pull request '.$warningMessage);

        return $this->getIo()->confirm('Merge it anyway ?', false);
    }
}
