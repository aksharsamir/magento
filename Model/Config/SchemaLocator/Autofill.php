<?php

namespace Etailors\Forms\Model\Config\SchemaLocator;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;

class Autofill implements SchemaLocatorInterface
{
    /**
     * XML schema for config file.
     */
    const CONFIG_FILE_SCHEMA = 'form_classes.xsd';

    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     * @var string
     */
    protected $perFileSchema = null;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $configDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Etailors_Forms');
        $this->schema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        $this->perFileSchema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
