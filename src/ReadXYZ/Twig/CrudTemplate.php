<?php


namespace App\ReadXYZ\Twig;

use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Data\TableData;
use App\ReadXYZ\Page\Page;
use App\ReadXYZ\Helpers\PhonicsException;

class CrudTemplate
{

    private string $tableName;
    private string $boolEnumTreatment;
    private Page $page;

    public function __construct(string $tableName, string $boolEnumTreatment = BoolEnumTreatment::KEEP_AS_Y_N)
    {
        if (!BoolEnumTreatment::isValid($boolEnumTreatment)) {
            throw new PhonicsException("$boolEnumTreatment is invalid bool enum treatment.");
        }

        $this->tableName = $tableName;
        $this->boolEnumTreatment = $boolEnumTreatment;
        $this->page = new Page("$tableName  VIEW - ADD - UPDATE - DELETE");
    }

    public function display(): void
    {
        //args contains tablename, fields and data
        //fields properties are name, read-only, isKey, default, width, enum-bool, isJson, auto-update
        $tableData = new TableData($this->tableName, $this->boolEnumTreatment);
        $args = $tableData->getTwigArguments();
        $this->page->addArguments($args);
        $this->page->display('tables_crud');
    }


}
