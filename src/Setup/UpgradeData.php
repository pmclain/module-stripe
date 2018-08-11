<?php

namespace Pmclain\Stripe\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /** @var Config */
    private $eavConfig;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $config
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $config
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $config;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                'stripe_customer_id',
                [
                    'type' => 'varchar',
                    'label' => 'Strip Customer ID',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 100,
                    'system' => false,
                    'position' => 100,
                ]
            );

            $stripeCustomerIdAttribute = $this->eavConfig->getAttribute(
                Customer::ENTITY,
                'stripe_customer_id'
            );
            $stripeCustomerIdAttribute->setData(
                'used_in_forms',
                [
                    'adminhtml_customer',
                ]
            );
            $stripeCustomerIdAttribute->save();
        }
    }
}
