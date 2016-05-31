<?php
//
//namespace UWDOEM\SecureUploads;
//
//use Propel\Generator\Model\Behavior;
//
///**
// * Class EncryptionBehavior
// *
// * @package Athens\Encryption
// */
//class FieldBehavior extends Behavior
//{
//
//    /**
//     * Multiple encrypted columns in the same table is OK.
//     *
//     * @return boolean
//     */
//    public function allowMultiple()
//    {
//        return true;
//    }
//
//    /**
//     * @param string $script
//     * @return void
//     * @throws \Exception If the schema specifies encryption on fields which are not
//     *                    VARBINARY.
//     */
//    public function tableMapFilter(&$script)
//    {
//        $table = $this->getTable();
//
//        foreach ($this->getColumnNames() as $columnName) {
//            $column = $table->getColumn($columnName);
//
//            $columnIsVarbinary = $column->getType() === "VARCHAR";
//
//            if ($columnIsVarbinary === false) {
//                throw new \Exception("Secure file upload columns must be of type VARCHAR. " .
//                    "Secure file upload column '{$column->getName()}' of type '{$column->getType()}' found. " .
//                    "Revise your schema.");
//            }
//        }
//
//        if (static::secureFileUploadDeclarationExists($script) === false) {
//            $insertLocation = strpos($script, ";", strpos($script, "const TABLE_NAME")) + 1;
//            static::secureFileUploadColumnsDeclaration($script, $insertLocation);
//        }
//
//        foreach ($this->getColumnRealNames() as $realColumnName) {
//            static::insertSecureFileUploadColumnName($script, $realColumnName);
//        }
//    }
//
//    /**
//     * @return string[]
//     */
//    protected function getColumnNames()
//    {
//        $columnNames = [];
//        foreach ($this->getParameters() as $key => $columnName) {
//            if (strpos($key, "column_name") !== false && empty($columnName) !== true) {
//                $columnNames[] = $columnName;
//            }
//        }
//        return $columnNames;
//    }
//
//    /**
//     * @return string[]
//     */
//    protected function getColumnRealNames()
//    {
//        $tableName = $this->getTable()->getName();
//
//        return array_map(
//            function ($columnName) use ($tableName) {
//                return "$tableName.$columnName";
//            },
//            $this->getColumnNames()
//        );
//    }
//
//    /**
//     * @param string $script
//     * @return boolean
//     */
//    protected static function secureFileUploadDeclarationExists($script)
//    {
//        return strpos($script, 'protected static $encryptedColumns') !== false;
//    }
//
//    /**
//     * @param string  $script
//     * @param integer $position
//     * @return void
//     */
//    protected static function secureFileUploadColumnsDeclaration(&$script, $position)
//    {
//
//        $content = <<<'EOT'
//
//
//    /**
//     * Those columns encrypted by Athens/Encryption
//     */
//    protected static $encryptedColumns = array(
//        );
//
//    /**
//     * Those columns encrypted deterministically by Athens/Encryption
//     */
//    protected static $encryptedSearchableColumns = array(
//        );
//EOT;
//
//        $script = substr_replace($script, $content, $position, 0);
//    }
//
//    /**
//     * @param string $script
//     * @param string $realColumnName
//     * @return void
//     */
//    public static function insertSecureFileUploadColumnName(&$script, $realColumnName)
//    {
//        $insertContent = "\n            '$realColumnName', ";
//
//        $insertLocation = strpos($script, '$encryptedColumns = array(') + strlen('$encryptedColumns = array(');
//        $script = substr_replace($script, $insertContent, $insertLocation, 0);
//    }
//}
