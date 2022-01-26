<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\PartnersRepository;
use App\Entity\Partners;
use App\Entity\PartnerSales;
use DateTime;
use League\Csv\Reader;


class WorkerService
{
    private $filesService;
    private $driveApi;
    private $managerRegistry;
    private $partnersRepository;


    public function __construct(FilesService $filesService, DriveApi $driveApi, ManagerRegistry $managerRegistry, PartnersRepository $partnersRepository)
    {
        $this->filesService = $filesService;
        $this->driveApi = $driveApi;
        $this->managerRegistry = $managerRegistry;
        $this->partnersRepository = $partnersRepository;
    }

    /**
     * Check files for correct mimeType and download CSV and googlesheet files
     * @param array $files
     * @param string $token
     * @param string $path
     * @return array
     */
    public function processFilesListAndDownload(array $files, string $token, string $uploadFolderId, string $archiveFolderId, string $invalidFolderId): array
    {
        $dataFiles = [];
        $invalidFiles=0;
        $totalFilesDownloaded = 0;
        if (isset($files)) {
            foreach ($files as $f) {
                // if any folders, keep untouched
                if ($f->mimeType == "application/vnd.google-apps.folder") {
                    continue;
                }
                // check if file is csv and correct name
                if (in_array($f->mimeType, ['text/csv']) && $this->filesService->checkPartnerFileName($f->name)) {
                    $fileContents = $this->driveApi->fileDownloadCSV($token, $f, '');
                    if ($this->processData($fileContents, $f->name)) {
                        $totalFilesDownloaded++;
                        $this->driveApi->fileMove($token, $f->id, $uploadFolderId, $archiveFolderId);
                    } else {
                        $invalidFiles++;
                        $this->driveApi->fileMove($token, $f->id, $uploadFolderId, $invalidFolderId);
                    }
                } else if (in_array($f->mimeType, ['application/vnd.google-apps.spreadsheet']) && $this->filesService->checkPartnerFileName($f->name)) {
                    // check if file is google sheet and correct name
                    $fileContents = $this->driveApi->fileDownloadGoogleSheets($token, $f, '');
                    if ($this->processData($fileContents, $f->name)) {
                        $totalFilesDownloaded++;
                        $this->driveApi->fileMove($token, $f->id, $uploadFolderId, $archiveFolderId);
                    } else {
                        $invalidFiles++;
                        $this->driveApi->fileMove($token, $f->id, $uploadFolderId, $invalidFolderId);
                    }
                } else {
                    $invalidFiles++;
                    $this->driveApi->fileMove($token, $f->id, $uploadFolderId, $invalidFolderId);
                }
            }
        }
        return ['ok' => $totalFilesDownloaded, 'bad' => $invalidFiles];
    }

    public function processData(string $csvContent, string $fileName): bool
    {
        $headers = ['dateTime', 'clientName', 'productName', 'quantity', 'piecePrice', 'deliveryType', 'deliveryCity', 'deliveryPrice', 'totalPrice'];
        $rowsProcessed = 0;
        $rows = [];
        echo '- ' . $fileName . PHP_EOL;

        $reader = Reader::createFromString($csvContent);
        $reader->setDelimiter($_ENV['CSV_DEFAULT_DELIMITER']);
        $records = $reader->getRecords();
        foreach ($records as $offset => $record) {
            if (count($record) !== count($headers)) {
                echo '  ^ file structure is not ok' . PHP_EOL;
                return false;
            } else {
                $rows[] = array_combine($headers, $record);
            }
        }

        if (sizeof($rows)>0) {
            //  insert to database
            $entityManager = $this->managerRegistry->getManager();
            $partnerName = $this->filesService->getPartnerNameFromOriginalFileName($fileName);
            if ($partnerName == '') {
                echo '  ^ unable to extract partnerName' . PHP_EOL;
                return false;
            }
            $partner = $this->partnersRepository->findOneBy(['partnerName' => $partnerName]);
            if (!$partner) {
                $partner = new Partners();
                $partner->setPartnerName($partnerName);
                $entityManager->persist($partner);
                $entityManager->flush();
                $partner = $this->partnersRepository->find($partner->getId());
            }

            foreach ($rows as $r) {
                $partnerSales = new PartnerSales();
                $partnerSales->setPartnerId($partner);
                $partnerSales->setItemDateTime(new DateTime($r['dateTime']));
                $partnerSales->setClientName($r['clientName']);
                $partnerSales->setProductName($r['productName']);
                $partnerSales->setQuantity($r['quantity']);
                $partnerSales->setPiecePrice($r['piecePrice']);
                $partnerSales->setDeliveryType($r['deliveryType']);
                $partnerSales->setDeliveryCity($r['deliveryCity']);
                $partnerSales->setDeliveryPrice($r['deliveryPrice']);
                $partnerSales->setTotalPrice($r['totalPrice']);
                $entityManager->persist($partnerSales);
                $entityManager->flush();
                $rowsProcessed++;
            }
            return true;
        } else {
            return false;
        }
    }


}
