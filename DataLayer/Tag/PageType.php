<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\App\RequestInterface;

class PageType implements TagInterface
{
    private RequestInterface $request;

    public function __construct(
        RequestInterface $request
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
