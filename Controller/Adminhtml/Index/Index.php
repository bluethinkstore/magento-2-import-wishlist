<?php
declare(strict_types=1);
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList as APPDIRECTORYLIST;
use Magento\Backend\App\Action;

/**
 * Used for download csv
 */
class Index extends Action
{
    /**
     * This is a folder name constants
     */
    private const FOLDER_NAME = "downloadsamplecsv";

    /**
     * This is a file name cosntants
     *
     */
    private const FILE_NAME = "import_wishlist_sample.csv";

    /**
     * @var FileFactory
     */
    private $downloader;

    /**
     * @var MEDIA
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var APPDIRECTORYLIST
     */
    private $appredirectlist;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->downloader = $fileFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            APPDIRECTORYLIST::MEDIA
        );
    }

    /**
     * Execute Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($this->getRequest()->getParams()["download_sample"])) {
            $filepath = self::FOLDER_NAME . "/" . self::FILE_NAME;
            $filename = self::FILE_NAME;
            $this->mediaDirectory->create(self::FOLDER_NAME);
            $stream = $this->mediaDirectory->openFile($filepath, "w+");
            $stream->lock();
            $header = ["email", "sku"];
            $stream->writeCsv($header);
            $data = $this->getSampleData();
            foreach ($data as $csvdata) {
                $data = [];
                $data[] = $csvdata["0"];
                $data[] = $csvdata["1"];
                $stream->writeCsv($data);
            }
            $content["type"] = "filename";
            $content["value"] = $filepath;
            $this->downloader->create(
                $filename,
                $content,
                APPDIRECTORYLIST::MEDIA
            );
        }

        return $resultRedirect->setPath("csvdata/index/importcsv");
    }

    /**
     * Get sample data
     *
     * @return string[][]
     */
    private function getSampleData()
    {
        return [
            [
                "roni_cost@example.com",
                "24-WB02,24-WB03,24-WB07,24-WB04,24-UG04",
            ],
        ];
    }
}
