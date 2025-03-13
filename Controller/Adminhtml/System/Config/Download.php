<?php declare(strict_types=1);

namespace Tagging\GTM\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Driver\File;
use Tagging\GTM\Logger\Debugger;

class Download extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Debugger
     */
    protected $debugger;

    /**
     * @var File
     */
    private $file;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Debugger $debugger
     * @param File $file
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Debugger $debugger,
        File $file
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->debugger = $debugger;
        $this->file = $file;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tagging_GTM::config');
    }

    /**
     * Download debug data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $logFile = BP . '/var/log/Tagging_GTM.log';
            if ($this->file->isExists($logFile)) {
                $content = $this->file->fileGetContents($logFile);
                $base64Content = base64_encode($content);
                
                return $result->setData([
                    'success' => true,
                    'content' => $base64Content,
                    'filename' => 'Tagging_GTM.log',
                    'mimeType' => 'text/plain'
                ]);
            }
            
            return $result->setData([
                'success' => false,
                'message' => __('Debug log file does not exist.')
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 