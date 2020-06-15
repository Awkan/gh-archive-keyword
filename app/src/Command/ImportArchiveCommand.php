<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\MapperNotFoundException;
use App\Import\Mapper\MapperInterface;
use App\Webservice\Provider\GHArchiveProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ImportArchiveCommand extends Command
{
    protected static $defaultName = 'app:import:archive';

    private DecoderInterface $decoder;
    private GHArchiveProvider $provider;
    private MapperInterface $mapper;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DecoderInterface $decoder,
        GHArchiveProvider $provider,
        MapperInterface $mapper,
        EntityManagerInterface $entityManager
    ) {
        $this->decoder = $decoder;
        $this->provider = $provider;
        $this->mapper = $mapper;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Import a GH Archive')
            ->setHelp('This command import data from GH archives according to given date into local database.')
            ->addArgument(
                'date',
                InputArgument::REQUIRED,
                'Day to export (YYYY-MM-DD)'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        /** @var string $dateString */
        $dateString = $input->getArgument('date');
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        $style->title('Import GT Archive');

        // Check param validity
        // If datetime is false, then $date is not a valid date
        if (false === $date) {
            throw new InvalidArgumentException($date . ' is not a valid date. Abort.');
        }

        $style->writeln('Begin import for date : ' . $dateString);

        $nbSuccess = 0;
        $nbDecodingError = 0;
        $nbNoType = 0;
        $nbNoMapper = 0;

        $style->newLine();
        $style->write('Fetching data to GHArchive ... ');
        // TODO : export the whole day by loop on $hour from 0 to 23 (+ concurrency ?)
        // Only use hour 15 for the moment
        $importDate = $date->setTime(15, 00);
        $dataset = $this->provider->fetch(
            $importDate->format('Y'),
            $importDate->format('m'),
            $importDate->format('d'),
            $importDate->format('H')
        );
        $style->writeLn('OK');
        $style->newLine();

        $style->progressStart(\count($dataset));

        foreach ($dataset as $item) {
            $style->progressAdvance();

            // Decode JSON into array
            // TODO : Check why some lines can't be decoded, in order to remove this "useless" try catch
            try {
                $decodedItem = $this->decoder->decode($item, JsonEncoder::FORMAT);
            } catch (\Exception $e) {
                ++$nbDecodingError;
                continue;
            }

            // We must have a type for the line in order to define how to import data
            // Skip otherwise
            $type = $decodedItem['type'] ?? false;
            if (false === $type) {
                ++$nbNoType;
                continue;
            }

            // Import data
            try {
                $mappedData = $this->mapper->map(
                    $decodedItem,
                    $type,
                    [
                        MapperInterface::CONTEXT_IMPORT_DATE => $importDate,
                    ]
                );
                $mappedData = \is_iterable($mappedData) ? $mappedData : [$mappedData];

                foreach ($mappedData as $item) {
                    // Warning : We didn't have check if items are already in database.
                    // This would be a nice feature, to avoid duplicate data or unique constraint violation ;-)
                    $this->entityManager->persist($item);
                }

                $this->entityManager->flush();
            } catch (MapperNotFoundException $e) {
                // If no mapper was found, then we don't want to import the data & not raise an exception
                ++$nbNoMapper;
            }

            ++$nbSuccess;
        }

        $style->progressFinish();
        $style->success('Data imported successfuly');

        $style->table(
            ['Rows import status', 'Number'],
            [
                ['SUCCESS', $nbSuccess],
                ['SKIP - No mapper (not needed data)', $nbNoMapper],
                ['ERROR - Cannot be decoded', $nbDecodingError],
                ['ERROR - No event type defined', $nbNoType],
            ]
        );

        return Command::SUCCESS;
    }
}
