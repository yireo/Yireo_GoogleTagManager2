<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag\Page;

use Tagging\GTM\Api\Data\TagInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;

class Breadcrumbs implements TagInterface
{
    private CatalogHelper $catalogHelper;

    public function __construct(
        CatalogHelper $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    public function get(): array
    {
        $data = [];
        $breadcrumbs = $this->catalogHelper->getBreadcrumbPath();
        foreach ($breadcrumbs as $breadcrumb) {
            if (is_array($breadcrumb) && isset($breadcrumb['label'])) {
                $data[] = $breadcrumb['label'];
            }
        }

        return $data;
    }
}
