<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\App\Request\Http as Request;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

class PageType implements TagInterface
{
    private Request $request;

    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }

    public function get(): string
    {
        $moduleName = $this->request->getModuleName();
        $controllerName = $this->request->getControllerName();
        $actionName = $this->request->getActionName();
        return $moduleName . '/' . $controllerName . '/' . $actionName;
    }
}
