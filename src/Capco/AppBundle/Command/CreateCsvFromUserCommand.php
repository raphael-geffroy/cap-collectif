<?php

namespace Capco\AppBundle\Command;

use Box\Spout\Common\Type;
use Capco\AppBundle\Command\Utils\ExportUtils;
use Capco\AppBundle\Utils\Arr;
use Capco\UserBundle\Entity\User;
use Box\Spout\Writer\WriterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Capco\AppBundle\GraphQL\InfoResolver;
use Overblog\GraphQLBundle\Request\Executor;
use Capco\UserBundle\Repository\UserRepository;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Capco\AppBundle\EventListener\GraphQlAclListener;
use Capco\AppBundle\Repository\UserArchiveRepository;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCsvFromUserCommand extends BaseExportCommand
{
    protected static $defaultName = 'capco:export:user';

    protected $userRepository;
    protected $userArchiveRepository;
    protected $em;
    protected $executor;
    protected $listener;
    protected $kernelRootDir;

    public function __construct(
        UserRepository $userRepository,
        UserArchiveRepository $userArchiveRepository,
        EntityManagerInterface $em,
        Executor $executor,
        GraphQlAclListener $listener,
        ExportUtils $exportUtils,
        string $kernelRootDir
    ) {
        $listener->disableAcl();
        $this->userRepository = $userRepository;
        $this->userArchiveRepository = $userArchiveRepository;
        $this->em = $em;
        $this->executor = $executor;
        $this->listener = $listener;
        $this->kernelRootDir = $kernelRootDir;

        parent::__construct($exportUtils);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Create csv file from user data')->addArgument(
            'userId',
            InputArgument::REQUIRED,
            'The ID of the user'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $userId = $input->getArgument('userId');
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        $userId = GlobalId::toGlobalId('User', $userId);
        $datas = $this->requestDatas($userId);
        foreach ($datas as $key => $value) {
            $this->createCsv($userId, $value, $key);
        }

        $archive = $this->userArchiveRepository->getLastForUser($user);

        if ($archive) {
            $archive->setReady(true);
            $archive->setPath(trim($this->getZipFilenameForUser($userId)));
            $this->em->flush();
        }

        $output->writeln($this->getZipFilenameForUser($userId));
    }

    protected function requestDatas(string $userId): array
    {
        $datas = [];

        $types = [
            'user' => $this->getUserGraphQLQuery($userId),
            'questions' => $this->getRepliesGraphQLQuery($userId),
            'medias' => $this->getMediasGraphQLQuery($userId),
            'groups' => $this->getGroupsGraphQLQuery($userId),
            'reports' => $this->getReportsGraphQLQuery($userId),
            'events' => $this->getEventsGraphQLQuery($userId),
            'proposals' => $this->getProposalsGraphQLQuery($userId),
            'opinions' => $this->getOpinionGraphQLQuery($userId),
            'opinionsVersion' => $this->getOpinionVersionGraphQLQuery($userId),
            'arguments' => $this->getArgumentGraphQLQuery($userId),
            'sources' => $this->getSourceGraphQLQuery($userId),
            'votes' => $this->getVotesGraphQLQuery($userId),
        ];

        foreach ($types as $type => $query) {
            $datas[$type] = $this->executor
                ->execute('internal', [
                    'query' => $query,
                    'variables' => [],
                ])
                ->toArray();
        }

        return $datas;
    }

    protected function getZipFilenameForUser(string $userId): string
    {
        $hash = sha1($userId . time());

        return "${hash}.zip";
    }

    protected function getZipPathForUser(string $userId): string
    {
        return $this->kernelRootDir . '/../web/export/' . $this->getZipFilenameForUser($userId);
    }

    protected function createZipArchive(
        string $zipName,
        array $files,
        bool $removeFilesAfter = true,
        bool $isMedias = false
    ): void {
        $zip = new \ZipArchive();

        $zip->open($zipName, \ZipArchive::CREATE);

        if ($isMedias) {
            $zip->addEmptyDir('pictures');
            foreach ($files as $file) {
                foreach ($file as $localName => $path) {
                    $zip->addFile($path, '/pictures/' . $localName);
                }
            }
        } else {
            foreach ($files as $file) {
                foreach ($file as $localName => $path) {
                    $zip->addFile($path, $localName);
                }
            }
        }

        $zip->close();

        if ($removeFilesAfter) {
            foreach ($files as $file) {
                foreach ($file as $localName => $path) {
                    unlink($path);
                }
            }
        }
    }

    protected function createCsv(string $userId, array $data, string $type): void
    {
        $writer = WriterFactory::create(Type::CSV);
        $writer->openToFile($this->getPath());

        if (isset($data['errors'])) {
            throw new \RuntimeException('Failed to query GraphQL to export userId ' . $userId);
        }
        unset($data['extensions']);

        // set headers row
        $header = $this->getCleanHeadersName($data, $type);
        $writer->addRow($header);

        $rows = [];
        //we need to handle indepth arrays who are not mapped
        if (($contributions = Arr::path($data, 'data.node.contributions.edges'))) {
            $rows = $this->getCleanArrayForRowInsert($contributions, $header, true);
        } elseif (($medias = Arr::path($data, 'data.node.medias'))) {
            $this->exportMedias($medias, $userId);
            $rows = $this->getCleanArrayForRowInsert($medias, $header);
        } elseif (($groups = Arr::path($data, 'data.node.groups.edges'))) {
            $rows = $this->getCleanArrayForRowInsert($groups, $header, true);
        } elseif (($reports = Arr::path($data, 'data.node.reports.edges'))) {
            $rows = $this->getCleanArrayForRowInsert($reports, $header, true);
        } elseif (($events = Arr::path($data, 'data.node.events.edges'))) {
            $rows = $this->getCleanArrayForRowInsert($events, $header, true);
        } elseif (($votes = Arr::path($data, 'data.node.votes.edges'))) {
            $rows = $this->getCleanArrayForRowInsert($votes, $header, true);
        } else {
            foreach ($header as $value) {
                $value = Arr::path($data, "data.node.${value}") ?? '';
                $rows[] = $value;
            }
        }

        $rows = array_map([$this->exportUtils, 'parseCellValue'], $rows);

        if (!empty($rows) && \is_array($rows[0])) {
            $writer->addRows($rows);
        } else {
            $writer->addRow($rows);
        }

        $writer->close();

        if ($header) {
            $this->createZipArchive($this->getZipPathForUser($userId), [
                ["${type}.csv" => $this->getPath()],
            ]);
        }
    }

    protected function getCleanArrayForRowInsert(
        array $contents,
        array $header,
        bool $isNode = false
    ): array {
        $rows = [];
        $rowCounter = 0;
        $responsesInserted = false;

        foreach ($contents as $content) {
            foreach ($header as $columnKey => $columnName) {
                if ($isNode) {
                    if (
                        false !== strpos($columnName, 'responses_') &&
                        false === $responsesInserted
                    ) {
                        $responsesDatas = $this->handleMultipleResponsesForQuestions(
                            $content,
                            $rowCounter,
                            $columnKey,
                            $rows
                        );
                        $rows = $responsesDatas['rows'];
                        $rowCounter = $responsesDatas['counter'];
                        $responsesInserted = true;
                    }
                    $cellData = Arr::path($content['node'], $columnName, null, '_');
                } else {
                    $cellData = Arr::path($content, $columnName, null, '_');
                }

                if (!\is_array($cellData)) {
                    $rows[$rowCounter][] = false === $cellData ? 0 : $cellData;
                }
            }
            ++$rowCounter;
        }

        return $rows;
    }

    protected function handleMultipleResponsesForQuestions(
        array $responses,
        int $rowCounter,
        int $columnKey,
        array $rows
    ): array {
        // A question can have multiple responses so we insert a line for each response
        $emptyRow = array_fill(0, $columnKey - 1, null);

        foreach ($responses['node']['responses'] as $response) {
            if (
                $response['question']['title'] &&
                'ValueResponse' === $response['__typename'] &&
                null !== $response['formattedValue']
            ) {
                $rows[$rowCounter][$columnKey] = $response['question']['title'];
                $rows[$rowCounter][$columnKey + 1] = $response['formattedValue'];
                ++$rowCounter;
                $rows[$rowCounter] = $emptyRow;
            }
        }

        return ['rows' => $rows, 'counter' => $rowCounter];
    }

    protected function exportMedias(array $medias, string $userId)
    {
        $mediasPath = $this->kernelRootDir . '/../web/media/default/0001/01/';

        foreach ($medias as $media) {
            if (isset($media['providerReference'])) {
                $fileToCopy = $mediasPath . $media['providerReference'];

                $this->createZipArchive(
                    $this->getZipPathForUser($userId),
                    [[$media['providerReference'] => $fileToCopy]],
                    false,
                    true
                );
            }
        }
    }

    protected function getCleanHeadersName($data, string $type): array
    {
        $infoResolver = new InfoResolver();
        $header = array_map(
            function (string $header) use ($type) {
                $header = str_replace('data_node_', '', $header);

                if ('medias' === $type) {
                    $header = str_replace('medias_', '', $header);
                } elseif ('groups' === $type) {
                    $header = str_replace('groups_edges_node_', '', $header);
                } elseif ('reports' === $type) {
                    $header = str_replace('reports_edges_node_', '', $header);
                } elseif ('events' === $type) {
                    $header = str_replace('events_edges_node_', '', $header);
                } elseif ('votes' === $type) {
                    $header = str_replace('votes_edges_node_', '', $header);
                } else {
                    $header = str_replace('contributions_edges_node_', '', $header);
                }

                return $header;
            },
            array_filter($infoResolver->guessHeadersFromFields($data), function (string $header) {
                return 'data_node_contributions_edges_node_responses___typename' !== $header;
            })
        );

        return $header;
    }

    protected function getFilename(): string
    {
        return 'data.csv';
    }

    protected function getPath(): string
    {
        return $this->kernelRootDir . '/../web/export/' . $this->getFilename();
    }

    protected function getUserGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      id
      email
      username
      userType {
        name
      }
      createdAt
      updatedAt
      lastLogin
      rolesText
      consentExternalCommunication
      enabled
      locked
      phoneConfirmed
      phoneConfirmationSentAt
      gender
      firstname
      lastname
      dateOfBirth
      websiteUrl
      biography
      address
      address2
      neighborhood
      zipCode
      city
      phone
      url
      samlId
      googleId
      facebookId
      facebookUrl
      twitterUrl
      linkedInUrl
      opinionsCount
      opinionVotesCount
      opinionVersionsCount
      argumentsCount
      argumentVotesCount
      proposalsCount
      proposalVotesCount
      commentVotesCount
      sourcesCount
      repliesCount
      postCommentsCount
      eventCommentsCount
      projectsCount
    }
  }
}
EOF;
    }

    protected function getOpinionGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: OPINION) {
        edges {
          node {
            ... on Opinion {
              title
              bodyText
              section {
                title
              }
              createdAt
              updatedAt
              url
              published
              trashed
              trashedAt
              trashedReason
              votes(first: 0) {
                totalCount
              }
              arguments(first: 0) {
                totalCount
              }
              sources(first: 0) {
                totalCount
              }
              versions(first: 0) {
                totalCount
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }

    protected function getOpinionVersionGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: OPINIONVERSION) {
        edges {
          node {
            ... on Version {
              title
              bodyText
              related {
                kind
                url
              }
              comment
              createdAt
              updatedAt
              url
              published
              trashed
              trashedAt
              trashedReason
              votes(first: 0) {
                totalCount
              }
              arguments(first: 0) {
                totalCount
              }
              sources(first: 0) {
                totalCount
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }

    protected function getArgumentGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: ARGUMENT) {
        edges {
          node {
            ... on Argument {
              body
              related {
                kind
                url
              }
              type
              createdAt
              updatedAt
              url
              published
              trashed
              trashedAt
              trashedReason
              votes(first: 0) {
                totalCount
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }

    protected function getSourceGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: SOURCE) {
        edges {
          node {
            ... on Source {
              related {
                kind
                url
              }
              url
              body
              createdAt
              updatedAt
              published
              trashed
              trashedAt
              trashedReason
              votes(first: 0) {
                totalCount
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }

    protected function getVotesGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      votes {
        edges {
          node {
            kind
            related {
              kind
              url
            }
            createdAt
            ... on PrivatableVote {
                private
            }
          }
        }
      }
    }
  }
}

EOF;
    }

    protected function getRepliesGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: REPLY) {
        edges {
          node {
            ... on Reply {
              questionnaire {
                title
              }
              createdAt
              updatedAt
              responses {
                __typename
                ... on ValueResponse {
                  question {
                    title
                  }
                  formattedValue
                }
                ... on MediaResponse {
                  question {
                    title
                  }
                  medias {
                    url
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }

    protected function getMediasGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      medias {
        id
        name
        enabled
        authorName
        description
        contentType
        size
        url
        providerReference
      }
    }
  }
}

EOF;
    }

    protected function getGroupsGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      groups {
        edges {
          node {
            title
            description
            users {
              totalCount
            }
            createdAt
            updatedAt
          }
        }
      }
    }
  }
}

EOF;
    }

    protected function getReportsGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      reports {
        edges {
          node {
            related {
              kind
            }
            type
            body
            createdAt
          }
        }
      }
    }
  }
}

EOF;
    }

    protected function getEventsGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      events {
        edges {
          node {
            title
            startAt
            endAt
            themes {
              title
            }
            projects {
              title
            }
            commentable
            createdAt
            updatedAt
            body
            link
            address
            zipCode
            city
            country
          }
        }
      }
    }
  }
}

EOF;
    }

    protected function getProposalsGraphQLQuery(string $userId): string
    {
        return <<<EOF
{
  node(id: "${userId}") {
    ... on User {
      contributions(type: PROPOSAL) {
        edges {
          node {
            ... on Proposal {
              url
              title
              bodyText
              formattedAddress
              createdAt
              updatedAt
              trashedAt
              trashedReason
              responses {
                __typename
                ... on ValueResponse {
                    question {
                        title
                    }
                    formattedValue
                }
                ... on MediaResponse {
                    question {
                        title
                    }
                    medias {
                      url
                    }
                }
              }
            }
          }
        }
      }
    }
  }
}
EOF;
    }
}
