<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\MapperNotFoundException;
use App\Importer\DataMapper\MapperInterface;
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

    private const GH_ARCHIVE_BASE_URI = 'https://data.gharchive.org/';

    private DecoderInterface $decoder;
    private MapperInterface $mapper;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DecoderInterface $decoder,
        MapperInterface $mapper,
        EntityManagerInterface $entityManager
    ) {
        $this->decoder = $decoder;
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

        $style->section('Import GT Archive');

        // Check param validity
        // If datetime is false, then $date is not a valid date
        if (false === $date) {
            throw new InvalidArgumentException($date . ' is not a valid date. Abort.');
        }

        $style->writeln('Begin importing for date : ' . $dateString);

        // TODO : export the whole day by loop on $hour from 0 to 23 (+ concurrency ?)
        $hour = 15;
        $dataset = \gzfile(self::GH_ARCHIVE_BASE_URI . $dateString . '-' . $hour . '.json.gz');
        $importDate = $date->setTime($hour, 0);

        foreach ($dataset as $line => $item) {
            // Decode JSON into array
            // TODO : Check why some lines can't be decoded, in order to remove this try catch
            try {
                $decodedItem = $this->decoder->decode($item, JsonEncoder::FORMAT);
            } catch (\Exception $e) {
                // Skip not decodable data with warning output for the moment.
                $style->warning('Cannot decode line ' . $line);
                continue;
            }

            // We must have a type for the line in order to define how to import data
            // Skip otherwise
            $type = $decodedItem['type'] ?? false;
            if (false === $type) {
                $style->warning('Cannot import following data since there is no "type" defined : ' . $item);
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
                // We don't want to raise error if no mapper was found
                // If no mapper was found, then we don't want to import the data
            }
        }

        $style->success('Data imported successfuly');

        return Command::SUCCESS;
    }
}
