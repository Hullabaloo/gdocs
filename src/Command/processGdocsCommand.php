<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\DriveApi;
use App\Service\WorkerService;

class processGdocsCommand extends Command
{

    protected static $defaultName = 'app:gdocs';
    protected static $defaultDescription = '';

    private $driveApi;
    private $workersService;

    public function __construct(DriveApi $driveApi, WorkerService $workersService)
    {
        $this->driveApi = $driveApi;
        $this->workersService = $workersService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Download and process files from google drive.')
            ->setHelp('Allows to process new files from our partners - CSV/Google Sheets only.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Google Drive Files downloader and processor',
            '===========================================',
            '',
        ]);

        // generate JWT and request access token
        $tkn = $this->driveApi->requestAuthToken();
        if ($tkn['err'] == 1) {
            $output->writeln('ERROR: ' . $tkn['message']);
            return Command::FAILURE;
        }

        // check if upload folder exist, get folder id
        $uploadFolderId = $this->driveApi->getElementIdByName($tkn['token'], "upload");
        if ($uploadFolderId['err'] == 1) {
            $output->writeln('ERROR: ' . $uploadFolderId['message'] .' / Or "upload" folder does not exist, create it at GoogleDrive');
            return Command::FAILURE;
        }

        // check if archive folder exist, get folder id
        $archiveFolderId = $this->driveApi->getElementIdByName($tkn['token'], "archive");
        if ($archiveFolderId['err'] == 1) {
            $output->writeln('ERROR: ' . $archiveFolderId['message'] .' / Or "archive" folder does not exist, create it at GoogleDrive');
            return Command::FAILURE;
        }

        // check if invalid folder exist, get folder id
        $invalidFolderId = $this->driveApi->getElementIdByName($tkn['token'], "invalid");
        if ($invalidFolderId['err'] == 1) {
            $output->writeln('ERROR: ' . $invalidFolderId['message'] .' / Or "invalid" folder does not exist, create it at GoogleDrive');
            return Command::FAILURE;
        }

        // get list of all files located in upload folder
        $fls = $this->driveApi->driveGetFilesInFolder($tkn['token'], $uploadFolderId['id']);
        if ($fls['err'] == 1) {
            $output->writeln('ERROR: ' . $fls['message']);
            return Command::FAILURE;
        }

        $totalFilesLocated = sizeof($fls['files']);
        // if we have at least 1 file in 'upload' folder then process
        if ($totalFilesLocated > 0) {
            $output->writeln('Number of misc files/folders found at GoogleDrive shared folder: ' . $totalFilesLocated);
            $output->writeln('Now downloading and processing...');

            // process files
            $downloadedFiles = $this->workersService->processFilesListAndDownload($fls['files'], $tkn['token'], $uploadFolderId['id'], $archiveFolderId['id'], $invalidFolderId['id']);
            $output->writeln([
                '',
                'Finished!',
                'Good files: ' . $downloadedFiles['ok'],
                'Bad files: ' . $downloadedFiles['bad'],
                '',
            ]);
            return Command::SUCCESS;
        }
        $output->writeln([
            '',
            'Finished! No files located or downloaded.',
            '',
        ]);
        return Command::SUCCESS;
    }
}