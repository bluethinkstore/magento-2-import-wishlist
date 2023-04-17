<?php
declare(strict_types=1);
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList as APPDIRECTORYLIST;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\Dir;

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
     * This is a module name cosntants
     *
     */
    private const MODULE_NAME = "Bluethinkinc_ImportWishlist";

    /**
     * This is a file name of your directory constant.
     *
     */
    private const MODULE_FILE_PATH = "Files/Sample/import_wishlist_sample.csv";

    /**
     * This is a name  of entity directory constant.
     *
     */
    private const ENTITY = "import_wishlist_sample";

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
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param RawFactory $resultRawFactory
     * @param ReadFactory $readFactory
     * @param Dir $moduleDir
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        Dir $moduleDir
    ) {
        parent::__construct($context);
        $this->downloader = $fileFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            APPDIRECTORYLIST::MEDIA
        );
        $this->resultRawFactory = $resultRawFactory;
        $this->readFactory = $readFactory;
        $this->moduleDir = $moduleDir;
    }

    /**
     * Execute Action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $entityName = self::ENTITY;
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
                APPDIRECTORYLIST::MEDIA,
                "application/octet-stream"
            );
            $fileContents = $this->getFileContents($entityName);
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($fileContents);
            return $resultRaw;
        }

        return $resultRedirect->setPath("csvdata/index/importcsv");
    }

    /**
     * Get sample data
     *
     * @return string[][]
     */
    public function getSampleData()
    {
        return [["roni_cost@example.com", "24-MB01,24-MB02,24-WB02,24-WB03"]];
    }

    /**
     * Returns Content for the given file associated to an Import entity
     *
     * @param string $entityName
     * @throws NoSuchEntityException
     * @return string
     */
    public function getFileContents(string $entityName): string
    {
        $directoryRead = $this->readFactory->create(
            $this->getModuleDirectory()
        );
        $filePath = $this->getModuleDirectory() . "/" . self::MODULE_FILE_PATH;
        return $directoryRead->readFile($filePath);
    }

    /**
     * Get module directory
     *
     * @return string
     */
    public function getModuleDirectory()
    {
        return $this->moduleDir->getDir(self::MODULE_NAME);
    }
}
