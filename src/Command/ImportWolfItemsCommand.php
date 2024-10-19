<?php

declare(strict_types=1);

namespace WolfShop\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WolfShop\Entity\Item;

#[AsCommand(
    name: 'wolf:import-items',
    description: 'Import items from an API',
)]
class ImportWolfItemsCommand extends Command
{
    private const DATA_SOURCE_URL = 'https://api.restful-api.dev/objects';

    private const BATCH_SIZE = 100;

    private const LEGENDARY_ITEM_NAME = 'Samsung Galaxy S23';

    private const LEGENDARY_ITEM_QUALITY = 80;

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    /**
     * execute function
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $content = [];
        $output->writeln('Retrieving items from URL ' . self::DATA_SOURCE_URL);

        // Retrive content from the given data source
        try {
            $response = $this->client->request('GET', self::DATA_SOURCE_URL);
            $content = $response->toArray();
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());

            return Command::FAILURE;
        }

        $contentCount = count($content);
        $output->writeln('Retrieved ' . $contentCount . ' items successfully');
        if (empty($content)) {
            $output->writeln('No item to import');

            return Command::SUCCESS;
        }

        $output->writeln('Importing items to database...');

        // Starts and displays the progress bar
        $progressBar = new ProgressBar($output, $contentCount);
        $progressBar->start();

        // Handle data by chunk
        $chunkedData = array_chunk($content, self::BATCH_SIZE);
        foreach ($chunkedData as $chunkedItems) {
            $this->handleChunkedItems($chunkedItems, $output, $progressBar);
        }

        // Ensures that the progress bar is at 100%
        $progressBar->finish();

        $output->writeln(''); // Break new line
        $output->writeln('Import process completed');

        return Command::SUCCESS;
    }

    /**
     * handleChunkedItems function
     */
    private function handleChunkedItems(array $chunkedItems, OutputInterface $output, ProgressBar $progressBar): void
    {
        foreach ($chunkedItems as $data) {
            $itemName = $data['name'] ?? null;
            if (empty($itemName)) {
                $output->writeLn('Invalid item without name attribute - Item ID ' . $data['id']);
                continue;
            }

            /** @var Item $existingItem */
            $existingItem = $this->entityManager->getRepository(Item::class)->findOneByName($itemName);
            if (! empty($existingItem)) {
                // In case Item already exists by name, update Quality for it
                $existingItem->setQuality($existingItem->getQuality() + 1);
                $this->entityManager->persist($existingItem);
            } else {
                // Init new Item entity object
                $item = new Item();
                $item->setName($itemName);
                $item->setSellIn($this->initItemSellIn());
                $item->setQuality($this->initItemQuality($itemName));

                $this->entityManager->persist($item);
            }

            $this->entityManager->flush();

            // Advances the progress bar 1 unit
            $progressBar->advance();
        }

        // Detaches all objects from Doctrine for each chunk to free up memory
        $this->entityManager->clear();
    }

    /**
     * initItemSellIn function
     *
     * @return integer
     */
    private function initItemSellIn(): int
    {
        // @TODO: To define a proper logic for "sell_in" value initilization from the requirement
        return rand(10, 100);
    }

    /**
     * initItemQuality function
     *
     * @return integer
     */
    private function initItemQuality(string $itemName): int
    {
        // @TODO: To define a proper logic for "quality" value initilization from the requirement
        return $itemName === self::LEGENDARY_ITEM_NAME ? self::LEGENDARY_ITEM_QUALITY : rand(0, 50);
    }
}
