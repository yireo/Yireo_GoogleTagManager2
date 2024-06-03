<?php
declare(strict_types=1);

namespace Tagging\GTM\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'trytagging_marketing',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'TryTagging Marketing Data'
                ]
            );
        }

        $setup->endSetup();
    }
}
