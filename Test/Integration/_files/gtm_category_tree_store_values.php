<?php declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store.php');

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
$storeManager->reinitStores();
$secondStoreId = (int)$storeManager->getStore('fixture_second_store')->getId();
$categoryResource = $objectManager->get(CategoryResource::class);
$getCategoryByName = $objectManager->get(GetCategoryByName::class);

/** @var Category $parent */
$parent = $getCategoryByName->execute('GTM Parent');
$parent->setStoreId($secondStoreId);
$parent->setName('GTM Parent (Second Store)');
$categoryResource->saveAttribute($parent, 'name');
$parent->setMetaTitle('GTM Parent Meta Title (Second Store)');
$categoryResource->saveAttribute($parent, 'meta_title');

/** @var Category $storeDisabled */
$storeDisabled = $getCategoryByName->execute('GTM Store Disabled');
$storeDisabled->setStoreId($secondStoreId);
$storeDisabled->setIsActive(false);
$categoryResource->saveAttribute($storeDisabled, 'is_active');
