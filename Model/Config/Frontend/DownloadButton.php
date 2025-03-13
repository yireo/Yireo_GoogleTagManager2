<?php declare(strict_types=1);

namespace Tagging\GTM\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DownloadButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Tagging_GTM::system/config/download_button.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for download button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('gtm/system_config/download');
    }

    /**
     * Generate download button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'download_button',
            'label' => __('Download Debug Data'),
        ]);

        return $button->toHtml();
    }
} 